<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class OSCOM
{
    public const BASE_DIR = OSCOM_BASE_DIR;
    protected static $version;
    protected static $site = 'Shop';
    protected static $cfg = [];
    public static function initialize(): void
    {
        static::load_config();
        DateTime::set_time_zone();
        Error_Handler::initialize();
        HTTP::set_request_type();
    }
    public static function get_version()
    {
        if (!isset(static::$version)) {
            $file = static::BASE_DIR . 'version.txt';
            $v = trim(file_get_contents($file));
            if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $v)) {
                static::$version = $v;
            } else {
                trigger_error('Version number is not numeric. Please verify: ' . $file);
            }
        }
        return static::$version;
    }
    public static function site_exists(string $site, $strict = true): bool
    {
        $class = 'OSC\Sites\\' . $site . '\\' . $site;
        if (class_exists($class)) {
            if (is_subclass_of($class, \OSC\OM\Sites_Interface::class)) {
                return true;
            }
            trigger_error('OSC\OM\OSCOM::siteExists() - ' . $site . ': Site does not implement OSC\OM\SitesInterface and cannot be loaded.');
        } elseif ($strict === true) {
            trigger_error('OSC\OM\OSCOM::siteExists() - ' . $site . ': Site does not exist.');
        }
        return false;
    }
    public static function load_site($site = null): void
    {
        if (!isset($site)) {
            $site = static::$site;
        }
        static::set_site($site);
    }
    public static function set_site($site): void
    {
        if (!static::site_exists($site)) {
            $site = static::$site;
        }
        static::$site = $site;
        $class = 'OSC\Sites\\' . $site . '\\' . $site;
        $OSCOM_Site = new $class();
        Registry::set('Site', $OSCOM_Site);
        $OSCOM_Site->set_page();
    }
    public static function get_site()
    {
        return static::$site;
    }
    public static function has_site_page()
    {
        return Registry::get('Site')->has_page();
    }
    public static function get_site_page_file()
    {
        return Registry::get('Site')->get_page()->get_file();
    }
    public static function use_site_template_with_page_file()
    {
        return Registry::get('Site')->get_page()->use_site_template();
    }
    public static function is_rpc(): bool
    {
        $OSCOM_Site = Registry::get('Site');
        return $OSCOM_Site->has_page() && $OSCOM_Site->get_page()->is_rpc();
    }
    public static function link($page, $parameters = null, $add_session_id = true, $search_engine_safe = true): string|array
    {
        $page = HTML::sanitize($page);
        $site = $req_site = static::$site;
        if (str_contains((string) $page, '/') && preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', (string) $page, $matches) === 1 && OSCOM::site_exists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }
        if (!is_bool($add_session_id)) {
            $add_session_id = true;
        }
        if (!is_bool($search_engine_safe)) {
            $search_engine_safe = true;
        }
        if ($add_session_id === true && $site !== $req_site) {
            $add_session_id = false;
        }
        $link = static::get_config('http_server', $req_site) . static::get_config('http_path', $req_site) . $page;
        if (!empty($parameters)) {
            $p = HTML::sanitize($parameters);
            $p = str_replace([
                '\\',
                // apps
                '{',
                // product attributes
                '}',
            ], ['%5C', '%7B', '%7D'], $p);
            $link .= '?' . $p;
            $separator = '&';
        } else {
            $separator = '?';
        }
        while (str_ends_with($link, '&') || str_ends_with($link, '?')) {
            $link = substr($link, 0, -1);
        }
        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        if ($add_session_id == true && Registry::exists('Session')) {
            $OSCOM_Session = Registry::get('Session');
            if ($OSCOM_Session->has_started() && $OSCOM_Session->is_force_cookies() === false) {
                if (strlen(SID) > 0 || (HTTP::get_request_type() == 'NONSSL' && parse_url((string) static::get_config('http_server', $req_site), PHP_URL_SCHEME) == 'https' || HTTP::get_request_type() == 'SSL' && parse_url((string) static::get_config('http_server', $req_site), PHP_URL_SCHEME) == 'http')) {
                    $link .= $separator . HTML::sanitize(session_name() . '=' . session_id());
                }
            }
        }
        while (str_contains($link, '&&')) {
            $link = str_replace('&&', '&', $link);
        }
        if ($search_engine_safe == true && defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
            return str_replace(['?', '&', '='], '/', $link);
        }
        return $link;
    }
    public static function link_image(): mixed
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            $args[0] = null;
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        $args[2] = false;
        $page = $args[0];
        $req_site = static::$site;
        if (str_contains((string) $page, '/') && preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', (string) $page, $matches) === 1 && OSCOM::site_exists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }
        $args[0] = $req_site . '/' . static::get_config('http_images_path', $req_site) . $page;
        return forward_static_call_array(static::link(...), $args);
    }
    public static function link_public(): mixed
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            $args[0] = null;
        }
        if (!isset($args[1])) {
            $args[1] = null;
        }
        $args[2] = false;
        $page = $args[0];
        $req_site = static::$site;
        if (str_contains((string) $page, '/') && preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', (string) $page, $matches) === 1 && OSCOM::site_exists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }
        $args[0] = 'Shop/public/Sites/' . $req_site . '/' . $page;
        return forward_static_call_array(static::link(...), $args);
    }
    public static function redirect(): void
    {
        $args = func_get_args();
        $url = forward_static_call_array(static::link(...), $args);
        if (str_contains((string) $url, "\n") || str_contains((string) $url, "\r")) {
            $url = static::link('index.php', '', false);
        }
        HTTP::redirect($url);
    }
    public static function get_def(): mixed
    {
        $OSCOM_Language = Registry::get('Language');
        return call_user_func_array([$OSCOM_Language, 'getDef'], func_get_args());
    }
    public static function has_route(array $path): bool
    {
        return array_slice(array_keys($_GET), 0, count($path)) == $path;
    }
    public static function load_config(): void
    {
        static::load_config_file(static::BASE_DIR . 'Conf/global.php', 'global');
        if (is_file(static::BASE_DIR . 'Custom/Conf/global.php')) {
            static::load_config_file(static::BASE_DIR . 'Custom/Conf/global.php', 'global');
        }
        foreach (glob(static::BASE_DIR . 'Sites/*', GLOB_ONLYDIR) as $s) {
            $s = basename($s);
            if (static::site_exists($s, false) && is_file(static::BASE_DIR . 'Sites/' . $s . '/site_conf.php')) {
                static::load_config_file(static::BASE_DIR . 'Sites/' . $s . '/site_conf.php', $s);
                if (is_file(static::BASE_DIR . 'Custom/Sites/' . $s . '/site_conf.php')) {
                    static::load_config_file(static::BASE_DIR . 'Custom/Sites/' . $s . '/site_conf.php', $s);
                }
            }
        }
    }
    public static function load_config_file($file, $group): void
    {
        $cfg = [];
        if (is_file($file)) {
            include $file;
            if (isset($ini)) {
                $cfg = parse_ini_string($ini);
            }
        }
        if (!empty($cfg)) {
            static::$cfg[$group] = isset(static::$cfg[$group]) ? array_merge(static::$cfg[$group], $cfg) : $cfg;
        }
    }
    public static function get_config($key, $group = null)
    {
        if (!isset($group)) {
            $group = static::get_site();
        }
        return static::$cfg[$group][$key] ?? static::$cfg['global'][$key];
    }
    public static function config_exists($key, $group = null)
    {
        if (!isset($group)) {
            $group = static::get_site();
        }
        if (isset(static::$cfg[$group][$key])) {
            return true;
        }
        return isset(static::$cfg['global'][$key]);
    }
    public static function set_config($key, $value, $group = null): void
    {
        if (!isset($group)) {
            $group = 'global';
        }
        static::$cfg[$group][$key] = $value;
    }
    public static function autoload($class)
    {
        $prefix = 'OSC\\';
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return false;
        }
        if (strncmp($prefix . 'OM\Module\\', $class, strlen($prefix . 'OM\Module\\')) === 0) {
            // TODO remove and fix namespace
            $file = dirname(OSCOM_BASE_DIR) . '/' . str_replace(['OSC\OM\\', '\\'], ['', '/'], $class) . '.php';
            $custom = dirname(OSCOM_BASE_DIR) . '/' . str_replace(['OSC\OM\\', '\\'], ['OSC\Custom\OM\\', '/'], $class) . '.php';
        } else {
            $file = dirname(OSCOM_BASE_DIR) . '/' . str_replace('\\', '/', $class) . '.php';
            $custom = str_replace('OSC/OM/', 'OSC/Custom/OM/', $file);
        }
        if (is_file($custom)) {
            require $custom;
        } elseif (is_file($file)) {
            require $file;
        }
    }
}