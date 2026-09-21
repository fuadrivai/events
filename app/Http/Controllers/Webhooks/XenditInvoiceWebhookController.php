<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EventEmailTemplate;
use App\Services\EmailTemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class XenditInvoiceWebhookController extends Controller
{
    public function handle(Request $request)
    {

        $invoiceId = $request->input('id'); // Xendit invoice id
        $status    = strtoupper((string) $request->input('status')); // PAID / EXPIRED
        $paidAt    = $request->input('paid_at'); // optional
        $payload   = $request->all();

        if (!$invoiceId) {
            return response()->json(['ok' => true]); // ack cepat
        }

        // 3) Find payment (idempotency by invoice_id)
        $externalId = $request->input('external_id');
        $payment = Payment::where('invoice_id', $invoiceId)
            ->when($externalId, function ($query) use ($externalId) {
                $query->orWhere('external_id', $externalId);
            })
            ->first();
        if (!$payment) {
            Log::warning('Xendit webhook payment not found', [
                'invoice_id' => $invoiceId,
                'external_id' => $request->input('external_id'),
            ]);
            return response()->json(['ok' => false, 'message' => 'Payment not found'], 500);
        }

        // 4) Idempotent no-op
        if (
            $payment->status === 'PAID'
            && $status === 'PAID'
            && data_get($payment->payload_json, 'payment_email_sent_at')
        ) {
            return response()->json(['ok' => true]);
        }

        // 5) Apply status
        if ($status === 'PAID') {

            DB::transaction(function () use ($payment, $payload, $paidAt) {
                $payment->update([
                    'status' => 'PAID',
                    'paid_at' => $paidAt ? \Carbon\Carbon::parse($paidAt) : now(),
                    'payload_json' => $payload,
                ]);

                $payment->registration()->update([
                    'status' => 'PAID',
                ]);
            });

            // Load relasi untuk renderer & PDF
            $payment->loadMissing(['registration.event', 'registration.answers']);
            $registration = $payment->registration;

            if (!$registration) {
                Log::error('Xendit webhook registration not found for payment', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'registration_id' => $payment->registration_id,
                ]);
                return response()->json(['ok' => false, 'message' => 'Registration not found'], 500);
            }

            $event = $registration->event;

            $template = EventEmailTemplate::where('event_id', $registration->event_id)
                ->where('key', 'PAYMENT_PAID')
                ->first();

            if (!$template) {
                Log::error('PAYMENT_PAID email template not found', [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'payment_id' => $payment->id,
                ]);

                return response()->json(['ok' => false, 'message' => 'PAYMENT_PAID template not found'], 500);
            }

            $renderer = app(EmailTemplateRenderer::class);

                $subject = $renderer->render($template->subject, $registration, $payment);
                $content = $renderer->render($template->body, $registration, $payment);

                // ===== PDF receipt =====
                $logoPath = public_path('assets/logo_mh.png');
                $imageDataUri = null;

                if (is_file($logoPath)) {
                    $b64 = base64_encode(file_get_contents($logoPath));
                    if ($b64 !== '') {
                        $imageDataUri = 'data:image/png;base64,' . $b64;
                    }
                }

                $paidAt = $payment->paid_at ?? now();
                $amount = (int) $payment->amount;
                $total = (int) data_get($payment->payload_json, 'amount', 0);
                $total = $total > 0 ? $total : $amount + (optional($event)->included_admin_fee ? 3663 : 0);
                $adminFee = max(0, $total - $amount);

                $pdfHtml = view('pdf.receipt', [
                    'image_data_uri' => $imageDataUri,
                    'transaction_timestamp' => $paidAt->format('d M Y H:i'),
                    'external_id' => $payment->external_id ?? $payment->invoice_id,
                    'dateResv' => $paidAt->format('d M Y'),
                    'timeResv' => $paidAt->format('H:i'),
                    'description' => 'Registration for ' . (optional($event)->title ?? 'Event'),
                    'fee'   => number_format($amount, 0, ',', '.'),
                    'bank'  => number_format($adminFee, 0, ',', '.'),
                    'include_admin_fee' => $adminFee > 0,
                    'total' => number_format($total, 0, ',', '.'),
                ])->render();

                $pdfBytes = Pdf::loadHTML($pdfHtml)
                    ->setPaper('A4')
                    ->output();

                // ===== Wrap email with default header/footer =====
                $html = view('emails.layouts.default', [
                    'event' => $event ?? (object) ['title' => 'Mutiara Harapan Islamic School'],
                    'subject' => $subject,
                    'content' => $content,
                    'registration' => $registration,
                    'payment' => $payment,
                ])->render();

            Mail::html($html, function ($message) use ($registration, $subject, $pdfBytes) {
                $message->to($registration->email)
                    ->subject($subject)
                    ->attachData($pdfBytes, 'payment-receipt.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            $payment->update([
                'payload_json' => array_merge($payment->payload_json ?? [], [
                    'payment_email_sent_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Xendit payment receipt email sent', [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'registration_id' => $registration->id,
                'recipient' => $registration->email,
            ]);

            return response()->json(['ok' => true]);
        }

        if ($status === 'EXPIRED') {
            DB::transaction(function () use ($payment, $payload) {
                if ($payment->status !== 'PAID') {
                    $payment->update([
                        'status' => 'EXPIRED',
                        'payload_json' => $payload,
                    ]);

                    $payment->registration()->update([
                        'status' => 'EXPIRED',
                    ]);
                }
            });

            return response()->json(['ok' => true]);
        }

        // status lain: simpan payload saja
        $payment->update(['payload_json' => $payload]);

        return response()->json(['ok' => true]);
    }
}
