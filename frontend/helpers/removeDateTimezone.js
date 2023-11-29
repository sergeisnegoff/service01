export function removeDateTimezone(date) {
    if (!date) return;

    return date.toString().split('+')[0];
}
