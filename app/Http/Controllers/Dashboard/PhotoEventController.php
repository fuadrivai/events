<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PhotoEventController extends Controller
{
    public function index()
    {
        $photoEvents = PhotoEvent::withCount('photos')
            ->latest()
            ->paginate(12);

        return view('dashboard.photo-events.index', compact('photoEvents'));
    }

    public function create()
    {
        return view('dashboard.photo-events.form', [
            'photoEvent' => new PhotoEvent([
                'frame_color' => '#D9A978',
                'frame_background_color' => '#54172F',
                'frame_theme' => 'classic',
                'frame_mode' => 'classic',
                'active_date' => today(),
                'countdown_seconds' => 3,
                'status' => 'draft',
                'frame_layout' => PhotoEvent::defaultFrameLayout(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $template = $data['template'] ?? null;
        unset($data['template']);

        if ($template) {
            $templatePath = $template->store('photo-event-templates/'.$data['slug'], 'local');
            if ($templatePath === false) {
                report(new \RuntimeException('Photo event template could not be stored.'));

                return back()->withInput()->withErrors(['template' => 'Template gagal disimpan. Silakan coba lagi.']);
            }

            $data['template_path'] = $templatePath;
        }

        $data['created_by'] = $request->user()->id;

        $photoEvent = PhotoEvent::create($data);

        return redirect()
            ->route('dashboard.photo-events.edit', $photoEvent)
            ->with('success', 'Photo event berhasil dibuat.');
    }

    public function edit(PhotoEvent $photoEvent)
    {
        $photos = $photoEvent->photos()
            ->latest()
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->file_available = Storage::disk('local')->exists($photo->path);

            return $photo;
        });

        return view('dashboard.photo-events.form', compact('photoEvent', 'photos'));
    }

    public function template(PhotoEvent $photoEvent)
    {
        $disk = Storage::disk('local');
        if (! $photoEvent->template_path || ! $disk->exists($photoEvent->template_path)) {
            abort(404, 'Template photo event tidak ditemukan.');
        }

        return $disk->response($photoEvent->template_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function photoPreview(PhotoEvent $photoEvent, Photo $photo)
    {
        abort_unless($photo->photo_event_id === $photoEvent->id, 404);

        $disk = Storage::disk('local');
        if (! $disk->exists($photo->path)) {
            report(new \RuntimeException('A photo event preview points to a missing image.'));
            abort(404, 'File foto tidak ditemukan.');
        }

        return $disk->response($photo->path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function update(Request $request, PhotoEvent $photoEvent)
    {
        $data = $this->validatedData($request, $photoEvent);
        $template = $data['template'] ?? null;
        unset($data['template']);

        $oldTemplatePath = $photoEvent->template_path;
        if ($template) {
            $templatePath = $template->store('photo-event-templates/'.$data['slug'], 'local');
            if ($templatePath === false) {
                report(new \RuntimeException('Photo event template could not be stored.'));

                return back()->withInput()->withErrors(['template' => 'Template gagal disimpan. Silakan coba lagi.']);
            }
            $data['template_path'] = $templatePath;
        }

        $photoEvent->update($data);
        if ($template && $oldTemplatePath) {
            Storage::disk('local')->delete($oldTemplatePath);
        }

        return back()->with('success', 'Pengaturan photo event berhasil disimpan.');
    }

    public function booth(PhotoEvent $photoEvent)
    {
        return view('dashboard.photo-events.booth', compact('photoEvent'));
    }

    private function validatedData(Request $request, ?PhotoEvent $photoEvent = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash', 'unique:photo_events,slug,'.($photoEvent?->id ?? 'NULL')],
            'title' => ['required', 'string', 'max:160'],
            'camera_label' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'active_date' => ['required', 'date'],
            'frame_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'frame_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'frame_mode' => ['required', 'in:classic,template'],
            'frame_theme' => ['sometimes', 'required', 'in:classic,tropical'],
            'countdown_seconds' => ['required', 'integer', 'between:0,10'],
            'status' => ['required', 'in:draft,published,closed'],
            'template' => [
                $request->input('frame_mode') === 'template' && ! $photoEvent?->template_path ? 'required' : 'nullable',
                'image',
                'mimes:png',
                'max:10240',
            ],
            'frame_layout' => ['required', 'json', 'max:10000'],
        ]);

        $layout = json_decode($validated['frame_layout'], true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($layout)) {
            throw ValidationException::withMessages([
                'frame_layout' => 'Konfigurasi layout template tidak valid.',
            ]);
        }
        $this->validateFrameLayout($layout);
        $validated['frame_layout'] = $layout;
        $validated['subtitle'] = $validated['subtitle'] ?? null;
        $validated['frame_theme'] = $validated['frame_theme'] ?? $photoEvent?->frame_theme ?? 'classic';

        return $validated;
    }

    private function validateFrameLayout(array $layout): void
    {
        $fontFamilies = ['Arial', 'Georgia', 'Verdana', 'Trebuchet MS', 'Courier New', 'Times New Roman', 'Impact'];
        $rules = [
            'photo' => ['required', 'array'],
            'photo.x' => ['required', 'numeric', 'between:0,1'],
            'photo.y' => ['required', 'numeric', 'between:0,1'],
            'photo.width' => ['required', 'numeric', 'between:0.05,1'],
            'photo.height' => ['required', 'numeric', 'between:0.05,1'],
            'elements' => ['required', 'array'],
            'elements.logo.x' => ['required', 'numeric', 'between:0,1'],
            'elements.logo.y' => ['required', 'numeric', 'between:0,1'],
            'elements.logo.width' => ['required', 'numeric', 'between:0.02,0.5'],
        ];

        foreach (['school', 'title', 'subtitle', 'date'] as $element) {
            $rules["elements.{$element}.x"] = ['required', 'numeric', 'between:0,1'];
            $rules["elements.{$element}.y"] = ['required', 'numeric', 'between:0,1'];
            $rules["elements.{$element}.width"] = ['required', 'numeric', 'between:0.05,1'];
            $rules["elements.{$element}.fontSize"] = ['required', 'integer', 'between:8,160'];
            $rules["elements.{$element}.fontFamily"] = ['required', Rule::in($fontFamilies)];
            $rules["elements.{$element}.color"] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
            $rules["elements.{$element}.align"] = ['required', Rule::in(['left', 'center', 'right'])];
        }

        $validator = Validator::make($layout, $rules);
        $validator->after(function ($validator) use ($layout) {
            $boxes = [
                ['element' => data_get($layout, 'photo'), 'height' => 'height'],
                ['element' => data_get($layout, 'elements.logo'), 'height' => 'width'],
            ];
            foreach (['school', 'title', 'subtitle', 'date'] as $textElement) {
                $boxes[] = [
                    'element' => data_get($layout, "elements.{$textElement}"),
                    'height' => null,
                ];
            }

            foreach ($boxes as $box) {
                $element = $box['element'];
                if (! is_array($element)) {
                    continue;
                }

                $x = (float) ($element['x'] ?? 0);
                $y = (float) ($element['y'] ?? 0);
                $width = (float) ($element['width'] ?? 0);
                $height = $box['height']
                    ? (float) ($element[$box['height']] ?? 0)
                    : (float) ($element['fontSize'] ?? 0) * 1.25 / 1600;
                if ($x + $width > 1 || $y + $height > 1) {
                    $validator->errors()->add('frame_layout', 'Posisi elemen harus berada di dalam batas template.');
                    return;
                }
            }
        });
        $validator->validate();
    }
}
