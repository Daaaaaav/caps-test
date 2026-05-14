<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * BMKG Weather Service
 *
 * Fetches weather forecast data from the official BMKG open API.
 * Data source: https://api.bmkg.go.id/publik/prakiraan-cuaca
 *
 * Attribution: Data provided by BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)
 */
class WeatherService
{
    // Kebun Raya Bogor — Bogor Tengah, Kota Bogor, Jawa Barat
    const DEFAULT_ADM4 = '32.71.01.1001';
    const BMKG_API_URL = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';
    const CACHE_TTL    = 3600; // 1 hour

    /**
     * Get weather forecast for a given adm4 code.
     * Returns up to 3 days of forecasts, each with multiple time slots.
     */
    public function getForecast(string $adm4 = self::DEFAULT_ADM4): ?array
    {
        $cacheKey = "bmkg_weather_{$adm4}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($adm4) {
            try {
                $response = Http::timeout(10)
                    ->withoutVerifying() // SSL cert path broken in local Laragon — re-enable in production
                    ->get(self::BMKG_API_URL, [
                        'adm4' => $adm4,
                    ]);

                if (!$response->successful()) {
                    Log::warning('BMKG API returned non-200', ['status' => $response->status()]);
                    return null;
                }

                $raw = $response->json();
                return $this->parse($raw);

            } catch (\Exception $e) {
                Log::error('BMKG API fetch failed', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Parse raw BMKG response into a clean structure.
     */
    private function parse(array $raw): array
    {
        $lokasi = $raw['lokasi'] ?? [];
        $days   = $raw['data'][0]['cuaca'] ?? [];

        $forecast = [];

        foreach ($days as $daySlots) {
            if (empty($daySlots)) continue;

            // Group slots by date
            $date = substr($daySlots[0]['local_datetime'] ?? '', 0, 10);

            $slots = array_map(function ($slot) {
                return [
                    'local_datetime' => $slot['local_datetime'] ?? null,
                    'time_label'     => $this->timeLabel($slot['local_datetime'] ?? ''),
                    'weather'        => $slot['weather'] ?? 0,
                    'weather_desc'   => $slot['weather_desc_en'] ?? $slot['weather_desc'] ?? 'Unknown',
                    'weather_desc_id'=> $slot['weather_desc'] ?? 'Unknown',
                    'icon'           => $slot['image'] ?? null,
                    'temp'           => $slot['t'] ?? null,
                    'humidity'       => $slot['hu'] ?? null,
                    'wind_speed'     => $slot['ws'] ?? null,
                    'wind_dir'       => $slot['wd'] ?? null,
                    'visibility'     => $slot['vs_text'] ?? null,
                    'rain_mm'        => $slot['tp'] ?? 0,
                    'cloud_cover'    => $slot['tcc'] ?? null,
                ];
            }, $daySlots);

            // Pick the representative slot (midday or first available)
            $representative = $this->pickRepresentative($slots);

            $forecast[] = [
                'date'           => $date,
                'date_label'     => $this->dateLabel($date),
                'slots'          => $slots,
                'summary'        => $representative,
                'max_temp'       => max(array_column($slots, 'temp')),
                'min_temp'       => min(array_column($slots, 'temp')),
                'avg_humidity'   => round(array_sum(array_column($slots, 'humidity')) / count($slots)),
                'rain_chance'    => $this->rainChance($slots),
                'weather_icon'   => $this->weatherEmoji($representative['weather'] ?? 0),
            ];
        }

        return [
            'location' => [
                'desa'       => $lokasi['desa'] ?? '',
                'kecamatan'  => $lokasi['kecamatan'] ?? '',
                'kotkab'     => $lokasi['kotkab'] ?? '',
                'provinsi'   => $lokasi['provinsi'] ?? '',
                'lat'        => $lokasi['lat'] ?? null,
                'lon'        => $lokasi['lon'] ?? null,
                'timezone'   => $lokasi['timezone'] ?? 'Asia/Jakarta',
            ],
            'forecast'     => $forecast,
            'fetched_at'   => now()->toDateTimeString(),
            'source'       => 'BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)',
            'source_url'   => 'https://data.bmkg.go.id',
        ];
    }

    private function pickRepresentative(array $slots): array
    {
        // Prefer slot closest to 12:00 local time
        foreach ($slots as $slot) {
            $hour = (int) substr($slot['local_datetime'] ?? '', 11, 2);
            if ($hour >= 11 && $hour <= 14) return $slot;
        }
        return $slots[0] ?? [];
    }

    private function timeLabel(string $datetime): string
    {
        if (!$datetime) return '';
        $hour = (int) substr($datetime, 11, 2);
        if ($hour < 6)  return 'Early Morning';
        if ($hour < 12) return 'Morning';
        if ($hour < 15) return 'Afternoon';
        if ($hour < 18) return 'Late Afternoon';
        return 'Evening';
    }

    private function dateLabel(string $date): string
    {
        if (!$date) return '';
        $ts = strtotime($date);
        $today    = strtotime(date('Y-m-d'));
        $tomorrow = $today + 86400;

        if ($ts === $today)    return 'Today';
        if ($ts === $tomorrow) return 'Tomorrow';
        return date('D, d M', $ts);
    }

    private function rainChance(array $slots): int
    {
        $rainy = array_filter($slots, fn($s) => ($s['rain_mm'] ?? 0) > 0.5);
        return count($slots) > 0 ? (int) round(count($rainy) / count($slots) * 100) : 0;
    }

    /**
     * BMKG weather code → emoji
     * Codes: 0=clear, 1=sunny, 2=partly cloudy, 3=mostly cloudy, 4=overcast,
     *        5=haze, 10=smoke, 45=fog, 60=light rain, 61=rain, 63=heavy rain,
     *        80=isolated shower, 95=thunderstorm, 97=heavy thunderstorm
     */
    public function weatherEmoji(int $code): string
    {
        return match (true) {
            in_array($code, [0, 1])       => '☀️',
            in_array($code, [2, 3])       => '⛅',
            in_array($code, [4])          => '☁️',
            in_array($code, [5, 10, 45])  => '🌫️',
            in_array($code, [60, 61, 80]) => '🌧️',
            in_array($code, [63])         => '⛈️',
            in_array($code, [95, 97])     => '⛈️',
            default                       => '🌤️',
        };
    }
}
