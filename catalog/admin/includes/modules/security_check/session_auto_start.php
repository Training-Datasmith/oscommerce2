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
class Security_Check_session_auto_start
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/session_auto_start');
    }
    public function pass(): bool
    {
        return (bool) ini_get('session.auto_start') == false;
    }
    public function get_message()
    {
        return OSCOM::get_def('warning_session_auto_start');
    }
}