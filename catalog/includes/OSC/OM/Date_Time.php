<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class DateTime
{
    protected $datetime = false;
    protected $raw_pattern_date = 'Y-m-d';
    protected $raw_pattern_time = 'H:i:s';
    public function __construct(string $datetime, $use_raw_pattern = false, $strict = false)
    {
        if ($use_raw_pattern === false) {
            $pattern = OSCOM::get_def('date_time_format');
        } else {
            $pattern = $this->raw_pattern_date . ' ' . $this->raw_pattern_time;
        }
        // format time as 00:00:00 if it is missing from the date
        $new_datetime = strtotime($datetime);
        if ($new_datetime !== false) {
            $new_datetime = date($pattern, $new_datetime);
            $this->datetime = \DateTime::create_from_format($pattern, $new_datetime);
            $strict_log = false;
        }
        if ($this->datetime === false) {
            $strict_log = true;
        } else {
            $errors = \DateTime::get_last_errors();
            if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
                $this->datetime = false;
                $strict_log = true;
            }
        }
        if ($strict === true && $strict_log === true) {
            trigger_error('DateTime: ' . $datetime . ' (' . $new_datetime . ') cannot be formatted to ' . $pattern);
        }
    }
    public function is_valid(): bool
    {
        return $this->datetime instanceof \DateTime;
    }
    public function get($pattern = null)
    {
        if (isset($pattern)) {
            return $this->datetime->format($pattern);
        }
        return $this->datetime;
    }
    public function get_short($with_time = false): string|false
    {
        $pattern = $with_time === false ? OSCOM::get_def('date_format_short') : OSCOM::get_def('date_time_format');
        return strftime($pattern, $this->get_timestamp());
    }
    public function get_long(): string|false
    {
        return strftime(OSCOM::get_def('date_format_long'), $this->get_timestamp());
    }
    public static function to_short($raw_datetime, $with_time = false, $strict = true): string|false
    {
        $result = '';
        $date = new DateTime($raw_datetime, true, $strict);
        if ($date->is_valid()) {
            $pattern = $with_time === false ? OSCOM::get_def('date_format_short') : OSCOM::get_def('date_time_format');
            $result = strftime($pattern, $date->get_timestamp());
        }
        return $result;
    }
    public static function to_long($raw_datetime, $strict = true): string|false
    {
        $result = '';
        $date = new DateTime($raw_datetime, true, $strict);
        if ($date->is_valid()) {
            return strftime(OSCOM::get_def('date_format_long'), $date->get_timestamp());
        }
        return $result;
    }
    public function get_raw($with_time = true)
    {
        $pattern = $this->raw_pattern_date;
        if ($with_time === true) {
            $pattern .= ' ' . $this->raw_pattern_time;
        }
        return $this->datetime->format($pattern);
    }
    public function get_timestamp()
    {
        return $this->datetime->get_timestamp();
    }
    /**
     * @return array{id: (int | string), text: string, group: string}[]
     */
    public static function get_time_zones(): array
    {
        $time_zones_array = [];
        foreach (\DateTimeZone::list_identifiers() as $id) {
            $tz_string = str_replace('_', ' ', $id);
            $id_array = explode('/', $tz_string, 2);
            $time_zones_array[$id_array[0]][$id] = $id_array[1] ?? $id_array[0];
        }
        $result = [];
        foreach ($time_zones_array as $zone => $zones_array) {
            foreach ($zones_array as $key => $value) {
                $result[] = ['id' => $key, 'text' => $value, 'group' => $zone];
            }
        }
        return $result;
    }
    public static function set_time_zone($time_zone = null): bool
    {
        if (!isset($time_zone)) {
            $time_zone = OSCOM::config_exists('time_zone') ? OSCOM::get_config('time_zone') : date_default_timezone_get();
        }
        return date_default_timezone_set($time_zone);
    }
}