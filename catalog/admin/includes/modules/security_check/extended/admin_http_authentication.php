<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class Security_Check_Extended_admin_http_authentication
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/extended/admin_http_authentication');
        $this->title = OSCOM::get_def('module_security_check_extended_admin_http_authentication_title');
    }
    public function pass(): bool
    {
        return isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']);
    }
    public function get_message()
    {
        return OSCOM::get_def('module_security_check_extended_admin_http_authentication_error');
    }
}