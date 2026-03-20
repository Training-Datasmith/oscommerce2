<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Modules;

interface Content_Interface
{
    public function execute();
    public function is_enabled();
    public function check();
    public function install();
    public function remove();
    public function keys();
}