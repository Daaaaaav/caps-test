<?php

namespace App\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;

class ZoomService
{
    protected Client $http;
    protected string $accountId;
    protected string $clientId;
    protected string $clientSecret;
    protected string $userId;

    public function __construct()
    {
        $this->http = new Client(['base_uri' => 'https://api.zoom.us']);
        $this->accountId = env('ZOOM_ACCOUNT_ID');
        $this->clientId = env('ZOOM_CLIENT_ID');
        $this->clientSecret = env('ZOOM_CLIENT_SECRET');
        $this->userId = env('ZOOM_USER_ID', 'me');
    }

    protected function getAccessToken(): string
    {
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
