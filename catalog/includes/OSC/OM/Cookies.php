<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

class Cookies
{
    protected $domain;
    protected $path;

    public function __construct()
    {
        $this->domain = OSCOM::getConfig('http_cookie_domain');
        $this->path = OSCOM::getConfig('http_cookie_path');
    }

    public function set($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = true, $httponly = true): bool
    {
        return setcookie($name, (string) $value, ['expires' => $expire, 'path' => $path ?? $this->path, 'domain' => $domain ?? $this->domain, 'secure' => $secure, 'httponly' => $httponly]);
    }

    public function del($name, $path = null, $domain = null, $secure = true, $httponly = true): bool
    {
        if ($this->set($name, '', time() - 3600, $path, $domain, $secure, $httponly)) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
            }

            return true;
        }

        return false;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setDomain($domain): void
    {
        $this->domain = $domain;
    }

    public function setPath($path): void
    {
        $this->path = $path;
    }
}
