<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Apps
{
    /**
     * @return mixed[]
     */
    public static function get_all(): array
    {
        $result = [];
        $apps_directory = OSCOM::BASE_DIR . 'Apps';
        if ($vdir = new \Directory_Iterator($apps_directory)) {
            foreach ($vdir as $vendor) {
                if ($vendor->is_dot()) {
                    continue;
                }
                if (!$vendor->is_dir()) {
                    continue;
                }
                if (!$adir = new \Directory_Iterator($vendor->get_path() . '/' . $vendor->get_filename())) {
                    continue;
                }
                foreach ($adir as $app) {
                    if (!(!$app->is_dot() && $app->is_dir())) {
                        continue;
                    }
                    if (!static::exists($vendor->get_filename() . '\\' . $app->get_filename())) {
                        continue;
                    }
                    if ($json = static::get_info($vendor->get_filename() . '\\' . $app->get_filename()) === false) {
                        continue;
                    }
                    $result[] = $json;
                }
            }
        }
        return $result;
    }
    /**
     * @return mixed[]
     */
    public static function get_modules(string $type, $filter_vendor_app = null, $filter = null): array
    {
        $result = [];
        if (!Registry::exists('ModuleType' . $type)) {
            $class = 'OSC\OM\Modules\\' . $type;
            if (!class_exists($class)) {
                trigger_error('OSC\OM\Apps::getModules(): ' . $type . ' module class not found in OSC\OM\Modules\\');
                return $result;
            }
            Registry::set('ModuleType' . $type, new $class());
        }
        $OSCOM_Type = Registry::get('ModuleType' . $type);
        $filter_vendor = $filter_app = null;
        if (isset($filter_vendor_app)) {
            if (str_contains($filter_vendor_app, '\\')) {
                [$filter_vendor, $filter_app] = explode('\\', $filter_vendor_app, 2);
            } else {
                $filter_vendor = $filter_vendor_app;
            }
        }
        $vendor_directory = OSCOM::BASE_DIR . 'Apps';
        if (is_dir($vendor_directory)) {
            if ($vdir = new \Directory_Iterator($vendor_directory)) {
                foreach ($vdir as $vendor) {
                    if (!(!$vendor->is_dot() && $vendor->is_dir())) {
                        continue;
                    }
                    if (!(!isset($filter_vendor) || $vendor->get_filename() == $filter_vendor)) {
                        continue;
                    }
                    if (!$adir = new \Directory_Iterator($vendor->get_path() . '/' . $vendor->get_filename())) {
                        continue;
                    }
                    foreach ($adir as $app) {
                        if (!(!$app->is_dot() && $app->is_dir() && (!isset($filter_app) || $app->get_filename() == $filter_app) && static::exists($vendor->get_filename() . '\\' . $app->get_filename()))) {
                            continue;
                        }
                        if (!$json = static::get_info($vendor->get_filename() . '\\' . $app->get_filename()) !== false) {
                            continue;
                        }
                        if (!isset($json['modules'][$type])) {
                            continue;
                        }
                        $modules = $json['modules'][$type];
                        if (isset($filter)) {
                            $modules = $OSCOM_Type->filter($modules, $filter);
                        }
                        foreach ($modules as $key => $data) {
                            $result = array_merge($result, $OSCOM_Type->get_info($vendor->get_filename() . '\\' . $app->get_filename(), $key, $data));
                        }
                    }
                }
            }
        }
        return $result;
    }
    public static function exists(string $app): bool
    {
        if (str_contains($app, '\\')) {
            [$vendor, $app] = explode('\\', $app, 2);
            if (class_exists('OSC\Apps\\' . $vendor . '\\' . $app . '\\' . $app)) {
                if (is_subclass_of('OSC\Apps\\' . $vendor . '\\' . $app . '\\' . $app, \OSC\OM\App_Abstract::class)) {
                    return true;
                }
                trigger_error('OSC\OM\Apps::exists(): ' . $vendor . '\\' . $app . ' - App is not a subclass of OSC\OM\AppAbstract and cannot be loaded.');
            }
        } else {
            trigger_error('OSC\OM\Apps::exists(): ' . $app . ' - Invalid format, must be: Vendor\App.');
        }
        return false;
    }
    public static function get_module_class($module, string $type)
    {
        if (!Registry::exists('ModuleType' . $type)) {
            $class = 'OSC\OM\Modules\\' . $type;
            if (!class_exists($class)) {
                trigger_error('OSC\OM\Apps::getModuleClass(): ' . $type . ' module class not found in OSC\OM\Modules\\');
                return false;
            }
            Registry::set('ModuleType' . $type, new $class());
        }
        $OSCOM_Type = Registry::get('ModuleType' . $type);
        return $OSCOM_Type->get_class($module);
    }
    public static function get_info(string $app)
    {
        if (str_contains($app, '\\')) {
            [$vendor, $app] = explode('\\', $app, 2);
            $metafile = OSCOM::BASE_DIR . 'Apps/' . basename($vendor) . '/' . basename($app) . '/oscommerce.json';
            if (is_file($metafile) && ($json = json_decode(file_get_contents($metafile), true)) !== null) {
                return $json;
            }
            trigger_error('OSC\OM\Apps::getInfo(): ' . $vendor . '\\' . $app . ' - Could not read App information in ' . $metafile . '.');
        } else {
            trigger_error('OSC\OM\Apps::getInfo(): ' . $app . ' - Invalid format, must be: Vendor\App.');
        }
        return false;
    }
    public static function get_route_destination($route = null, $filter_vendor_app = null)
    {
        if (empty($route)) {
            $route = array_keys($_GET);
        }
        $result = $routes = [];
        if (empty($route)) {
            return $result;
        }
        $filter_vendor = $filter_app = null;
        if (isset($filter_vendor_app)) {
            if (str_contains($filter_vendor_app, '\\')) {
                [$filter_vendor, $filter_app] = explode('\\', $filter_vendor_app, 2);
            } else {
                $filter_vendor = $filter_vendor_app;
            }
        }
        $vendor_directory = OSCOM::BASE_DIR . 'Apps';
        if (is_dir($vendor_directory)) {
            if ($vdir = new \Directory_Iterator($vendor_directory)) {
                foreach ($vdir as $vendor) {
                    if (!(!$vendor->is_dot() && $vendor->is_dir())) {
                        continue;
                    }
                    if (!(!isset($filter_vendor) || $vendor->get_filename() == $filter_vendor)) {
                        continue;
                    }
                    if (!$adir = new \Directory_Iterator($vendor->get_path() . '/' . $vendor->get_filename())) {
                        continue;
                    }
                    foreach ($adir as $app) {
                        if (!(!$app->is_dot() && $app->is_dir() && (!isset($filter_app) || $app->get_filename() == $filter_app) && static::exists($vendor->get_filename() . '\\' . $app->get_filename()))) {
                            continue;
                        }
                        if (!$json = static::get_info($vendor->get_filename() . '\\' . $app->get_filename()) !== false) {
                            continue;
                        }
                        if (!isset($json['routes'][OSCOM::get_site()])) {
                            continue;
                        }
                        $routes[$json['vendor'] . '\\' . $json['app']] = $json['routes'][OSCOM::get_site()];
                    }
                }
            }
        }
        return call_user_func(['OSC\Sites\\' . OSCOM::get_site() . '\\' . OSCOM::get_site(), 'resolveRoute'], $route, $routes);
    }
}