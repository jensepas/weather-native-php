export function getWeatherDescription(code: number): {
    desc: string;
    icon: string;
} {
    switch (true) {
        case code === 0:
            return { desc: 'Ciel dégagé', icon: 'wi wi-day-sunny' };

        case code === 1:
            return {
                desc: 'Clair avec quelques nuages',
                icon: 'wi wi-day-cloudy',
            };

        case code === 2:
            return { desc: 'Partiellement nuageux', icon: 'wi wi-day-cloudy' };

        case code === 3:
            return { desc: 'Nuageux', icon: 'wi wi-cloudy' };

        case code === 47:
            return { desc: 'Brouillard', icon: 'wi wi-fog' };

        case code <= 48:
            return { desc: 'Brouillard givré', icon: 'wi wi-fog' };

        case code === 51:
            return { desc: 'Bruine légère', icon: 'wi wi-sprinkle' };

        case code === 61:
            return { desc: 'Pluie légère', icon: 'wi wi-rain' };

        case code === 63:
            return { desc: 'Pluie modérée', icon: 'wi wi-rain' };

        case code === 65:
            return { desc: 'Fortes pluies', icon: 'wi wi-rain' };

        case code === 80:
            return { desc: 'Averses de pluie', icon: 'wi wi-showers' };

        case code === 81:
            return { desc: 'Averses de pluie modérées', icon: 'wi wi-showers' };

        case code === 95:
            return { desc: 'Tempête', icon: 'wi wi-thunderstorm' };

        case code === 96:
            return {
                desc: 'Orage avec grêle légère',
                icon: 'wi wi-thunderstorm',
            };

        case code === 99:
            return { desc: 'Orage avec grêle', icon: 'wi wi-thunderstorm' };

        default:
            return { desc: 'Ciel inconnu', icon: 'wi wi-day-sunny' };
    }
}
