<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Hash
{
    public static function encrypt(string $plain, $algo = null)
    {
        if (!isset($algo) || $algo == 'default' || $algo == 'bcrypt') {
            if (!isset($algo) || $algo == 'default') {
                $algo = PASSWORD_DEFAULT;
            } else {
                $algo = PASSWORD_BCRYPT;
            }
            return password_hash($plain, $algo);
        }
        if ($algo == 'phpass') {
            if (!class_exists('PasswordHash', false)) {
                include OSCOM::get_config('dir_root', 'Shop') . 'includes/third_party/PasswordHash.php';
            }
            $hasher = new \Password_Hash(10, true);
            return $hasher->hash_password($plain);
        }
        if ($algo == 'salt') {
            $password = '';
            for ($i = 0; $i < 10; $i++) {
                $password .= static::get_random_int();
            }
            $salt = substr(md5($password), 0, 2);
            return md5($salt . $plain) . ':' . $salt;
        }
        trigger_error('OSC\OM\Hash::encrypt() Algorithm "' . $algo . '" unknown.');
        return false;
    }
    public static function verify(string $plain, $hash)
    {
        $result = false;
        if (strlen($plain) > 0 && strlen((string) $hash) > 0) {
            switch (static::get_type($hash)) {
                case 'phpass':
                    if (!class_exists('PasswordHash', false)) {
                        include OSCOM::get_config('dir_root', 'Shop') . 'includes/third_party/PasswordHash.php';
                    }
                    $hasher = new \Password_Hash(10, true);
                    $result = $hasher->check_password($plain, $hash);
                    break;
                case 'salt':
                    // split apart the hash / salt
                    $stack = explode(':', (string) $hash, 2);
                    if (count($stack) === 2) {
                        $result = hash_equals($stack[0], md5($stack[1] . $plain));
                    } else {
                        $result = false;
                    }
                    break;
                default:
                    $result = password_verify($plain, (string) $hash);
                    break;
            }
        }
        return $result;
    }
    public static function needs_rehash($hash, $algo = null): bool
    {
        if (!isset($algo) || $algo == 'default') {
            $algo = PASSWORD_DEFAULT;
        } elseif ($algo == 'bcrypt') {
            $algo = PASSWORD_BCRYPT;
        }
        if (!is_int($algo)) {
            trigger_error('OSC\OM\Hash::needsRehash() Algorithm "' . $algo . '" not supported.');
        }
        return password_needs_rehash($hash, $algo);
    }
    public static function get_type($hash)
    {
        $info = password_get_info($hash);
        if ($info['algo'] > 0) {
            return $info['algoName'];
        }
        if (str_starts_with((string) $hash, '$P$')) {
            return 'phpass';
        }
        if (preg_match('/^[A-Z0-9]{32}\:[A-Z0-9]{2}$/i', (string) $hash) === 1) {
            return 'salt';
        }
        trigger_error('OSC\OM\Hash::getType() hash type not found for "' . substr((string) $hash, 0, 5) . '"');
        return '';
    }
    public static function get_random_int($min = null, $max = null, $secure = true): int
    {
        if (!isset($min)) {
            $min = 0;
        }
        if (!isset($max)) {
            $max = PHP_INT_MAX;
        }
        try {
            $result = random_int($min, $max);
        } catch (\Exception $e) {
            if ($secure === true) {
                throw $e;
            }
            $result = mt_rand($min, $max);
        }
        return $result;
    }
    public static function get_random_string($length, string $type = 'mixed'): false|string
    {
        if (!in_array($type, ['mixed', 'chars', 'digits'])) {
            trigger_error('Hash::getRandomString() $type not recognized: ' . $type, E_USER_ERROR);
            return false;
        }
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $base = '';
        if ($type == 'mixed' || $type == 'chars') {
            $base .= $chars;
        }
        if ($type == 'mixed' || $type == 'digits') {
            $base .= $digits;
        }
        $rand_value = '';
        do {
            $random = base64_encode((string) static::get_random_bytes($length));
            for ($i = 0, $n = strlen($random); $i < $n; $i++) {
                $char = substr($random, $i, 1);
                if (str_contains($base, $char)) {
                    $rand_value .= $char;
                }
            }
        } while (strlen($rand_value) < $length);
        if (strlen($rand_value) > $length) {
            return substr($rand_value, 0, $length);
        }
        return $rand_value;
    }
    public static function get_random_bytes($length, $secure = true): string
    {
        try {
            $result = random_bytes($length);
        } catch (\Exception $e) {
            if ($secure === true) {
                throw $e;
            }
            $result = '';
            $random_state = '';
            for ($i = 0; $i < $length; $i += 16) {
                $random_state = md5(microtime() . $random_state);
                $result .= pack('H*', md5($random_state));
            }
            $result = substr($result, 0, $length);
        }
        return $result;
    }
}