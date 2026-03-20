<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

abstract class Modules_Abstract
{
    /**
     * @var string
     */
    public $code;
    protected $interface;
    protected $ns = 'OSC\Apps\\';
    abstract public function get_info($app, $key, $data);
    abstract public function get_class($module);
    final public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->get_short_name();
        $this->init();
    }
    protected function init()
    {
    }
    public function filter($modules, $filter)
    {
        return $modules;
    }
}