<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Modules;

interface Admin_Dashboard_Interface
{
    public function get_output();
    public function install();
    public function keys();
    public function is_enabled();
    public function check();
    public function remove();
}