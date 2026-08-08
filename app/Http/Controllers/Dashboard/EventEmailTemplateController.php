<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEmailTemplate;
use Illuminate\Http\Request;
use App\Models\EventField;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Services\EmailTemplateRenderer;

class EventEmailTemplateController extends Controller
{
    public function index(string $orgSlug, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $fields = EventField::where('event_id', $event->id)
        ->orderBy('order_index')
        ->get(['field_key','label']);

        $templates = EventEmailTemplate::where('event_id', $event->id)
            ->get()
            ->keyBy('key');

        $templateKeys = [
            'INVOICE_CREATED',
            'PAYMENT_PAID',
        ];

        return view('dashboard.events.email_templates.index', [
            'orgSlug' => $orgSlug,
            'event' => $event,
            'templates' => $templates,
            'templateKeys' => $templateKeys,
            'fields' => $fields,
        ]);

    }

    public function store(Request $request, string $orgSlug, int $eventId)
    {
        $event = Event::findOrFail($eventId);

        $data = $request->validate([
            'key' => ['required','string','max:50'],
            'subject' => ['required','string','max:255'],
            'body' => ['required','string'],
        ]);

        EventEmailTemplate::updateOrCreate(
            ['event_id' => $event->id, 'key' => $data['key']],
            ['subject' => $this->cleanBladeVariables($data['subject']), 'body' =>$this->cleanBladeVariables($data['body'])]
        );

        return back()->with('success', 'Template email berhasil disimpan.');
    }
    public function preview(Request $request, string $orgSlug, int $eventId)
    {
        $event = Event::findOrFail($eventId);
    
        $data = $request->validate([
            'key' => ['required','string','max:50'],
        ]);
    
        $key = $data['key'];
    
        $template = EventEmailTemplate::where('event_id', $event->id)
            ->where('key', $key)
            ->first();
    
        if (!$template) {
            abort(404, "Template {$key} belum dibuat untuk event ini.");
        }
    
        // ambil registration terbaru untuk event ini
        $registration = EventRegistration::with(['answers','event'])
            ->where('event_id', $event->id)
            ->latest('id')
            ->first();
    
        if (!$registration) {
            return view('dashboard.events.email_templates.preview', [
                'event' => $event,
                'key' => $key,
                'subject' => '[Preview] Belum ada pendaftaran untuk event ini.',
                'body' => '<p>Silakan buat 1 pendaftaran dulu agar preview bisa pakai data nyata.</p>',
                'used_registration_id' => null,
            ]);
        }
    
        // ambil payment terbaru untuk registration ini (kalau ada)
        $payment = Payment::where('registration_id', $registration->id)
            ->latest('created_at')
            ->first();
    
        $renderer = app(EmailTemplateRenderer::class);
    
        $subject = $renderer->render($template->subject, $registration, $payment);
        $body    = $renderer->render($template->body, $registration, $payment);
    
        return view('dashboard.events.email_templates.preview', [
            'event' => $event,
            'key' => $key,
            'subject' => $subject,
            'body' => $body, // HTML
            'used_registration_id' => $registration->id,
        ]);
    }

    function cleanBladeVariables($text)
{
    return preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/', function ($matches) {
        $variable = $matches[1];

        // Hapus semua spasi di sekitar dot
        $variable = preg_replace('/\s*\.\s*/', '.', $variable);

        return '{{' . trim($variable) . '}}';
    }, $text);
}


}
