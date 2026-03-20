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
class Security_Check_default_language
{
    public $type = 'error';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/default_language');
    }
    public function pass(): bool
    {
        return defined('DEFAULT_LANGUAGE');
    }
    public function get_message()
    {
        return OSCOM::get_def('error_no_default_language_defined');
    }
}