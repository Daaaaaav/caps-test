<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GoogleMeetService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setScopes(explode(' ', env('GOOGLE_SCOPES')));
    }

    private function tokenPath(int $userId): string
    {
        $dir = storage_path('app/google_tokens');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir . DIRECTORY_SEPARATOR . "user_{$userId}.json";
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    //Dipanggil saat callback OAuth Google (kamu sudah punya ini)
    public function handleCallback(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('OAuth error: ' . $token['error'] . (!empty($token['error_description']) ? ' - ' . $token['error_description'] : ''));
        }
        if (!isset($token['access_token'])) {
            throw new \RuntimeException('Invalid token from Google: missing access_token.');
        }

        // If Google didn’t send refresh_token this time, keep the old one (common behavior)
        $path = $this->tokenPath(\Auth::id());
        if (empty($token['refresh_token']) && file_exists($path)) {
            $old = json_decode(file_get_contents($path), true) ?: [];
            if (!empty($old['refresh_token'])) {
                $token['refresh_token'] = $old['refresh_token'];
            }
        }

        File::put($path, json_encode($token, JSON_PRETTY_PRINT));
    }

    /**
     * Cek apakah user sudah connect Google dan token valid (auto-refresh kalau expire).
     * Return true jika siap dipakai untuk call API.
     */
    public function isConnected(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        if (!$userId)
            return false;

        $path = $this->tokenPath($userId);
        if (!file_exists($path)) {
            return false;
        }

        $token = json_decode(file_get_contents($path), true);
        if (!is_array($token) || empty($token['access_token'])) {
            return false;
        }

        $this->client->setAccessToken($token);

        if (!$this->client->getRefreshToken() && !empty($token['refresh_token'])) {
            $this->client->refreshToken($token['refresh_token']);
            $this->client->setAccessToken(array_merge($token, $this->client->getAccessToken()));
        }

        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken() ?: ($token['refresh_token'] ?? null);
            if (!$refreshToken) {

                return false;
            }
            $new = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($new['error'])) {
                return false;
            }

            $merged = array_merge($token, array_filter($new));
            if (empty($merged['refresh_token'])) {
                $merged['refresh_token'] = $refreshToken;
            }

            file_put_contents($path, json_encode($merged, JSON_PRETTY_PRINT));
            $this->client->setAccessToken($merged);
        }

        return true;
    }

    //Boot client dengan token user tertentu (dipakai internal sebelum call API)
    private function bootWithTokensFor(int $userId): void
    {
        $path = $this->tokenPath($userId);
        if (!file_exists($path)) {
            throw new \RuntimeException('Google not connected. Please connect first.');
        }

        $token = json_decode(file_get_contents($path), true);
        if (!is_array($token) || !isset($token['access_token'])) {
            throw new \RuntimeException('Invalid token format in storage. Please reconnect Google.');
        }

        $this->client->setAccessType('offline');
        $this->client->setAccessToken($token);

        if (!$this->client->getRefreshToken() && !empty($token['refresh_token'])) {
            $this->client->refreshToken($token['refresh_token']);
            $this->client->setAccessToken(array_merge($token, $this->client->getAccessToken()));
        }

        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken() ?: ($token['refresh_token'] ?? null);
            if (!$refreshToken) {
                throw new \RuntimeException('Missing refresh_token. Remove app access in Google Account, then connect again.');
            }

            $new = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($new['error'])) {
                throw new \RuntimeException('Failed refreshing token: ' . $new['error']);
            }

            $merged = array_merge($token, array_filter($new));
            if (empty($merged['refresh_token']))
                $merged['refresh_token'] = $refreshToken;

            file_put_contents($path, json_encode($merged, JSON_PRETTY_PRINT));
            $this->client->setAccessToken($merged);
        }
    }

    //Create an event with a Google Meet link.
    //Optionally pass attendees' emails to auto-invite people.

    public function createMeet(string $summary, Carbon $start, Carbon $end, ?string $description = null, array $attendeesEmails = []): array
    {
        $userId = Auth::id();
        $this->bootWithTokensFor($userId);

        $service = new Calendar($this->client);
        $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');

        $attendees = array_map(fn($e) => ['email' => trim($e)], $attendeesEmails);

        $event = new Event([
            'summary' => $summary,
            'description' => $description,
            'start' => [
                'dateTime' => $start->copy()->timezone(config('app.timezone', 'Asia/Jakarta'))->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Asia/Jakarta'),
            ],
            'end' => [
                'dateTime' => $end->copy()->timezone(config('app.timezone', 'Asia/Jakarta'))->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Asia/Jakarta'),
            ],
            'attendees' => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => (string) Str::uuid(),
                ],
            ],
        ]);

        $created = $service->events->insert($calendarId, $event, [
            'conferenceDataVersion' => 1,
            'sendUpdates' => empty($attendees) ? 'none' : 'all',
        ]);

        return [
            'url' => $created->hangoutLink ?? null,
            'code' => null,
            'password' => null,
        ];
    }
}
