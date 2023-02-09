<?php

use Carbon\Carbon;

/**
 * Date Formatter
 * Format: 18 July 2019
 *
 * @author Sayuri.Tsuboi
 */
if (!function_exists('dateformat')) {
    function dateformat($date)
    {
        return empty($date) ? '-' : Carbon::parse($date)->format('d M Y');
    }
}
/**
 * Datetime Formatter
 * Format: 18 Jul,19 10:23
 *
 * @author Sayuri.Tsuboi
 */
if (!function_exists('datetimeformat')) {
    function datetimeformat($date)
    {
        return empty($date) ? '-' : Carbon::parse($date)->format('d M,y H:i');
    }
}

/**
 * Period Formatter
 * Format: 18 July 2019 - 20 July 2019
 *
 * @author Sayuri.Tsuboi
 */
if (!function_exists('period_format')) {
    function period_format($date1, $date2)
    {
        $period = '';

        $difference = difference_dates($date1, $date2);
        if ($difference == 0) {
            $period = dateformat($date1);
        } else if ($difference < 0) {
            $period = sprintf("%s - %s", dateformat($date2), dateformat($date1));
        } else {
            $period = sprintf("%s - %s", dateformat($date1), dateformat($date2));
        }
        return $period;
    }
}

/**
 * Calculate date differences
 * If date2 is before than date1, return minus value
 *
 * @author Sayuri.Tsuboi
 */
if (!function_exists('difference_dates')) {
    function difference_dates($date1, $date2 = null)
    {
        if (is_null($date2)) {
            $date2 = date("Y-m-d");
        }

        $startDate = new Carbon($date1);
        $endDate = new Carbon($date2);
        return $startDate->diffInDays($endDate, false);
    }
}

/**
 * Calculate datetime (minutes) differences
 * If datetime2 is before than datetime1, return minus value
 *
 * @author Sayuri.Tsuboi
 */
if (!function_exists('difference_datetimes')) {
    function difference_datetimes($datetime1, $datetime2)
    {
        $startDatetime = new Carbon($datetime1);
        $endDatetime = new Carbon($datetime2);
        return $startDatetime->diffInMinutes($endDatetime, false);
    }
}

/**
 * Determine if the current controller name/route/query matches one of multiple params
 *
 * @param array $names Controller name (Variable arguments)
 * @return bool
 */
if (!function_exists('getCurrentControllerName')) {
    function getCurrentControllerName(...$names)
    {
        $currentRouteName = Route::currentRouteName();
        $currentControllerName = explode('/', $currentRouteName)[0];
        if (strpos($currentRouteName, '.') !== false) {
            $currentControllerName = explode('.', $currentRouteName)[0];
        }
        $query = head(Request::query());
        $currentQuery = $currentControllerName . '.' . $query;
        // dump($currentRouteName, $currentControllerName, $currentQuery);
        return in_array($currentRouteName, $names, true) ||
            in_array($currentControllerName, $names, true) ||
            in_array($currentQuery, $names, false);
    }
}

/**
 * Return string of 'active' if current controller name/route/query matches one of multiple params
 *
 * @param array $names Controller name (Variable arguments)
 * @return bool
 */
if (!function_exists('setActiveNav')) {
    function setActiveNav(...$names)
    {
        return getCurrentControllerName(...$names) ? 'active' : '';
    }
}

/**
 * Return formatted id with prefix
 *
 * @param string $requisitionType ['procurements', 'travel', ... ]
 * @param number $id
 * @return string formatted id
 */
if (!function_exists('idFormatter')) {
    function idFormatter($requisitionType, $id)
    {
        $prefix = '';
        switch ($requisitionType) {
            case 'procurements':
            case 'procurement':
                $prefix = 'P';
                break;
            case 'purchases':
            case 'purchase':
                $prefix = 'PR';
                break;
            case 'orders':
            case 'order':
                $prefix = 'O';
                break;
            case 'vouchers':
            case 'voucher':
                $prefix = 'V';
                break;
            case 'travels':
            case 'travel':
                $prefix = 'TRV';
                break;

            case 'transport':
            case 'transport':
                $prefix = 'TRNS';
                break;

            case 'hires':
            case 'hire':
                $prefix = 'HIRE';
                break;
            case 'transports':
            case 'transport':
                $prefix = 'TRS';
                break;
            case 'subsistences':
            case 'subsistence':
                $prefix = 'SUB';
                break;
            case 'maintenance':
                $prefix = 'M';
                break;
            case 'loan':
                $prefix = 'L';
                break;
            case 'book':
                $prefix = 'B';
                break;
        }
        return $prefix . str_pad($id, 7, "0", STR_PAD_LEFT);
    }
}

/**
 * Return formatted id with prefix
 *
 * @param string $requisitionType ['procurements', 'travel', ... ]
 * @param number $id
 * @return string formatted id
 */
if (!function_exists('removeFormattedId')) {
    function removeFormattedId($formattedId)
    {
        $output = preg_replace("/^[A-Z]*/", '', $formattedId);
        return $output;
    }
}


/**
 * Add empty element for combobox
 *
 * @param array
 * @return array
 */
if (!function_exists('addEmpty')) {
    function addEmpty($array)
    {
        return array_merge(['' => ''], $array);
    }
}
