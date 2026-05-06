<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class CityService
{
    private array $defaultCities = [];

    public function addCity($city): string
    {
        $cities = $this->getCities();
        $infosCity = json_decode($city['cityData']);

        if (!collect($cities)->contains('id', $infosCity->id)) {
            $cities[] = $infosCity;
            $this->saveCities($cities);
        }
        return $infosCity->id;
    }

    public function getCities(): array
    {
        if (Storage::exists('cities.json')) {
            $content = Storage::get('cities.json');
            $cities = json_decode($content, true);
            return is_array($cities) && !empty($cities) ? $cities : $this->defaultCities;
        }
        return $this->defaultCities;
    }

    public function saveCities(array $cities): void
    {
        Storage::put('cities.json', json_encode(array_values($cities), JSON_PRETTY_PRINT));
    }

    public function removeCity(string $id): void
    {
        $cities = $this->getCities();
        if (count($cities) >= 1) {
            $cities = collect($cities)->reject(fn($c) => (string)$c['id'] === $id)->values()->all();
            $this->saveCities($cities);
        }
    }

    public function updateCityOrder(array $cityIds): void
    {
        $currentCities = $this->getCities();
        $orderedCities = [];

        foreach ($cityIds as $id) {
            $city = collect($currentCities)->firstWhere('id', $id);
            if ($city) {
                $orderedCities[] = $city;
            }
        }

        $this->saveCities($orderedCities);
    }

    public function getSelectedCity(array $cities, ?string $id): array
    {
        return collect($cities)->firstWhere('id', $id) ?? $cities[0];
    }

    public function exportCities(): ?string
    {
        if (Storage::exists('cities.json')) {
            return Storage::get('cities.json');
        }
        return null;
    }

    public function importCities(string $content): bool
    {
        $cities = json_decode($content, true);

        if (is_array($cities)) {
            $this->saveCities($cities);
            return true;
        }

        return false;
    }
}
