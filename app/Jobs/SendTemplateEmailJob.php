<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use App\Models\Payment;
use App\Services\TemplateEmailRenderer;
use App\Mail\GenericHtmlMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTemplateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $registrationId;
    public string $templateKey;
    public ?int $paymentId;

    public function __construct(int $registrationId, string $templateKey, ?int $paymentId = null)
    {
        $this->registrationId = $registrationId;
        $this->templateKey = $templateKey;
        $this->paymentId = $paymentId;
    }

    public function handle(TemplateEmailRenderer $renderer): void
    {
        $registration = EventRegistration::with(['event', 'answers'])->findOrFail($this->registrationId);

        $payment = null;
        if ($this->paymentId) {
            $payment = Payment::find($this->paymentId);
        } else {
            $payment = Payment::where('registration_id', $registration->id)->latest('id')->first();
        }

        $to = $registration->email;
        if (!$to) return;

        $rendered = $renderer->render(
            event: $registration->event,
            registration: $registration,
            payment: $payment,
            templateKey: $this->templateKey
        );

        if (!$rendered || empty($rendered['subject']) || empty($rendered['html'])) {
            // kalau template belum ada / kosong, jangan crash queue
            return;
        }

        Mail::to($to)->send(new GenericHtmlMail(
            subject: $rendered['subject'],
            html: $rendered['html'],
        ));
    }
}
