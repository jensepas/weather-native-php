export interface UvData {
    label: string;
    color: string;
    text: string;
    description: string;
}

export function getUvData(uv: number): UvData {
    const map: Array<[number, UvData]> = [
        [
            2,
            {
                label: 'Faible',
                color: 'bg-green-400',
                text: 'text-green-400',
                description: 'Aucun risque',
            },
        ],
        [
            5,
            {
                label: 'Modéré',
                color: 'bg-yellow-400',
                text: 'text-yellow-400',
                description: 'Protection légère recommandée',
            },
        ],
        [
            7,
            {
                label: 'Élevé',
                color: 'bg-orange-400',
                text: 'text-orange-400',
                description: 'Crème solaire conseillée',
            },
        ],
        [
            10,
            {
                label: 'Très élevé',
                color: 'bg-red-500',
                text: 'text-red-500',
                description: 'Protection forte nécessaire',
            },
        ],
        [
            Infinity,
            {
                label: 'Extrême',
                color: 'bg-purple-600',
                text: 'text-purple-600',
                description: 'Éviter exposition',
            },
        ],
    ];

    return map.find(([max]) => uv <= max)![1];
}
