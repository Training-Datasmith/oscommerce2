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
class Security_Check_session_storage
{
    public $type = 'warning';
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/session_storage');
    }
    public function pass(): bool
    {
        if (OSCOM::get_config('store_sessions') != '') {
            return true;
        }
        return (bool) File_System::is_writable(session_save_path());
    }
    public function get_message()
    {
        if (OSCOM::get_config('store_sessions') == '') {
            if (!is_dir(session_save_path())) {
                return OSCOM::get_def('warning_session_directory_non_existent', ['session_path' => session_save_path()]);
            }
            if (!File_System::is_writable(session_save_path())) {
                return OSCOM::get_def('warning_session_directory_not_writeable', ['session_path' => session_save_path()]);
            }
        }
    }
}