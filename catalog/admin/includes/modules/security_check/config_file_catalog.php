<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\File_System;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class Security_Check_config_file_catalog
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/config_file_catalog');
    }
    public function pass(): bool
    {
        return !File_System::is_writable(OSCOM::get_config('dir_root', 'Shop') . 'includes/configure.php');
    }
    public function get_message()
    {
        return OSCOM::get_def('warning_config_file_writeable', ['configure_file_path' => OSCOM::get_config('dir_root', 'Shop') . 'includes/configure.php']);
    }
}