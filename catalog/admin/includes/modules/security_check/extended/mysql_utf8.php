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
class Security_Check_Extended_mysql_utf8
{
    public $type = 'warning';
    public $has_doc = true;
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/extended/mysql_utf8');
        $this->title = OSCOM::get_def('module_security_check_extended_mysql_utf8_title');
    }
    public function pass(): bool
    {
        $OSCOM_Db = Registry::get('Db');
        $Qcheck = $OSCOM_Db->query('show table status');
        if ($Qcheck->fetch() !== false) {
            do {
                if ($Qcheck->has_value('Collation') && $Qcheck->value('Collation') != 'utf8_unicode_ci') {
                    return false;
                }
            } while ($Qcheck->fetch());
        }
        return true;
    }
    public function get_message(): string
    {
        return '<a href="' . OSCOM::link('database_tables.php') . '">' . OSCOM::get_def('module_security_check_extended_mysql_utf8_error') . '</a>';
    }
}