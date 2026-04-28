<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;

trait AstronomyUtils
{
    const float PI = M_PI;
    const float RAD = M_PI / 180;
    const float DAY_MS = 1000 * 60 * 60 * 24;
    const int J1970 = 2440588;
    const int J2000 = 2451545;

    const float E = (M_PI / 180) * 23.4397; // obliquity of the Earth

    /**
     * @throws Exception
     */
    public function fromJulian($j, DateTimeZone $timezone = null): DateTime
    {
        $timestamp = ($j + 0.5 - self::J1970) * self::DAY_MS / 1000;

        $date = new DateTime('@' . (int)$timestamp); // UTC strict

        if ($timezone) {
            $date->setTimezone($timezone);
        }

        return $date;
    }

    public function toDays(DateTime $date): float|int
    {
        return $this->toJulian($date) - self::J2000;
    }

    public function toJulian(DateTime $date): float
    {
        return ($date->getTimestamp() * 1000) / self::DAY_MS - 0.5 + self::J1970;
    }

    public function rightAscension($l, $b): float
    {
        return atan2(sin($l) * cos(self::E) - tan($b) * sin(self::E), cos($l));
    }

    public function declination($l, $b): float
    {
        return asin(sin($b) * cos(self::E) + cos($b) * sin(self::E) * sin($l));
    }

    public function azimuth($h, $phi, $dec): float
    {
        return atan2(sin($h), cos($h) * sin($phi) - tan($dec) * cos($phi));
    }

    public function altitude($h, $phi, $dec): float
    {
        return asin(sin($phi) * sin($dec) + cos($phi) * cos($dec) * cos($h));
    }

    public function siderealTime($d, $lw): float
    {
        return self::RAD * (280.16 + 360.9856235 * $d) - $lw;
    }

    public function astroRefraction($h): float
    {
        if ($h < 0) {
            $h = 0;
        }
        return 0.0002967 / tan($h + 0.00312536 / ($h + 0.08901179));
    }

    public function azimuthToDirectionShort(float $deg): string
    {
        $directions = [
            'N', 'NNE', 'NE', 'ENE',
            'E', 'ESE', 'SE', 'SSE',
            'S', 'SSO', 'SO', 'OSO',
            'O', 'ONO', 'NO', 'NNO'
        ];

        return $directions[(int)round($deg / 22.5) % 16];
    }

    public function azimuthToDirection(float $deg): string
    {
        $directions = [
            'Nord',
            'Nord-nord-est',
            'Nord-est',
            'Est-nord-est',
            'Est',
            'Est-sud-est',
            'Sud-est',
            'Sud-sud-est',
            'Sud',
            'Sud-sud-ouest',
            'Sud-ouest',
            'Ouest-sud-ouest',
            'Ouest',
            'Ouest-nord-ouest',
            'Nord-ouest',
            'Nord-nord-ouest'
        ];

        return $directions[(int)round($deg / 22.5) % 16];
    }
}
