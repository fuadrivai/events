<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPriceOption;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    public function show(string $eventSlug)
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        $fields = $event->fields()
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $priceOptions = EventPriceOption::where('event_id', $event->id)
            ->where('is_active', 1)
            ->orderBy('order_index')
            ->get();

        return view('public.event', compact('event', 'fields', 'priceOptions'));
    }

    public function register(Request $request, string $eventSlug)
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'phone' => ['required','string','max:50'],
            'answers' => ['nullable','array'],
        ]);

        $priceOptions = EventPriceOption::where('event_id', $event->id)
            ->where('is_active', 1)
            ->orderBy('order_index')
            ->get();

        $hasPrices = $priceOptions->count() > 0;

        $selectedPrice = null;
        if ($hasPrices) {
            $request->validate([
                'price_option_id' => ['required','integer'],
            ]);

            $selectedPrice = $priceOptions->firstWhere('id', (int) $request->price_option_id);
            if (!$selectedPrice) {
                return back()->withErrors(['price_option_id' => 'Pilihan harga tidak valid.'])->withInput();
            }
        }

        $reg = new EventRegistration();
        $reg->event_id = $event->id;
        $reg->name = $request->name;
        $reg->email = $request->email;
        $reg->phone = $request->phone;
        $reg->status = 'registered';

        if ($hasPrices && $selectedPrice) {
            $reg->price_option_id = $selectedPrice->id;
            $reg->price_name_snapshot = $selectedPrice->name;
            $reg->amount_snapshot = $selectedPrice->amount;
        }

        $reg->save();

        // NOTE: untuk v1 ini dulu. Nanti status jadi SUBMITTED/INVOICED saat Xendit masuk.
        return back()->with('success', 'Pendaftaran berhasil disimpan. Silakan lanjut ke pembayaran.');
    }
}
