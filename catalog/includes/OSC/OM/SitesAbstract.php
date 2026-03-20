<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

abstract class Sites_Abstract implements \OSC\OM\Sites_Interface
{
    protected string $code;
    protected $page;
    protected $app;
    protected $route;
    public $actions_index = 1;
    abstract protected function init();
    abstract public function set_page();
    final public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->get_short_name();
        return $this->init();
    }
    public function get_code()
    {
        return $this->code;
    }
    public function has_page()
    {
        return isset($this->page);
    }
    public function get_page()
    {
        return $this->page;
    }
    public function get_route()
    {
        return $this->route;
    }
    public static function resolve_route(array $route, array $routes)
    {
    }
}