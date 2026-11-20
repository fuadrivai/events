<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PhotoEvent;
use Illuminate\Http\Request;

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
                'active_date' => today(),
                'countdown_seconds' => 3,
                'status' => 'draft',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        $photoEvent = PhotoEvent::create($data);

        return redirect()
            ->route('dashboard.photo-events.edit', $photoEvent)
            ->with('success', 'Photo event berhasil dibuat.');
    }

    public function edit(PhotoEvent $photoEvent)
    {
        return view('dashboard.photo-events.form', compact('photoEvent'));
    }

    public function update(Request $request, PhotoEvent $photoEvent)
    {
        $photoEvent->update($this->validatedData($request, $photoEvent));

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
            'frame_theme' => ['sometimes', 'required', 'in:classic,tropical'],
            'countdown_seconds' => ['required', 'integer', 'between:0,10'],
            'status' => ['required', 'in:draft,published,closed'],
        ]);

        $validated['subtitle'] = $validated['subtitle'] ?? null;
        $validated['frame_theme'] = $validated['frame_theme'] ?? $photoEvent?->frame_theme ?? 'classic';

        return $validated;
    }
}
