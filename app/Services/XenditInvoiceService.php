<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class XenditInvoiceService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = (string) config('xendit.secret_key');

        if (!$this->secretKey) {
            throw new \RuntimeException('XENDIT_SECRET_KEY belum di-set di .env');
        }
    }

    /**
     * Create Xendit Invoice
     *
     * @param array $payload
     * @return array
     */
    public function createInvoice(array $payload): array
    {
        $body = [
            'external_id' => $payload['external_id'],
            'amount' => (int) $payload['amount'],
            'payer_email' => $payload['payer_email'] ?? null,
            'description' => $payload['description'] ?? 'Event Registration',
            'invoice_duration' => $payload['invoice_duration'] ?? 'Event Registration',
        ];
    
        // ⛔ jangan kirim kalau tidak ada
        if (!empty($payload['success_redirect_url'])) {
            $body['success_redirect_url'] = $payload['success_redirect_url'];
        }
    
        if (!empty($payload['failure_redirect_url'])) {
            $body['failure_redirect_url'] = $payload['failure_redirect_url'];
        }

        $token = $payload['token'] ?? $this->secretKey;
    
        $response = Http::withBasicAuth($token, '')
            ->acceptJson()
            ->post('https://api.xendit.co/v2/invoices', $body);
    
        if (!$response->successful()) {
            throw new \RuntimeException(
                'Xendit create invoice failed: ' . $response->body()
            );
        }
    
        return $response->json();
    }

}
