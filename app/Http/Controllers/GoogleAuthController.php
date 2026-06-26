<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GoogleAuthController extends Controller
{
    private function getClient(): Client
    {
        $client = new Client();
        
        $secretPath = config('services.google.client_secret_path', 'storage/app/google/client_secret.json');
        if (!str_starts_with($secretPath, '/')) {
            $secretPath = base_path($secretPath);
        }

        if (!file_exists($secretPath)) {
            abort(500, "OAuth Client Secret not found at: {$secretPath}. Please download it from Google Cloud Console.");
        }

        $client->setAuthConfig($secretPath);
        
        // This generates an absolute URL based on the current environment (local or production domain)
        $client->setRedirectUri(url('/google/callback'));
        
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            \Google\Service\Calendar::CALENDAR,
            \Google\Service\Calendar::CALENDAR_EVENTS,
        ]);

        return $client;
    }

    public function auth()
    {
        $client = $this->getClient();
        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return "Error from Google: " . $request->get('error');
        }

        if (!$request->has('code')) {
            return "No authorization code provided.";
        }

        $client = $this->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        if (array_key_exists('error', $token)) {
            return "Error fetching token: " . json_encode($token);
        }

        // Save token to file
        $tokenPath = config('services.google.token_path', 'storage/app/google/token.json');
        if (!str_starts_with($tokenPath, '/')) {
            $tokenPath = base_path($tokenPath);
        }

        File::put($tokenPath, json_encode($token));

        return "<h1>Success!</h1><p>Google authentication complete. The token has been saved. You can now close this window and try booking an online meeting.</p>";
    }
}
