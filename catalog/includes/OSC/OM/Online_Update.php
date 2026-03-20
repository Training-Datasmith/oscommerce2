<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Online_Update
{
    public static function log($message, string $version): void
    {
        if (File_System::is_writable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt', true)) {
            $message = '[' . date('d-M-Y H:i:s') . '] ' . trim((string) $message) . "\n";
            file_put_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt', $message, FILE_APPEND);
        }
    }
    public static function reset_log(string $version): void
    {
        if (static::log_exists($version) && File_System::is_writable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt')) {
            unlink(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }
    }
    public static function get_log(string $version): string
    {
        $result = '';
        if (static::log_exists($version)) {
            $result = file_get_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }
        return trim($result);
    }
    public static function log_exists(string $version): bool
    {
        return is_file(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
    }
    public static function get_log_path(string $version)
    {
        if (static::log_exists($version)) {
            return File_System::display_path(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }
        return '';
    }
}