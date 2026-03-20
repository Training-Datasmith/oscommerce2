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
class Security_Check_file_uploads
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/file_uploads');
    }
    public function pass(): bool
    {
        return (bool) ini_get('file_uploads');
    }
    public function get_message()
    {
        return OSCOM::get_def('warning_file_uploads_disabled');
    }
}