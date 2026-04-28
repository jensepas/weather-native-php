<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;

class SunCalc
{
    use AstronomyUtils;

    const float J0 = 0.0009;

    private array $times = [
        [-0.833, 'sunrise', 'sunset'],
        [-0.3, 'sunriseEnd', 'sunsetStart'],
        [-6, 'dawn', 'dusk'],
        [-12, 'nauticalDawn', 'nauticalDusk'],
        [-18, 'nightEnd', 'night'],
        [6, 'goldenHourEnd', 'goldenHour']
    ];

    public function getPosition(DateTime $date, $lat, $lng): array
    {
        $lw = self::RAD * -$lng;
        $phi = self::RAD * $lat;
        $d = $this->toDays($date);

        $c = $this->sunCoords($d);
        $h = $this->siderealTime($d, $lw) - $c['ra'];

        return [
            'azimuth' => $this->azimuth($h, $phi, $c['dec']),
            'altitude' => $this->altitude($h, $phi, $c['dec'])
        ];
    }

    public function sunCoords($d): array
    {
        $m = $this->solarMeanAnomaly($d);
        $l = $this->eclipticLongitude($m);
        return [
            'dec' => $this->declination($l, 0),
            'ra' => $this->rightAscension($l, 0)
        ];
    }

    public function solarMeanAnomaly($d): float
    {
        return self::RAD * (357.5291 + 0.98560028 * $d);
    }

    public function eclipticLongitude($m): float
    {
        $c = self::RAD * (1.9148 * sin($m) + 0.02 * sin(2 * $m) + 0.0003 * sin(3 * $m));
        $p = self::RAD * 102.9372;
        return $m + $c + $p + self::PI;
    }

    /**
     * @throws Exception
     */
    public function getTimes(DateTime $date, $lat, $lng, $timezone = 'UTC', $height = 0): array
    {
        $tz = new DateTimeZone($timezone);

        // Stabilisation de la date : on force à 12:00:00 UTC pour le calcul du jour julien,
        // car le jour julien commence à midi UTC. Si on est trop tôt ou trop tard (fuseau horaire),
        // le calcul julianCycle peut sauter d'un jour.
        $t = clone $date;
        $t->setTime(12, 0);
        $t->setTimezone(new DateTimeZone('UTC'));

        $lw = self::RAD * -$lng;
        $phi = self::RAD * $lat;
        $dh = $this->observerAngle($height);
        $d = $this->toDays($t);

        $n = $this->julianCycle($d, $lw);
        $ds = $this->approxTransit(0, $lw, $n);
        $m = $this->solarMeanAnomaly($ds);
        $l = $this->eclipticLongitude($m);
        $dec = $this->declination($l, 0);
        $noon = $this->solarTransitJ($ds, $m, $l);

        $result = [
            'solarNoon' => $this->fromJulian($noon, $tz),
            'nadir' => $this->fromJulian($noon - 0.5, $tz)
        ];

        foreach ($this->times as $time) {
            $h0 = ($time[0] + $dh) * self::RAD;

            $set = $this->getSetJ($h0, $lw, $phi, $dec, $n, $m, $l);

            if ($set === null) {
                $result[$time[1]] = null;
                $result[$time[2]] = null;
                continue;
            }

            $rise = $noon - ($set - $noon);

            $result[$time[1]] = $this->fromJulian($rise, $tz);
            $result[$time[2]] = $this->fromJulian($set, $tz);
        }

        return $result;
    }

    public function observerAngle($height): float
    {
        return -2.076 * sqrt($height) / 60;
    }

    public function julianCycle($d, $lw): float
    {
        return round($d - self::J0 - $lw / (2 * self::PI));
    }

    public function approxTransit($ht, $lw, $n): float
    {
        return self::J0 + ($ht + $lw) / (2 * self::PI) + $n;
    }

    public function solarTransitJ($ds, $m, $l): float
    {
        return self::J2000 + $ds + 0.0053 * sin($m) - 0.0069 * sin(2 * $l);
    }

    public function getSetJ($h, $lw, $phi, $dec, $n, $m, $l): ?float
    {
        $w = $this->hourAngle($h, $phi, $dec);

        if ($w === null) {
            return null;
        }

        $a = $this->approxTransit($w, $lw, $n);
        return $this->solarTransitJ($a, $m, $l);
    }

    public function hourAngle($h, $phi, $d): ?float
    {
        $cosH = (sin($h) - sin($phi) * sin($d)) / (cos($phi) * cos($d));

        if ($cosH > 1) {
            return null; // soleil toujours sous l’horizon
        }

        if ($cosH < -1) {
            return null; // soleil toujours au-dessus de l’horizon
        }

        return acos($cosH);
    }
}
