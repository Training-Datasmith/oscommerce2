<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Modules;

use OSC\OM\Registry;
abstract class Admin_Dashboard_Abstract implements \OSC\OM\Modules\Admin_Dashboard_Interface
{
    /**
     * @var string
     */
    public $code;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;
    protected $db;
    abstract protected function init();
    abstract public function get_output();
    abstract public function install();
    abstract public function keys();
    final public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->get_short_name();
        $this->db = Registry::get('Db');
        $this->init();
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check()
    {
        return isset($this->sort_order);
    }
    public function remove()
    {
        return $this->db->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
}