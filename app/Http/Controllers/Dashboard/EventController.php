<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventField;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->isAdmin()
            ? \App\Models\Event::query()
            : \App\Models\Event::where('created_by', $user->id);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('status')) {
            $status = strtolower((string) $request->status);

            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(status) = ?', ['active'])
                      ->orWhereRaw('LOWER(status) = ?', ['published']);
                });
            } else {
                $query->whereRaw('LOWER(status) = ?', [$status]);
            }
        }

        $events = $query
            ->orderByRaw("CASE
                WHEN LOWER(status) IN ('active', 'published') THEN 1
                WHEN LOWER(status) = 'draft' THEN 2
                WHEN LOWER(status) = 'closed' THEN 3
                ELSE 4
            END ASC")
            ->latest()
            ->paginate(20)
            ->appends($request->only(['search', 'status']));

        return view('dashboard.events.index', compact('events'));
    }

    public function create()
{
    return view('dashboard.events.create');
}
public function store(Request $request)
{
    $data = $request->validate([
        'title'  => 'required|string|max:255',
        'slug'   => 'required|string|max:255|unique:events,slug',
        'intro_html' => 'nullable|string',
        'active_until' => 'nullable|date',
        'price_question_label' => 'nullable|string|max:150',
    ]);

    $data['status'] = 'draft';
    $data['active_until'] = $data['active_until'] ? Carbon::parse($data['active_until'])->format('Y-m-d H:i:s') : null;
    $event = Event::create([
        ...$data,
        'created_by' => auth()->id(),
    ]);

    // ✅ kirim model (akan pakai id untuk {event})
    return redirect()->route('dashboard.events.edit', $event)
        ->with('success', 'Event dibuat (draft).');
}


public function show(\App\Models\Event $event)
{
    return view('dashboard.events.show', compact('event'));
}
public function fields(\App\Models\Event $event)
{
    $fields = $event->fields()->orderBy('order_index')->get();
    return view('dashboard.events.fields', compact('event', 'fields'));
}
public function fieldsCreate(\App\Models\Event $event)
{
    return view('dashboard.events.fields-create', compact('event'));
}

public function fieldsStore(Request $request, Event $event)
{
    $request->validate([
        'label' => 'required|string|max:255',
        'type'  => 'required|in:text,textarea,select,radio,checkbox,email,phone,number,date',
        'is_required' => 'nullable|boolean',

        // wajib kalau type select/radio/checkbox
        'options' => 'nullable|string|required_if:type,select,radio,checkbox|min:1',
    ]);

    $optionsJson = null;

    if (in_array($request->type, ['select','radio','checkbox'], true)) {
        // guard kalau cuma spasi/newline
        if (trim((string) $request->options) === '') {
            return back()->withErrors(['options' => 'Options wajib diisi (minimal 1 baris).'])->withInput();
        }

        $lines = preg_split("/\r\n|\n|\r/", (string) $request->options);
        $options = collect($lines)->map(fn($v) => trim($v))->filter()->values()->all();

        if (count($options) === 0) {
            return back()->withErrors(['options' => 'Options wajib diisi (minimal 1 baris).'])->withInput();
        }

        $optionsJson = $options;
    }

    $baseKey = Str::slug($request->label, '_');
    $fieldKey = $baseKey ?: 'field';

    $suffix = 1;
    while ($event->fields()->where('field_key', $fieldKey)->exists()) {
        $suffix++;
        $fieldKey = $baseKey . '_' . $suffix;
    }

    $lastOrder = (int) $event->fields()->max('order_index');
    $nextOrder = $lastOrder + 1;

    EventField::create([
        'event_id' => $event->id,
        'field_key' => $fieldKey,
        'label' => $request->label,
        'type' => $request->type,
        'is_required' => (bool) $request->boolean('is_required'),
        'options_json' => $optionsJson,
        'order_index' => $nextOrder,
        'is_active' => true,
    ]);

    return redirect()
        ->route('dashboard.events.fields', $event)
        ->with('success', 'Pertanyaan berhasil ditambahkan');
}
public function fieldsEdit(Event $event, EventField $field)
{
    // safety: pastikan field milik event ini
    abort_unless($field->event_id === $event->id, 404);

    return view('dashboard.events.fields-edit', compact('event', 'field'));
}

public function fieldsUpdate(Request $request, Event $event, EventField $field)
{
    abort_unless($field->event_id === $event->id, 404);

    $request->validate([
        'label' => 'required|string|max:255',
        'type'  => 'required|in:text,textarea,select,radio,checkbox,email,phone,number,date',
        'is_required' => 'nullable|boolean',
        'is_active' => 'nullable|boolean',
        'options' => 'required_if:type,select,radio,checkbox|string|min:1',
    ]);

    $optionsJson = null;
    if (in_array($request->type, ['select','radio','checkbox'], true)) {
        if (trim((string) $request->options) === '') {
            return back()->withErrors(['options' => 'Options wajib diisi (minimal 1 baris).'])->withInput();
        }
        $lines = preg_split("/\r\n|\n|\r/", (string) $request->options);
        $options = collect($lines)->map(fn($v) => trim($v))->filter()->values()->all();

        if (count($options) === 0) {
            return back()->withErrors(['options' => 'Options wajib diisi (minimal 1 baris).'])->withInput();
        }
        $optionsJson = $options;
    }

    $field->update([
        'label' => $request->label,
        'type' => $request->type,
        'is_required' => (bool) $request->boolean('is_required'),
        'is_active' => (bool) $request->boolean('is_active'),
        'options_json' => $optionsJson,
    ]);

    return redirect()
        ->route('dashboard.events.fields', $event)
        ->with('success', 'Pertanyaan berhasil diupdate');
}

public function fieldsDestroy(Event $event, EventField $field)
{
    abort_unless($field->event_id === $event->id, 404);

    $field->delete();

    return redirect()
        ->route('dashboard.events.fields', $event)
        ->with('success', 'Pertanyaan berhasil dihapus');
}
public function fieldsReorder(Request $request, Event $event)
{
    $ids = $request->input('ids', []);
    if (!is_array($ids) || count($ids) === 0) {
        return response()->json(['ok' => false, 'message' => 'Invalid payload'], 422);
    }

    // Pastikan semua id milik event ini
    $count = $event->fields()->whereIn('id', $ids)->count();
    if ($count !== count($ids)) {
        return response()->json(['ok' => false, 'message' => 'Invalid fields'], 422);
    }

    DB::transaction(function () use ($event, $ids) {
        foreach ($ids as $i => $id) {
            $event->fields()->where('id', $id)->update(['order_index' => $i + 1]);
        }
    });

    return response()->json(['ok' => true]);
}
public function edit(Event $event)
{
    return view('dashboard.events.edit', compact('event'));
}

public function update(Request $request, Event $event)
{
    $data = $request->validate([
        'status' => ['nullable','string'],
        'title'  => 'required|string|max:255',
        'slug'   => 'required|string|max:255|unique:events,slug,' . $event->id,
        'intro_html' => 'nullable|string',
        'active_until' => 'nullable|string',
        'price_question_label' => 'nullable|string|max:150',
    ]);
    // Update normal field
    $status = $data['status'] ?? null;
    
    $user = auth()->user();
    if(!$user->isAdmin()){
        
        unset($data['status']);
    }
    $event->update($data);


    return back()->with('success', 'Event tersimpan.');
}


}
