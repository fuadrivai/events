<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPriceOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventPriceOptionController extends Controller
{
    private function loadEvent(string $eventId): Event
    {
        return Event::query()->where('id', $eventId)->firstOrFail();
    }

    private function generateUniqueCode(Event $event, ?string $preferredCode = null): string
    {
        $candidate = trim((string) ($preferredCode ?? ''));

        if ($candidate === '') {
            $candidate = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } else {
            $candidate = strtoupper(preg_replace('/[^A-Z0-9]/', '', $candidate));
            if ($candidate === '') {
                $candidate = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            }
        }

        $candidate = substr($candidate, 0, 12);
        $suffix = 2;

        while (EventPriceOption::where('event_id', $event->id)
            ->whereRaw('UPPER(code) = ?', [$candidate])
            ->exists()) {
            $candidate = substr($candidate, 0, 9) . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function index(string $orgSlug, string $eventId)
    {
        $event = $this->loadEvent($eventId);

        $prices = EventPriceOption::query()
            ->where('event_id', $event->id)
            ->orderBy('order_index')
            ->get();

        return view('org.events.prices.index', compact('event', 'prices', 'orgSlug'));
    }

    public function store(Request $request, string $orgSlug, string $eventId)
    {
        $event = $this->loadEvent($eventId);

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'sales_start_at' => ['nullable', 'date'],
            'sales_end_at' => ['nullable', 'date', 'after_or_equal:sales_start_at'],
        ]);

        $code = $this->generateUniqueCode($event, $data['code'] ?? null, $data['name']);
        $maxOrder = EventPriceOption::where('event_id', $event->id)->max('order_index') ?? 0;

        EventPriceOption::create([
            'event_id' => $event->id,
            'code' => $code,
            'name' => $data['name'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'is_active' => $data['is_active'] ?? true,
            'quota' => $data['quota'] ?? null,
            'sales_start_at' => $data['sales_start_at'] ?? null,
            'sales_end_at' => $data['sales_end_at'] ?? null,
            'order_index' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Price option berhasil ditambahkan.');
    }

    public function update(Request $request, string $orgSlug, string $eventId, string $priceId)
    {
        $event = $this->loadEvent($eventId);

        $price = EventPriceOption::query()
            ->where('event_id', $event->id)
            ->where('id', $priceId)
            ->firstOrFail();

        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('event_price_options', 'code')
                    ->where('event_id', $event->id)
                    ->ignore($price->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'sales_start_at' => ['nullable', 'date'],
            'sales_end_at' => ['nullable', 'date', 'after_or_equal:sales_start_at'],
        ]);

        $price->update([
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? $price->currency,
            'quota' => $data['quota'] ?? null,
            'sales_start_at' => $data['sales_start_at'] ?? null,
            'sales_end_at' => $data['sales_end_at'] ?? null,
        ]);

        return back()->with('success', 'Price option berhasil diupdate.');
    }

    public function toggle(string $orgSlug, string $eventId, string $priceId)
    {
        $event = $this->loadEvent($eventId);

        $price = EventPriceOption::query()
            ->where('event_id', $event->id)
            ->where('id', $priceId)
            ->firstOrFail();

        $price->is_active = !$price->is_active;
        $price->save();

        return back()->with('success', 'Status price option berhasil diubah.');
    }

    public function reorder(Request $request, string $orgSlug, string $eventId)
    {
        $event = $this->loadEvent($eventId);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = EventPriceOption::where('event_id', $event->id)
            ->whereIn('id', $data['ids'])
            ->count();

        if ($count !== count($data['ids'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid ids'], 422);
        }

        DB::transaction(function () use ($data, $event) {
            foreach ($data['ids'] as $i => $id) {
                EventPriceOption::where('event_id', $event->id)
                    ->where('id', $id)
                    ->update(['order_index' => $i + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
