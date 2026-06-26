<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class GoogleMeetService
{
    private ?Client $client = null;
    private string $calendarId;

    public function __construct()
    {
        $this->calendarId = config('services.google.calendar_id', 'primary');
    }

    /**
     * Boot the Google Client using OAuth credentials if available, falling back to service account.
     */
    private function bootClient(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $this->client = new Client();
        $this->client->setAccessType('offline');
        $this->client->setScopes([
            Calendar::CALENDAR,
            Calendar::CALENDAR_EVENTS,
        ]);

        $tokenPath = config('services.google.token_path', 'storage/app/google/token.json');
        if (!str_starts_with($tokenPath, '/')) {
            $tokenPath = base_path($tokenPath);
        }

        $clientSecretPath = config('services.google.client_secret_path', 'storage/app/google/client_secret.json');
        if (!str_starts_with($clientSecretPath, '/')) {
            $clientSecretPath = base_path($clientSecretPath);
        }

        // 1. Try OAuth 2.0 flow (if token exists)
        if (file_exists($tokenPath) && file_exists($clientSecretPath)) {
            $this->client->setAuthConfig($clientSecretPath);
            
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);

            // Refresh the token if it's expired
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    // Save the new token
                    file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                } else {
                    throw new \RuntimeException("Google OAuth token is expired and no refresh token is available. Please re-authenticate.");
                }
            }
            return $this->client;
        }

        // 2. Fallback to Service Account flow
        $credentialsPath = config('services.google.credentials_path', 'storage/app/google/google-service-account.json');
        if (!str_starts_with($credentialsPath, '/')) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException("Neither OAuth token ({$tokenPath}) nor Service Account JSON ({$credentialsPath}) found.");
        }

        $this->client->setAuthConfig($credentialsPath);

        $impersonate = config('services.google.impersonate_email');
        if (!empty($impersonate)) {
            $this->client->setSubject($impersonate);
        }

        return $this->client;
    }

    /**
     * Check if the Google service is connected (either OAuth token or Service Account exists).
     */
    public function isConnected(?int $userId = null): bool
    {
        try {
            $tokenPath = config('services.google.token_path', 'storage/app/google/token.json');
            if (!str_starts_with($tokenPath, '/')) {
                $tokenPath = base_path($tokenPath);
            }
            if (file_exists($tokenPath)) {
                return true;
            }

            $credentialsPath = config('services.google.credentials_path', 'storage/app/google/google-service-account.json');
            if (!str_starts_with($credentialsPath, '/')) {
                $credentialsPath = base_path($credentialsPath);
            }
            return file_exists($credentialsPath);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Create a Google Calendar event with a Google Meet link.
     *
     * @return array{url: string|null, code: string|null, password: string|null, event_id: string|null}
     */
    public function createMeet(
        string $summary,
        Carbon $start,
        Carbon $end,
        ?string $description = null,
        array $attendeesEmails = []
    ): array {
        $client = $this->bootClient();
        $service = new Calendar($client);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $attendees = array_map(fn($e) => ['email' => trim($e)], $attendeesEmails);

        $event = new Event([
            'summary'     => $summary,
            'description' => $description,
            'start'       => [
                'dateTime' => $start->copy()->timezone($tz)->toRfc3339String(),
                'timeZone' => $tz,
            ],
            'end'         => [
                'dateTime' => $end->copy()->timezone($tz)->toRfc3339String(),
                'timeZone' => $tz,
            ],
            'attendees'      => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId'            => (string) Str::uuid(),
                ],
            ],
        ]);

        $created = $service->events->insert($this->calendarId, $event, [
            'conferenceDataVersion' => 1,
            'sendUpdates'           => empty($attendees) ? 'none' : 'all',
        ]);

        return [
            'url'      => $created->hangoutLink ?? null,
            'code'     => null,
            'password' => null,
            'event_id' => $created->id ?? null,
        ];
    }

    /**
     * Delete a Google Calendar event (to cancel a meeting).
     */
    public function deleteMeet(string $eventId): bool
    {
        try {
            $client = $this->bootClient();
            $service = new Calendar($client);
            $service->events->delete($this->calendarId, $eventId);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}
