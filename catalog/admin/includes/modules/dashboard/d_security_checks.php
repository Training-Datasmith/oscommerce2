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
class d_security_checks
{
    public $code = 'd_security_checks';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_admin_dashboard_security_checks_title');
        $this->description = OSCOM::get_def('module_admin_dashboard_security_checks_description');
        if (defined('MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS')) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER;
            $this->enabled = MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS == 'True';
        }
    }
    public function get_output()
    {
        global $PHP_SELF;
        $oscom_message_stack = Registry::get('MessageStack');
        $sec_check_types = ['info', 'warning', 'error'];
        $file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
        $secmodules_array = [];
        if ($secdir = @dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/')) {
            while ($file = $secdir->read()) {
                if (!is_dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/' . $file)) {
                    if (substr($file, strrpos($file, '.')) == $file_extension) {
                        $secmodules_array[] = $file;
                    }
                }
            }
            sort($secmodules_array);
            $secdir->close();
        }
        foreach ($secmodules_array as $secmodule) {
            include OSCOM::get_config('dir_root') . 'includes/modules/security_check/' . $secmodule;
            $secclass = 'securityCheck_' . substr($secmodule, 0, strrpos($secmodule, '.'));
            if (class_exists($secclass)) {
                $sec_check = new $secclass();
                if (!$sec_check->pass()) {
                    if (!in_array($sec_check->type, $sec_check_types)) {
                        $sec_check->type = 'info';
                    }
                    $oscom_message_stack->add($sec_check->get_message(), $sec_check->type, 'securityCheckModule');
                }
            }
        }
        if (!$oscom_message_stack->exists('securityCheckModule')) {
            $oscom_message_stack->add(OSCOM::get_def('module_admin_dashboard_security_checks_success'), 'success', 'securityCheckModule');
        }
        return $oscom_message_stack->get('securityCheckModule');
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Security Checks Module', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to run the security checks for this installation?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS', 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER'];
    }
}