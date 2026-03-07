<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

class Registry
{
    private static array $data = [];

    public static function get(string $key)
    {
        if (!static::exists($key)) {
            trigger_error('OSC\OM\Registry::get - ' . $key . ' is not registered');

            return false;
        }

        return static::$data[$key];
    }

    public static function set(string $key, $value, $force = false)
    {
        if (!is_object($value)) {
            trigger_error('OSC\OM\Registry::set - ' . $key . ' is not an object and cannot be set in the registry');

            return false;
        }

        if (static::exists($key) && ($force !== true)) {
            trigger_error('OSC\OM\Registry::set - ' . $key . ' already registered and is not forced to be replaced');

            return false;
        }

        static::$data[$key] = $value;
    }

    public static function exists($key): bool
    {
        return array_key_exists($key, static::$data);
    }
}
