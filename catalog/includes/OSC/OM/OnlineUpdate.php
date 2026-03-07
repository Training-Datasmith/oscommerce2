<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

class OnlineUpdate
{
    public static function log($message, string $version): void
    {
        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt', true)) {
            $message = '[' . date('d-M-Y H:i:s') . '] ' . trim((string) $message) . "\n";

            file_put_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt', $message, FILE_APPEND);
        }
    }

    public static function resetLog(string $version): void
    {
        if (static::logExists($version) && FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt')) {
            unlink(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }
    }

    public static function getLog(string $version): string
    {
        $result = '';

        if (static::logExists($version)) {
            $result = file_get_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }

        return trim($result);
    }

    public static function logExists(string $version): bool
    {
        return is_file(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
    }

    public static function getLogPath(string $version)
    {
        if (static::logExists($version)) {
            return FileSystem::displayPath(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }

        return '';
    }
}
