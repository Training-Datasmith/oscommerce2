<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Modules;

interface Order_Total_Interface
{
    public function process();
    public function check();
    public function install();
    public function remove();
    public function keys();
}