export function getWindIcon(
    deg: number,
    type: 'towards' | 'from' = 'towards',
): string {
    const directions: number[] = [
        0, 23, 45, 68, 90, 113, 135, 158, 180, 203, 225, 248, 270, 293, 313,
        336,
    ];

    const normalizedDeg = (deg + 360) % 360;
    const index = Math.round(normalizedDeg / 22.5) % 16;

    let angle = directions[index];

    if (type === 'from') {
        angle = (angle + 180) % 360;
    }

    return `wi wi-wind ${type}-${angle}-deg`;
}
