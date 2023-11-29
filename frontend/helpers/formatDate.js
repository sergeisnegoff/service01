import dayjs from 'dayjs';
import dayjsRU from 'dayjs/locale/ru';
import { removeDateTimezone } from '@/helpers/removeDateTimezone';

// https://day.js.org/docs/en/plugin/relative-time
// import relativeTime from 'dayjs/plugin/relativeTime';
// dayjs.extend(relativeTime);

// https://day.js.org/docs/en/plugin/to-object
// import toObject from 'dayjs/plugin/toObject';
// dayjs.extend(toObject);

// https://day.js.org/docs/en/plugin/utc
// import utc from 'dayjs/plugin/utc';
// dayjs.extend(utc);

import custom from 'dayjs/plugin/customParseFormat';
dayjs.extend(custom);

// import isTomorrow from 'dayjs/plugin/isTomorrow';
// dayjs.extend(isTomorrow);

// import isToday from 'dayjs/plugin/isToday';
// dayjs.extend(isToday);


// https://day.js.org/docs/en/display/format
// https://day.js.org/docs/en/display/difference

export default function formatDate(date, format = undefined) {
    return dayjs(removeDateTimezone(date)).locale(dayjsRU)
        .format(format);
}
