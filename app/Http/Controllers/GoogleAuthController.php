<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GoogleAuthController extends Controller
{
    /**
     * Build a Google Client from env vars (no JSON file required).
     * Falls back to a client_secret.json file if the env vars are not set,
     * so both deployment styles are supported.
     */
    private function getClient(): Client
    {
        $client = new Client();
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setRedirectUri(url('/google/callback'));
        $client->setScopes([
            \Google\Service\Calendar::CALENDAR,
            \Google\Service\Calendar::CALENDAR_EVENTS,
        ]);

        $clientId     = config('services.google.client_id')     ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

        if (!empty($clientId) && !empty($clientSecret)) {
            // Preferred: configure directly from env vars
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            return $client;
        }

        // Fallback: load from a downloaded client_secret.json file
        $secretPath = config('services.google.client_secret_path', 'storage/app/google/client_secret.json');
        if (!str_starts_with($secretPath, '/')) {
            $secretPath = base_path($secretPath);
        }

        if (!file_exists($secretPath)) {
            abort(500,
                'Google OAuth is not configured. '
                . 'Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file, '
                . 'or place a client_secret.json file at: ' . $secretPath
            );
        }

        $client->setAuthConfig($secretPath);
        return $client;
    }

    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function auth()
    {
        $client = $this->getClient();
        return redirect()->away($client->createAuthUrl());
    }

    /**
     * Handle the OAuth callback from Google, save the token, and redirect back.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()
                ->route('superadmin.settings')
                ->withErrors(['google' => 'Google auth error: ' . $request->get('error')]);
        }

        if (!$request->has('code')) {
            return redirect()
                ->route('superadmin.settings')
                ->withErrors(['google' => 'No authorization code returned from Google.']);
        }

        $client = $this->getClient();
        $token  = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        if (array_key_exists('error', $token)) {
            return redirect()
                ->route('superadmin.settings')
                ->withErrors(['google' => 'Failed to fetch token: ' . ($token['error_description'] ?? $token['error'])]);
        }

        // Persist the token so GoogleMeetService can pick it up
        $tokenPath = config('services.google.token_path', 'storage/app/google/token.json');
        if (!str_starts_with($tokenPath, '/')) {
            $tokenPath = base_path($tokenPath);
        }

        File::ensureDirectoryExists(dirname($tokenPath));
        File::put($tokenPath, json_encode($token, JSON_PRETTY_PRINT));

        return redirect()
            ->route('superadmin.settings')
            ->with('success', 'Google account connected successfully!');
    }
}
