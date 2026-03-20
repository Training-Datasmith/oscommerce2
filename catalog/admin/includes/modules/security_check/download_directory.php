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
class Security_Check_download_directory
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/download_directory');
    }
    public function pass()
    {
        if (DOWNLOAD_ENABLED != 'true') {
            return true;
        }
        return is_dir(OSCOM::get_config('dir_root', 'Shop') . 'download/');
    }
    public function get_message()
    {
        return OSCOM::get_def('warning_download_directory_non_existent', ['download_path' => OSCOM::get_config('dir_root', 'Shop') . 'download/']);
    }
}