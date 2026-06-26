<?php

namespace App\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;

class ZoomService
{
    protected Client $http;
    protected ?string $accountId;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected string $userId;

    public function __construct()
    {
        $this->http = new Client(['base_uri' => 'https://api.zoom.us']);

        // Read directly from env() as a fallback so a stale config cache
        // doesn't cause a TypeError at construction time.
        $this->accountId    = config('zoom.account_id')    ?? env('ZOOM_ACCOUNT_ID')    ?? null;
        $this->clientId     = config('zoom.client_id')     ?? env('ZOOM_CLIENT_ID')     ?? null;
        $this->clientSecret = config('zoom.client_secret') ?? env('ZOOM_CLIENT_SECRET') ?? null;
        $this->userId       = config('zoom.user_id')       ?? env('ZOOM_USER_ID', 'me') ?? 'me';
    }

    protected function validateConfig(): void
    {
        // Re-check env() directly as a last resort in case the config cache
        // was built before the .env keys were populated.
        $accountId    = $this->accountId    ?: env('ZOOM_ACCOUNT_ID');
        $clientId     = $this->clientId     ?: env('ZOOM_CLIENT_ID');
        $clientSecret = $this->clientSecret ?: env('ZOOM_CLIENT_SECRET');

        if (empty($accountId) || empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException(
                'Zoom tidak dikonfigurasi dengan benar. Pastikan ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID, dan ZOOM_CLIENT_SECRET sudah diatur.'
            );
        }

        // Sync back so getAccessToken() uses the resolved values.
        $this->accountId    = $accountId;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    protected function getAccessToken(): string
    {
        $this->validateConfig();
        $resp = $this->http->post('/oauth/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ],
            'form_params' => [
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId,
            ],
        ]);

        $json = json_decode((string) $resp->getBody(), true);
        return $json['access_token'];
    }

    /**
     * Create a Zoom meeting
     * @return array [url, code, password]
     */
    public function createMeeting(string $topic, Carbon $start, Carbon $end, ?string $agenda = null): array
    {
        $token = $this->getAccessToken();

        $duration = max(15, $end->diffInMinutes($start));
        $payload = [
            'topic' => $topic,
            'type' => 2,
            'start_time' => $start->copy()->tz('UTC')->format('Y-m-d\TH:i:s\Z'),
            'duration' => $duration,
            'timezone' => $start->getTimezone()->getName(),
            'agenda' => $agenda ?? '',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'waiting_room' => true,
                'approval_type' => 0,
            ],
        ];

        $resp = $this->http->post("/v2/users/{$this->userId}/meetings", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
        ]);

        $json = json_decode((string) $resp->getBody(), true);

        return [
            'url' => $json['join_url'] ?? null,
            'code' => $json['id'] ?? null,
            'password' => $json['password'] ?? null,
        ];
    }
}
