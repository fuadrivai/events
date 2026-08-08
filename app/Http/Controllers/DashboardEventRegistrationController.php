<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class DashboardEventRegistrationController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $q = trim((string) $request->query('q', ''));

        $registrations = EventRegistration::query()
            ->where('event_id', $event->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('registration_code', 'like', "%{$q}%")
                       ->orWhereHas('answers', function ($a) use ($q) {
                           $a->where('field_key', 'like', "%{$q}%")
                             ->orWhere('value', 'like', "%{$q}%");
                       });
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString(); // penting: biar q ikut pagination

        return view('dashboard.registrations.index', compact('event', 'registrations', 'q'));
    }

    public function show(EventRegistration $registration)
    {
        $registration->load('event', 'answers');
        return view('dashboard.registrations.show', compact('registration'));
    }
}
