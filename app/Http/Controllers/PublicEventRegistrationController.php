<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventRegistration;
use App\Models\EventRegistrationAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\EventPriceOption;
use App\Models\Payment;
use App\Services\XenditInvoiceService;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Http\Response;

class PublicEventRegistrationController extends Controller
{
    public function store(Request $request, Event $event, XenditInvoiceService $xendit)
    {
        if (! $event->isRegistrationOpen()) {
            abort(Response::HTTP_GONE);
        }

        $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150'],
            'phone' => ['required','string','max:30'],
            'answers' => ['nullable','array'],
        ]);

        // ambil price option aktif untuk event ini
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

            $selectedPrice = $priceOptions->firstWhere('id', (int) $request->input('price_option_id'));
            if (!$selectedPrice) {
                return back()->withErrors(['price_option_id' => 'Pilihan harga tidak valid.'])->withInput();
            }
        }

        $answers = $request->input('answers', []);

        // =========================
        // TX #1: create registration + answers
        // =========================
        $registration = DB::transaction(function () use ($event, $answers, $request, $selectedPrice) {

            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'active_until' => $event->active_until??null,
                // status awal
                'status' => 'SUBMITTED',

                'registration_code' => strtoupper(Str::random(8)),
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),

                // snapshot pricing (kalau ada)
                'price_option_id' => $selectedPrice ? $selectedPrice->id : null,
                'price_name_snapshot' => $selectedPrice ? $selectedPrice->name : null,
                'amount_snapshot' => $selectedPrice ? (int) $selectedPrice->amount : 0,
            ]);

            foreach ($answers as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                EventRegistrationAnswer::create([
                    'registration_id' => $registration->id,
                    'field_key' => (string) $key,
                    'value' => (string) $value,
                ]);
            }

            return $registration;
        });

        // =========================
        // Jika gratis (amount 0): selesai
        // =========================
        $amount = (int) ($registration->amount_snapshot ?? 0);
        if ($amount <= 0) {
            $registration->update(['status' => 'PAID']);
            return back()->with('success', 'Data pendaftaran telah kami terima. (Event gratis)');
        }

        // =========================
        // Create Xendit invoice (OUTSIDE TX)
        // =========================
        $activeUntil = Carbon::parse($event->active_until, 'Asia/Jakarta');
        $durationInSeconds = max(0, (int) now()->diffInSeconds($activeUntil, false));

        $externalId = $event->slug . '-' . $registration->id;
        $adminFee = $event->included_admin_fee ? 3663 : 0;
        $totalAmount = (int) $amount + $adminFee;
        $branch = $event->owner?->branch;
        try {
            $invoice = $xendit->createInvoice([
                'external_id' => $externalId,
                'amount' => $totalAmount,
                'payer_email' => $registration->email,
                'description' => $request->input('name').' - Registration for ' . ($event->title ?? 'Event'),
                'success_redirect_url' => url('/payment/success'),
                'failure_redirect_url' => url('/payment/failed'),
                'invoice_duration' => $durationInSeconds,
                'token'=> $branch?->xendit_token??null,
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['payment' => 'Gagal membuat invoice pembayaran. Silakan coba lagi.'])
                ->withInput();
        }

        $invoiceId  = $invoice['id'] ?? null;
        $invoiceUrl = $invoice['invoice_url'] ?? null;

        if (!$invoiceId || !$invoiceUrl) {
            return back()->withErrors(['payment' => 'Response invoice tidak lengkap (id/url).'])
                ->withInput();
        }

        // =========================
        // TX #2: save payment + update status INVOICED
        // =========================
        DB::transaction(function () use ($registration, $invoiceId, $invoiceUrl, $invoice, $externalId, $amount) {
            Payment::create([
                'registration_id' => $registration->id,
                'provider' => 'xendit',
                'invoice_id' => $invoiceId,
                'external_id' => $externalId,
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => 'PENDING',
                'invoice_url' => $invoiceUrl,
                'payload_json' => $invoice,
                'active_until' => $registration->active_until??null,
            ]);

            $registration->update([
                'status' => 'INVOICED',
            ]);
        });

        // =========================
        // Send Email: INVOICE_CREATED (with default header/footer)
        // =========================
        $payment = Payment::where('registration_id', $registration->id)->latest('id')->first();
        $template = \App\Models\EventEmailTemplate::where('event_id', $event->id)
            ->where('key', 'INVOICE_CREATED')
            ->first();

        if ($payment) {
            $renderer = app(\App\Services\EmailTemplateRenderer::class);

            $subject = $template
                ? $renderer->render($template->subject, $registration, $payment)
                : 'Invoice Registration - ' . ($event->title ?? 'Event');
            $content = $template
                ? $renderer->render($template->body, $registration, $payment)
                : '<p>Halo ' . e($registration->name) . ',</p>'
                    . '<p>Invoice pendaftaran untuk <strong>' . e($event->title ?? 'Event') . '</strong> telah dibuat.</p>'
                    . '<p>Silakan lanjutkan pembayaran melalui tombol berikut:</p>'
                    . '<p><a href="' . e($payment->invoice_url) . '">Bayar Sekarang / Pay Now</a></p>';

            // Wrap dengan layout default (header & footer)
            $html = view('emails.layouts.default', [
                'event'   => $event,
                'active_until' => $payment->active_until??null,
                'subject' => $subject,
                'content' => $content,
                'registration' => $registration,
                'payment' => $payment,
            ])->render();

            Mail::html($html, function ($message) use ($registration, $subject) {
                $message->to($registration->email)->subject($subject);
            });
        }

        // Redirect langsung ke halaman bayar Xendit
        return redirect()->away($invoiceUrl);
    }
}
