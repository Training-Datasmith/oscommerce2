<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

interface Sites_Interface
{
    public function has_page();
    public function get_page();
    public function set_page();
    public static function resolve_route(array $route, array $routes);
}