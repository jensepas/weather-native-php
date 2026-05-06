<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getWeather(float $lat, float $lon, string $refresh = 'false')
    {
        $cacheKey = "weather_" . md5($lat . $lon);

        if ($refresh === 'true') {
            Cache::delete($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lat, $lon) {
            try {
                $response = Http::get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    "daily" => 'temperature_2m_max,temperature_2m_min,weather_code,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,daylight_duration,sunshine_duration,uv_index_max,rain_sum,showers_sum,snowfall_sum,precipitation_sum,precipitation_hours,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max,wind_direction_10m_dominant,shortwave_radiation_sum,et0_fao_evapotranspiration,uv_index_clear_sky_max',
                    "hourly" => 'temperature_2m,uv_index,weather_code,surface_pressure,wind_speed_10m,precipitation_probability,precipitation,relative_humidity_2m,apparent_temperature,wind_gusts_10m,wind_direction_10m',
                    "current" => 'weather_code,temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,rain,showers,snowfall,cloud_cover,pressure_msl,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'timezone' => 'auto',
                    'forecast_days' => 14
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $data['cached_at'] = now()->toDateTimeString();
                    return $data;
                }

                return null;
            } catch (Exception) {
                return null;
            }
        });
    }

    public function formatForecast(array $daily): array
    {
        $forecast = [];
        $count = count($daily['time'] ?? []);
        for ($i = 0; $i < min($count, 14); $i++) {
            $forecast[] = [
                'date' => Carbon::parse($daily['time'][$i])->locale('fr')->isoFormat('ddd D'),
                'max' => $daily['temperature_2m_max'][$i],
                'min' => $daily['temperature_2m_min'][$i],
                'icon' => $this->getWeatherDescription($daily['weather_code'][$i] ?? 0),
                'uv' => $daily['uv_index_max'][$i],
                'rain_prob' => $daily['precipitation_probability_max'][$i],
                'sunrise' => Carbon::parse($daily['sunrise'][$i])->format('H:i'),
                'sunset' => Carbon::parse($daily['sunset'][$i])->format('H:i'),
            ];
        }
        return $forecast;
    }

    public function getWeatherDescription($code): array
    {
        return match (true) {
            $code == 0 => ['desc' => 'Ciel dégagé', 'icon' => 'wi wi-day-sunny'],
            $code == 1 => ['desc' => 'Clair avec quelques nuages', 'icon' => 'wi wi-day-cloudy'],
            $code == 2 => ['desc' => 'Partiellement nuageux', 'icon' => 'wi wi-day-cloudy'],
            $code == 3 => ['desc' => 'Nuageux', 'icon' => 'wi wi-day-cloudy'],
            $code == 47 => ['desc' => 'Brouillard', 'icon' => 'wi wi-day-fog'],
            $code <= 48 => ['desc' => 'Brouillard givré', 'icon' => 'wi wi-day-fog'],
            $code == 51 => ['desc' => 'bruine légère', 'icon' => 'wi wi-day-sprinkle'],
            $code == 61 => ['desc' => 'Pluie légère', 'icon' => 'wi wi-day-rain'],
            $code == 63 => ['desc' => 'Pluie modérée', 'icon' => 'wi wi-day-rain'],
            $code == 65 => ['desc' => 'fortes pluies', 'icon' => 'wi wi-day-rain'],
            $code == 80 => ['desc' => 'Averses de pluie', 'icon' => 'wi wi-day-showers'],
            $code == 81 => ['desc' => 'Averses de pluie modérés', 'icon' => 'wi wi-day-showers'],
            $code == 95 => ['desc' => 'Tempête', 'icon' => 'wi wi-day-thunderstorm'],
            $code == 96 => ['desc' => 'Orage avec grêle légère', 'icon' => 'wi wi-day-thunderstorm'],
            $code == 99 => ['desc' => 'Orage avec grêle', 'icon' => 'wi wi-day-thunderstorm'],
            default => ['desc' => 'Ciel Inconnu', 'icon' => 'wi wi-day-sunny'],
        };
    }

    public function getWeatherTheme(int $code, bool $isDay = true): array
    {
        if (!$isDay) {
            return [
                'bg' => 'bg-gradient-to-br from-slate-900/80 via-indigo-950/80 to-black/80',
                'text' => 'text-white',
            ];
        }

        return match (true) {
            $code === 0 => [
                'bg' => 'bg-gradient-to-br from-yellow-400/30 via-orange-500/30 to-red-500/30',
                'text' => '',
            ],
            $code <= 3 => [
                'bg' => 'bg-gradient-to-br from-blue-400/30 via-sky-500/30 to-indigo-600/30',
                'text' => '',
            ],
            $code <= 48 => [
                'bg' => 'bg-gradient-to-br from-gray-400/30 via-gray-600/30 to-slate-800/30',
                'text' => '',
            ],
            $code <= 67 => [
                'bg' => 'bg-gradient-to-br from-slate-600/30 via-blue-800/30 to-indigo-900/30',
                'text' => '',
            ],
            $code <= 77 => [
                'bg' => 'bg-gradient-to-br from-blue-100/30 via-sky-300/30 to-blue-500/30',
                'text' => 'text-gray-900',
            ],
            $code <= 82 => [
                'bg' => 'bg-gradient-to-br from-blue-500/30 via-indigo-700/30 to-slate-900/30',
                'text' => 'text-white',
            ],
            $code <= 99 => [
                'bg' => 'bg-gradient-to-br from-purple-800/30 via-indigo-900/30 to-black/30',
                'text' => 'text-white',
            ],
            default => [
                'bg' => 'bg-gradient-to-br from-blue-400/30 to-indigo-600/30',
                'text' => 'text-white',
            ],
        };
    }
}
