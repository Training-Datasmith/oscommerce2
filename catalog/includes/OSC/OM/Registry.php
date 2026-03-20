<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

/**
 * Singleton object registry for shared application services.
 *
 * Stores shared objects (DB connection, session, language service, etc.) keyed
 * by string. Only object instances may be stored. Use Registry::get() throughout
 * the application instead of global variables.
 *
 * @since  2016
 */
class Registry
{
    /** @var array<string, object>  Map of registered service key → object instance. */
    private static array $data = [];

    /**
     * Retrieves a registered object by key.
     *
     * Triggers a PHP E_USER_NOTICE and returns false if the key has not been
     * registered.
     *
     * @param  string  $key  The registration key (e.g. 'Db', 'Session', 'Language').
     * @return object|false  The registered object, or false if not found.
     * @since  2016
     */
    public static function get(string $key): object|false
    {
        if (!static::exists($key)) {
            trigger_error('OSC\OM\Registry::get - ' . $key . ' is not registered');

            return false;
        }

        return static::$data[$key];
    }

    /**
     * Registers an object under the given key.
     *
     * Only objects may be registered. If the key is already in use and $force is
     * false, the existing registration is preserved and a notice is triggered.
     * Pass $force = true to replace an existing registration.
     *
     * @param  string  $key    The registration key (e.g. 'Db', 'Session').
     * @param  object  $value  The object to register.
     * @param  bool    $force  When true, overwrite an existing registration silently.
     * @return false|null      Returns false on validation failure, null on success.
     * @since  2016
     */
    public static function set(string $key, mixed $value, bool $force = false): false|null
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
        return null;
    }

    /**
     * Checks whether a key has been registered.
     *
     * @param  string  $key  The registration key to check.
     * @return bool          True if the key exists in the registry, false otherwise.
     * @since  2016
     */
    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::$data);
    }
}
