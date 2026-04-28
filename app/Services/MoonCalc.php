<?php

namespace App\Services;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class MoonCalc
{
    use AstronomyUtils;

    /**
     * @throws Exception
     */
    public function getTimes(DateTime|string $date, $lat, $lng, $timezone = 'UTC'): array
    {
        $t = $date instanceof DateTime ? clone $date : new DateTime($date);
        $t->setTime(12, 0, 0);
        $t->setTimezone(new DateTimeZone($timezone));

        $rad = pi() / 180;
        $hc = 0.133 * $rad;
        $h0 = $this->getPosition($t, $lat, $lng)['altitude'] - $hc;
        $distance = $this->getPosition($t, $lat, $lng)['distance'];
        $rise = null;
        $set = null;

        for ($i = 1; $i <= 24; $i += 2) {
            $h1 = $this->getPosition($this->hoursLater($t, $i), $lat, $lng)['altitude'] - $hc;
            $h2 = $this->getPosition($this->hoursLater($t, $i + 1), $lat, $lng)['altitude'] - $hc;
            $a = ($h0 + $h2) / 2 - $h1;
            $b = ($h2 - $h0) / 2;
            $xe = -$b / (2 * $a);
            $d = $b * $b - 4 * $a * $h1;
            $roots = 0;
            $x1 = 0;
            $x2 = 0;
            $ye = ($a * $xe + $b) * $xe + $h1;

            if ($d >= 0) {
                $dx = sqrt($d) / (abs($a) * 2);
                $x1 = $xe - $dx;
                $x2 = $xe + $dx;
                if (abs($x1) <= 1) {
                    $roots++;
                }
                if (abs($x2) <= 1) {
                    $roots++;
                }
                if ($x1 < -1) {
                    $x1 = $x2;
                }
            }

            if ($roots === 1) {
                if ($h0 < 0) {
                    $rise = $i + $x1;
                } else {
                    $set = $i + $x1;
                }
            } elseif ($roots === 2) {
                $rise = $i + ($ye < 0 ? $x2 : $x1);
                $set = $i + ($ye < 0 ? $x1 : $x2);
            }

            if ($rise !== null && $set !== null) {
                break;
            }

            $h0 = $h2;
        }

        $result['moonset'] = $result['moonrise'] = $result['moonset_next'] = null;

        $result = [];
        if ($rise !== null) {
            $result['moonrise'] = $this->hoursLater($t, $rise);
        }

        if ($set === null) {
            $nextDay = (clone $t)->modify('+1 day');
            $next = $this->getTimes($nextDay, $lat, $lng, $timezone);

            if (isset($next['moonset'])) {
                $result['moonset_next'] = $next['moonset'];
            }
        } else {
            $result['moonset'] = $this->hoursLater($t, $set);
        }

        $illumination = $this->getIllumination($t);

        if ($illumination) {
            $moonPhaseLabelIcon = $this->getMoonPhaseLabelIcon($illumination['phase']);
            $result['phase'] = $illumination['phase'];
            $result['fraction'] = $illumination['fraction'];
            $result['angle'] = $illumination['angle'];
            $result['icon'] = $moonPhaseLabelIcon['icon'];
            $result['label'] = $moonPhaseLabelIcon['label'];
            $result['distance'] = $distance;
        }

        if ($rise === null && $set === null) {
            $result['alwaysUp'] = $ye > 0;
        }

        return $result;
    }

    public function getPosition(DateTime $date, $lat, $lng): array
    {
        $lw = self::RAD * -$lng;
        $phi = self::RAD * $lat;
        $d = $this->toDays($date);
        $c = $this->moonCoords($d);
        $h = $this->siderealTime($d, $lw) - $c['ra'];
        $h = $this->altitude($h, $phi, $c['dec']);
        $pa = atan2(sin($h), tan($phi) * cos($c['dec']) - sin($c['dec']) * cos($h));

        return [
            'azimuth' => $this->azimuth($h, $phi, $c['dec']),
            'altitude' => $h + $this->astroRefraction($h),
            'distance' => $c['dist'],
            'parallacticAngle' => $pa
        ];
    }

    public function moonCoords($d): array
    {
        $l = self::RAD * (218.316 + 13.176396 * $d);
        $m = self::RAD * (134.963 + 13.064993 * $d);
        $f = self::RAD * (93.272 + 13.229350 * $d);

        $l = $l + self::RAD * 6.289 * sin($m);
        $b = self::RAD * 5.128 * sin($f);
        $dt = 385001 - 20905 * cos($m);

        return [
            'ra' => $this->rightAscension($l, $b),
            'dec' => $this->declination($l, $b),
            'dist' => $dt
        ];
    }

    /**
     * @throws Exception
     */
    private function hoursLater(DateTime $date, $h): DateTime
    {
        $interval = new DateInterval('PT' . (int)($h * 3600) . 'S');
        $newDate = clone $date;
        $newDate->add($interval);

        return $newDate;
    }

    public function getIllumination(DateTime $date = null): array
    {
        if ($date === null) {
            $date = new DateTime();
        }

        $d = $this->toDays($date);

        $sunCalc = new SunCalc();
        $s = $sunCalc->sunCoords($d);
        $m = $this->moonCoords($d);

        $dist = 149598000;

        $phi = acos(sin($s['dec']) * sin($m['dec']) + cos($s['dec']) * cos($m['dec']) * cos($s['ra'] - $m['ra']));
        $inc = atan2($dist * sin($phi), $m['dist'] - $dist * cos($phi));
        $angle = atan2(
            cos($s['dec']) * sin($s['ra'] - $m['ra']),
            sin($s['dec']) * cos($m['dec']) - cos($s['dec']) * sin($m['dec']) * cos($s['ra'] - $m['ra'])
        );

        return [
            'fraction' => (1 + cos($inc)) / 2,
            'phase' => 0.5 + 0.5 * $inc * ($angle < 0 ? -1 : 1) / M_PI,
            'angle' => $angle
        ];
    }

    private function getMoonPhaseLabelIcon($phase): array
    {
        $phases = [
            ['icon' => 'wi wi-moon-alt-waxing-crescent-1', 'label' => 'Premier croissant'],
            ['icon' => 'wi wi-moon-alt-waxing-crescent-2', 'label' => 'Premier croissant'],
            ['icon' => 'wi wi-moon-alt-waxing-crescent-3', 'label' => 'Croissant croissant'],
            ['icon' => 'wi wi-moon-alt-waxing-crescent-4', 'label' => 'Croissant croissant'],
            ['icon' => 'wi wi-moon-alt-waxing-crescent-5', 'label' => 'Croissant croissant'],
            ['icon' => 'wi wi-moon-alt-waxing-crescent-6', 'label' => 'Croissant croissant'],
            ['icon' => 'wi wi-moon-alt-first-quarter', 'label' => 'Premier quartier'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-1', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-2', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-3', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-4', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-5', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-waxing-gibbous-6', 'label' => 'Gibbeuse croissante'],
            ['icon' => 'wi wi-moon-alt-full', 'label' => 'Pleine lune'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-1', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-2', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-3', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-4', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-5', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-waning-gibbous-6', 'label' => 'Gibbeuse décroissante'],
            ['icon' => 'wi wi-moon-alt-third-quarter', 'label' => 'Dernier quartier'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-1', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-2', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-3', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-4', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-5', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-waning-crescent-6', 'label' => 'Dernier croissant'],
            ['icon' => 'wi wi-moon-alt-new', 'label' => 'Nouvelle lune'],
        ];
        $index = (int)floor($phase * count($phases));

        return $phases[$index];
    }
}
