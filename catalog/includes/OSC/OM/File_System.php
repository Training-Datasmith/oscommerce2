<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class File_System
{
    /**
     * @return mixed[]
     */
    public static function get_directory_contents($base): array
    {
        $base = str_replace('\\', '/', $base);
        // Unix style directory separator "/"
        $dir = new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($base, \Filesystem_Iterator::KEY_AS_PATHNAME | \Filesystem_Iterator::CURRENT_AS_SELF | \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::UNIX_PATHS));
        $result = [];
        foreach ($dir as $file) {
            $result[] = $file->get_path_name();
        }
        return $result;
    }
    public static function is_writable($location, $recursive_check = false): bool
    {
        if ($recursive_check === true) {
            if (!file_exists($location)) {
                while (true) {
                    $location = dirname((string) $location);
                    if (file_exists($location)) {
                        break;
                    }
                }
            }
        }
        return is_writable($location);
    }
    /**
     * @return mixed[]
     */
    public static function rmdir(string $dir, $dry_run = false): array
    {
        $result = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (!in_array($file, ['.', '..'])) {
                    if (is_dir($dir . '/' . $file)) {
                        $result = array_merge($result, static::rmdir($dir . '/' . $file, $dry_run));
                    } else {
                        $result[] = ['type' => 'file', 'source' => $dir . '/' . $file, 'result' => $dry_run === false ? unlink($dir . '/' . $file) : static::is_writable($dir . '/' . $file)];
                    }
                }
            }
            $result[] = ['type' => 'directory', 'source' => $dir, 'result' => $dry_run === false ? rmdir($dir) : static::is_writable($dir)];
        }
        return $result;
    }
    public static function display_path($pathname): string|array
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pathname);
    }
}