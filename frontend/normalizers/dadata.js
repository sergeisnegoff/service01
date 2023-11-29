export function addressFormatter(item) {
    const latitude = item?.data.geo_lat;
    const longitude = item?.data.geo_lon;

    return {
        title: item.value,
        id: item.value,
        latitude,
        longitude
    };
}
