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
class Security_Check_default_currency
{
    public $type = 'error';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/default_currency');
    }
    public function pass(): bool
    {
        return defined('DEFAULT_CURRENCY');
    }
    public function get_message()
    {
        return OSCOM::get_def('error_no_default_currency_defined');
    }
}