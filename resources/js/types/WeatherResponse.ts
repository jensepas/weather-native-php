// City
export interface City {
    name: string;
    latitude: number;
    longitude: number;
    country: string;
    id: string;
    admin1?: string;
    timezone?: string;
    elevation: number;
    population: number;
}

// Location
export interface Location {
    latitude: number;
    longitude: number;
}

// Time
export interface TimeData {
    localTime: string;
    localDate: string;
    timezone: string;
    cachedAt?: string;
}

// Weather description
export interface WeatherDescription {
    icon: string;
    desc: string;
}

// Theme
export interface WeatherTheme {
    bg: string;
    text: string;
}

// Current weather
export interface CurrentWeather {
    temperature_2m: number;
    apparent_temperature: number;
    precipitation: number;
    relative_humidity_2m: number;
    surface_pressure: number;
    weather_code: number;
    wind_speed_10m: number;
    wind_direction_10m: number;
    [key: string]: any; // fallback pour API évolutive
}

// Hourly weather
export interface HourlyWeather {
    time: string[];
    temperature_2m: number[];
    precipitation: number[];
    weather_code: number[];
    wind_speed_10m: number[];
    wind_direction_10m: number[];
    uv_index?: number[];
    [key: string]: any;
}

// Forecast (daily formaté)
export interface ForecastDay {
    date: string;
    min: number;
    max: number;
    uv: number;
    rain_prob?: number;
    weather_code: number;
    [key: string]: any;
}

// Weather block
export interface WeatherBlock {
    current: CurrentWeather;
    hourly: HourlyWeather;
    forecast: ForecastDay[];
    todayDetails: ForecastDay;
    description: WeatherDescription;
    theme: WeatherTheme;
}

// Sun data
export interface SunData {
    sunrise: string;
    sunset: string;
    solar_noon?: string;
    day_length?: number;
    localDate: string | { date: string };
    [key: string]: any;
}

// Sun formatted (ton formatter custom)
export interface SunFormatted {
    altitude: number;
    azimuth: number;
    direction: string;
    elevationAngle?: number;
    [key: string]: any;
}

// Moon data
export interface MoonData {
    moonrise?: string;
    moonset?: string;
    phase?: number;
    illumination?: number;
    [key: string]: any;
}

// Astronomy block
export interface AstronomyBlock {
    sun: SunData;
    sunFormatted: SunFormatted;
    moon: MoonData;
    isDay: boolean;
    moonDetails: MoonDetails;
    localDate: string | { date: string };
}

// Root response
export interface WeatherResponse {
    cities: City[];
    selectedCityInfos: City;
    location: Location;
    time: TimeData;
    weather: WeatherBlock;
    astronomy: AstronomyBlock;
    localDate: string | { date: string };
}

export interface MoonDetails {
    phase: number;
    illumination: number;
    icon: string;
    label: string;
}
