<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EventEmailTemplate;
use App\Services\EmailTemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class XenditInvoiceWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Verify token (XENDIT_WEBHOOK_TOKEN)
        // $expected = (string) config('xendit.webhook_token');
        // $received = (string) ($request->header('x-callback-token') ?? '');

        // if ($expected === '' || !hash_equals($expected, $received)) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // 2) Parse payload
        $invoiceId = $request->input('id'); // Xendit invoice id
        $status    = strtoupper((string) $request->input('status')); // PAID / EXPIRED
        $paidAt    = $request->input('paid_at'); // optional
        $payload   = $request->all();

        if (!$invoiceId) {
            return response()->json(['ok' => true]); // ack cepat
        }

        // 3) Find payment (idempotency by invoice_id)
        $payment = Payment::where('invoice_id', $invoiceId)->first();
        if (!$payment) {
            return response()->json(['ok' => true]); // ack biar xendit gak retry
        }

        // 4) Idempotent no-op
        if ($payment->status === 'PAID' && $status === 'PAID') {
            return response()->json(['ok' => true]);
        }

        // 5) Apply status
        if ($status === 'PAID') {

            // ambil status sebelum update (untuk idempotent email)
            $wasPaid = ($payment->status === 'PAID');

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

            // Kalau sebelumnya sudah PAID, stop (hindari kirim email/PDF dobel)
            if ($wasPaid) {
                return response()->json(['ok' => true]);
            }

            // Load relasi untuk renderer & PDF
            $payment->loadMissing(['registration.event', 'registration.answers']);
            $registration = $payment->registration;
            $event = $registration?->event;

            $template = EventEmailTemplate::where('event_id', $registration->event_id)
                ->where('key', 'PAYMENT_PAID')
                ->first();

            if ($template) {
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

                $adminFee = $event?->included_admin_fee ? 3663 : 0;
                $amount   = (int) $payment->amount;
                $total    = $amount + $adminFee;

                $pdfHtml = view('pdf.receipt', [
                    'image_data_uri' => $imageDataUri,
                    'transaction_timestamp' => $payment->paid_at?->format('d M Y H:i') ?? now()->format('d M Y H:i'),
                    'external_id' => $payment->external_id ?? $payment->invoice_id,
                    'dateResv' => $payment->paid_at?->format('d M Y') ?? now()->format('d M Y'),
                    'timeResv' => $payment->paid_at?->format('H:i') ?? now()->format('H:i'),
                    'description' => 'Registration for ' . ($event?->title ?? 'Event'),
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
            }

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
