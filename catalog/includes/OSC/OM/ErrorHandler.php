<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Error_Handler
{
    public static function initialize(): void
    {
        ini_set('display_errors', false);
        ini_set('html_errors', false);
        ini_set('ignore_repeated_errors', true);
        if (File_System::is_writable(static::get_directory(), true)) {
            if (!is_dir(static::get_directory())) {
                mkdir(static::get_directory(), 0777, true);
            }
        }
        if (File_System::is_writable(static::get_directory())) {
            ini_set('log_errors', true);
            ini_set('error_log', static::get_directory() . 'errors-' . date('Ymd') . '.txt');
        }
    }
    public static function get_directory(): string
    {
        return OSCOM::BASE_DIR . 'Work/Logs/';
    }
}