<?php

namespace App\Services;

use App\Models\EventRegistration;
use App\Models\Payment;

class EmailTemplateRenderer
{
    public function render(string $text, EventRegistration $registration, ?Payment $payment = null): string
    {
        // pastikan relasi answers & event sudah ada
        $answers = $registration->answers
            ->pluck('value', 'field_key')
            ->toArray();

        $vars = [
            'name' => (string) ($registration->name ?? ''),
            'email' => (string) ($registration->email ?? ''),
            'phone' => (string) ($registration->phone ?? ''),

            'event_title' => (string) ($registration->event?->title ?? ''),
            'event_slug'  => (string) ($registration->event?->slug ?? ''),

            'price_name' => (string) ($registration->price_name_snapshot ?? ''),
            'amount' => number_format((int) ($registration->amount_snapshot ?? 0), 0, ',', '.'),
            'invoice_url' => $this->renderInvoiceButton($payment?->invoice_url),
            'active_until' => (string) ($registration?->active_until ?? ''),
            'paid_at' => $payment?->paid_at ? $payment->paid_at->format('d M Y H:i') : '',
        ];

        // replace core vars {{var}}
        foreach ($vars as $k => $v) {
            $text = str_replace('{{'.$k.'}}', $v, $text);
        }

        // replace answers vars {{answers.field_key}}
        foreach ($answers as $k => $v) {
            $text = str_replace('{{answers.'.$k.'}}', (string) $v, $text);
        }

        return $text;
    }

    protected function renderInvoiceButton(?string $url): string
    {
        if (!$url) {
            return '';
        }

        $style = implode('; ', [
            'display: inline-block',
            'padding: 12px 24px',
            'background-color: #6959c5ff',
            'color: #ffffff',
            'text-decoration: none',
            'border-radius: 8px',
            'font-weight: 600',
            'font-size: 16px',
            'box-shadow: 0 4px 6px rgba(0,0,0,0.1)',
            'font-family: Arial, sans-serif'
        ]);

        return '<div style="text-align: center; margin: 20px 0;">' . 
               '<a href="' . htmlspecialchars($url) . '" style="' . $style . '">Bayar Sekarang / Pay Now</a>' . 
               '</div>';
    }
}
