<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Cache
{
    protected static $path;
    protected $key;
    protected $data;
    public function __construct($key)
    {
        static::set_path();
        $this->set_key($key);
    }
    public function set_key(string $key)
    {
        if (!static::has_safe_name($key)) {
            trigger_error('OSC\OM\Cache: Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');
            return false;
        }
        $this->key = $key;
    }
    public function get_key()
    {
        return $this->key;
    }
    public function save($data)
    {
        if (File_System::is_writable(static::$path)) {
            return file_put_contents(static::$path . $this->key . '.cache', serialize($data), LOCK_EX) !== false;
        }
        return false;
    }
    public function exists($expire = null): bool
    {
        $filename = static::$path . $this->key . '.cache';
        if (is_file($filename)) {
            if (!isset($expire)) {
                return true;
            }
            $difference = floor((time() - filemtime($filename)) / 60);
            if (is_numeric($expire) && $difference < $expire) {
                return true;
            }
        }
        return false;
    }
    public function get()
    {
        $filename = static::$path . $this->key . '.cache';
        if (is_file($filename)) {
            $this->data = unserialize(file_get_contents($filename), ['allowed_classes' => false]);
        }
        return $this->data;
    }
    public static function has_safe_name($key): bool
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', (string) $key) === 1;
    }
    public function get_time(): int|false
    {
        $filename = static::$path . $this->key . '.cache';
        if (is_file($filename)) {
            return filemtime($filename);
        }
        return false;
    }
    public static function find(string $key, $strict = true): bool
    {
        if (!static::has_safe_name($key)) {
            trigger_error('OSC\OM\Cache::find(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');
            return false;
        }
        if (is_file(static::$path . $key . '.cache')) {
            return true;
        }
        if ($strict === false) {
            $key_length = strlen($key);
            $d = dir(static::$path);
            while (($entry = $d->read()) !== false) {
                if (strlen($entry) >= $key_length && substr($entry, 0, $key_length) == $key) {
                    $d->close();
                    return true;
                }
            }
        }
        return false;
    }
    public static function set_path(): void
    {
        static::$path = OSCOM::BASE_DIR . 'Work/Cache/';
    }
    public static function get_path()
    {
        if (!isset(static::$path)) {
            static::set_path();
        }
        return static::$path;
    }
    public static function clear(string $key)
    {
        if (!static::has_safe_name($key)) {
            trigger_error('OSC\OM\Cache::clear(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');
            return false;
        }
        if (File_System::is_writable(static::$path)) {
            foreach (glob(static::$path . $key . '*.cache') as $c) {
                unlink($c);
            }
        }
    }
    public static function clear_all(): void
    {
        if (File_System::is_writable(static::$path)) {
            foreach (glob(static::$path . '*.cache') as $c) {
                unlink($c);
            }
        }
    }
}