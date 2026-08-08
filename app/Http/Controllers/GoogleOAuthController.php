<?php

namespace App\Http\Controllers;

use App\Models\GoogleOwnerToken;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    private function client(): Client
    {
        $client = new Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));

        // WAJIB: offline agar dapat refresh_token
        $client->setAccessType('offline');
        $client->setPrompt('consent'); // agar refresh_token keluar saat pertama kali

        $client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE, // untuk create & share file
        ]);

        return $client;
    }

    // halaman connect (owner saja)
    public function connect(Request $request)
    {
        // OPTIONAL: batasi hanya user_id=1 atau role tertentu
        // abort_unless($request->user()?->id === 1, 403);

        $client = $this->client();
        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        // abort_unless($request->user()?->id === 1, 403);

        $code = $request->query('code');
        abort_unless($code, 400, 'Missing code');

        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (!empty($token['error'])) {
            abort(500, 'Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $refreshToken = $token['refresh_token'] ?? null;
        // NOTE: refresh_token biasanya hanya muncul di consent pertama / jika prompt=consent

        $expiresAt = now()->addSeconds((int)($token['expires_in'] ?? 3600));

        GoogleOwnerToken::updateOrCreate(
            ['owner_email' => config('google.owner_email')],
            [
                'access_token' => json_encode($token),
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
            ]
        );

        return redirect('/')->with('success', 'Google Owner account connected.');
    }
}
