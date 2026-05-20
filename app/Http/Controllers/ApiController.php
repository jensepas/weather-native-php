<?php

namespace App\Http\Controllers;

use App\Services\CityService;
use App\Services\MoonCalc;
use App\Services\SunCalc;
use App\Services\SunFormatter;
use App\Services\WeatherService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class ApiController extends Controller
{
    public function __construct(
        private readonly CityService    $cityService,
        private readonly WeatherService $weatherService,
        private readonly MoonCalc       $moonCalc,
        private readonly SunCalc        $sunCalc,
        private readonly SunFormatter   $sunFormatter
    )
    {
    }

    /**
     * @throws Exception
     */
    public function weather(Request $request): JsonResponse
    {
        return response()->json(
            $this->buildWeatherData($request)
        );
    }

    /**
     * @throws Exception
     */
    private function buildWeatherData(Request $request)
    {
        $userCities = $this->cityService->getCities();

        if (empty($userCities)) {
            return [
                'cities' => [],
                'selectedCityName' => '',
                'selectedCityId' => '',
                'selectedCityInfos' => [],
            ];
        }

        $selectedCityName = $request->input('city', $userCities[0]['id']);
        $refresh = $request->input('refresh', 'false');

        $selectedCity = $this->cityService->getSelectedCity($userCities, $selectedCityName);

        $latitude = (float)$selectedCity['latitude'];
        $longitude = (float)$selectedCity['longitude'];
        $timezone = $selectedCity['timezone'] ?? 'UTC';

        // Weather
        $weatherData = $this->weatherService->getWeather($latitude, $longitude, $refresh);

        $current = $weatherData['current'] ?? [];
        $daily = $weatherData['daily'] ?? [];
        $hourly = $weatherData['hourly'] ?? [];
        $cachedAt = $weatherData['cached_at'] ?? null;

        $localTime = Carbon::now($timezone);
        $day = $localTime->copy()->startOfDay();


        // Sun / Moon
        $sunData = $this->sunCalc->getTimes($day, $latitude, $longitude, $timezone);
        $position = $this->sunCalc->getPosition($localTime, $latitude, $longitude);

        $sunFormatter = $this->sunFormatter->format($position, $latitude, $longitude, $sunData, $localTime);

        $moonData = $this->moonCalc->getTimes($day, $latitude, $longitude, $timezone);

        if ($sunData['sunrise'] !== null && $sunData['sunset'] !== null) {
            $isDay = $localTime->between($sunData['sunrise'], $sunData['sunset']);
        } else {
            $isDay = ($position['altitude'] ?? 0) > 0;
        }

        $forecast = $this->weatherService->formatForecast($daily);
        $todayDetails = $forecast[0] ?? [];

        return [
            'cities' => $userCities,
            'selectedCityName' => (string)$selectedCityName,
            'selectedCityId' => (string)$selectedCity['id'],
            'selectedCityInfos' => $selectedCity,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],

            'time' => [
                'localTime' => $localTime->format('H:i'),
                'localDate' => $localTime->locale('fr')->isoFormat('dddd D MMMM YYYY'),
                'localDateC' => $localTime->toDateTimeLocalString(),
                'timezone' => $timezone,
                'cachedAt' => $cachedAt ? Carbon::parse($cachedAt)->timezone($timezone)->format('H:i') : null,
            ],

            'weather' => [
                'current' => $current,
                'hourly' => $hourly,
                'forecast' => $forecast,
                'todayDetails' => $todayDetails,
                'description' => $this->weatherService->getWeatherDescription($current['weather_code'] ?? 0),
                'theme' => $this->weatherService->getWeatherTheme($current['weather_code'] ?? 0, $isDay),
            ],
            'astronomy' => [
                'sun' => $sunData,
                'sunFormatted' => $sunFormatter,
                'moon' => $moonData,
                'moonDetails' => $this->getMoonData($moonData),
                'isDay' => $isDay,
                'localDate' =>  $localTime->toDateTimeString(),
            ]
        ];
    }

    private function getMoonData(?array $data): array
    {
        if (!$data) {
            return [
                'phase' => 0,
                'illumination' => 0,
                'icon' => 'wi wi-moon-alt-new',
                'label' => 'N/A',
                'moonrise' => '--:--',
                'moonset' => '--:--'
            ];
        }

        // Mapping des phases de l'API vers tes icônes
        $phaseLabel = $data['phase'];
        $iconData = $this->getMoonIconFromLabel($phaseLabel);

        return [
            'phase' => $data['phase'],
            'illumination' => $data['fraction'] / 100,
            'icon' => $iconData['icon'],
            'label' => $phaseLabel,
            'moonrise' => Carbon::parse($data['moonrise'] ?? '')->format('H:i'),
            'moonset' => Carbon::parse($data['moonset'] ?? $data['moonset_next'] ?? '')->format('H:i'),
        ];
    }

    private function getMoonIconFromLabel($phase): array
    {
        $phases = [
            ['icon' => 'wi wi-moon-alt-new', 'label' => 'Nouvelle lune'],
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
            ['icon' => 'wi wi-moon-alt-new', 'label' => 'Nouvelle lune']
        ];
        $index = (int)floor($phase * count($phases));

        return $phases[$index];
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        return Inertia::render('WeatherApp', $this->buildWeatherData($request));
    }

    public function cities(): Response
    {
        return Inertia::render('CityManagement', [
            'cities' => $this->cityService->getCities(),
        ]);
    }

    public function citiesList(): JsonResponse
    {
        return response()->json([
            'cities' => $this->cityService->getCities(),
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');
        if (strlen($query) < 2) {
            return response()->json();
        }

        $response = Http::get("https://geocoding-api.open-meteo.com/v1/search", [
            'name' => $query, 'count' => 5, 'language' => 'fr', 'format' => 'json'
        ]);

        return response()->json($response->json()['results'] ?? []);
    }

    public function addCity(Request $request): JsonResponse
    {
        $cityId = $this->cityService->addCity($request->cityData);
        return response()->json(['success' => true, 'id' => $cityId]);
    }

    public function removeCity(Request $request): JsonResponse
    {
        $this->cityService->removeCity($request->id);
        return response()->json(['success' => true]);
    }

    public function reorderCities(Request $request): JsonResponse
    {
        $cityIds = $request->input('cityIds');
        if (!is_array($cityIds)) {
            return response()->json(['success' => false, 'message' => 'Invalid cityIds provided'], 400);
        }

        $this->cityService->updateCityOrder($cityIds);

        return response()->json(['success' => true]);
    }

    public function exportCities()
    {
        $content = $this->cityService->exportCities();

        return response($content)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="cities.json"');
    }

    public function importCities(Request $request)
    {
        $content = $request->input('content');
        $success = $this->cityService->importCities($content);
        return response()->json(['success' => $success]);
    }
}
