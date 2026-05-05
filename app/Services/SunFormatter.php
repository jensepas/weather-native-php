<?php

namespace App\Services;

use DateTime;

class SunFormatter
{
    use AstronomyUtils;

    public function format(array $sunPosition, float $lat, float $lng, ?array $sunTimes = null, ?DateTime $date = null): array
    {
        $azimuth = $this->normalizeAzimuth(rad2deg($sunPosition['azimuth']));
        $altitude = rad2deg($sunPosition['altitude']);

        return [
            'azimuth' => $this->azimuthToDirection($azimuth),
            'azimuth_short' => $this->azimuthToDirectionShort($azimuth),
            'altitude' => round($altitude),
            'direction' => $this->azimuthToDirection($azimuth),
            'azimuth_deg' => round($azimuth),
            'altitude_deg' => round($altitude),
            'latitude' => $this->formatCoordinate($lat),
            'longitude' => $this->formatCoordinate($lng),
            'is_day' => $this->isDay($altitude, $sunTimes, $date),
            'label' => $this->isDay($altitude, $sunTimes, $date) ? 'Jour' : 'Nuit',
        ];
    }

    private function normalizeAzimuth(float $deg): float
    {
        // conversion SunCalc → standard (Nord = 0°)
        $az = fmod($deg + 180, 360);
        return $az < 0 ? $az + 360 : $az;
    }

    private function formatCoordinate(float $coordinate): string
    {

        $deg = floor($coordinate);
        $min = floor(($coordinate - $deg) * 60);

        return  $deg . '°' . $min . '\'';
    }

    private function isDay(float $altitudeDeg, ?array $sunTimes, ?DateTime $date): bool
    {
        // Méthode 1 (précise avec horaires)
        if ($sunTimes && $date && isset($sunTimes['sunrise'], $sunTimes['sunset'])) {
            return $date >= $sunTimes['sunrise'] && $date <= $sunTimes['sunset'];
        }

        // fallback (moins précis)
        return $altitudeDeg > 0;
    }
}
