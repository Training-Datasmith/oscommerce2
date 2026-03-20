<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\Registry;
class Action_Recorder
{
    public $_module;
    public $_user_id;
    public $_user_name;
    protected $lang;
    public function __construct($module, $user_id = null, $user_name = null)
    {
        global $PHP_SELF;
        $this->lang = Registry::get('Language');
        $module = HTML::sanitize(str_replace(' ', '', $module));
        if (defined('MODULE_ACTION_RECORDER_INSTALLED') && tep_not_null(MODULE_ACTION_RECORDER_INSTALLED)) {
            if (tep_not_null($module) && in_array($module . '.' . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.') + 1), explode(';', (string) MODULE_ACTION_RECORDER_INSTALLED))) {
                if (!class_exists($module)) {
                    if (is_file('includes/modules/action_recorder/' . $module . '.' . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.') + 1))) {
                        $this->lang->load_definitions('modules/action_recorder/' . $module);
                        include 'includes/modules/action_recorder/' . $module . '.' . substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.') + 1);
                    } else {
                        return;
                    }
                }
            } else {
                return;
            }
        } else {
            return;
        }
        $this->_module = $module;
        if (!empty($user_id) && is_numeric($user_id)) {
            $this->_user_id = $user_id;
        }
        if (!empty($user_name)) {
            $this->_user_name = $user_name;
        }
        $GLOBALS[$this->_module] = new $module();
        $GLOBALS[$this->_module]->set_identifier();
    }
    public function can_perform()
    {
        if (tep_not_null($this->_module)) {
            return $GLOBALS[$this->_module]->can_perform($this->_user_id, $this->_user_name);
        }
        return false;
    }
    public function get_title()
    {
        if (tep_not_null($this->_module)) {
            return $GLOBALS[$this->_module]->title;
        }
    }
    public function get_identifier()
    {
        if (tep_not_null($this->_module)) {
            return $GLOBALS[$this->_module]->identifier;
        }
    }
    public function record($success = true): void
    {
        $OSCOM_Db = Registry::get('Db');
        if (tep_not_null($this->_module)) {
            $OSCOM_Db->save('action_recorder', ['module' => $this->_module, 'user_id' => (int) $this->_user_id, 'user_name' => $this->_user_name, 'identifier' => $this->get_identifier(), 'success' => $success == true ? 1 : 0, 'date_added' => 'now()']);
        }
    }
    public function expire_entries()
    {
        if (tep_not_null($this->_module)) {
            return $GLOBALS[$this->_module]->expire_entries();
        }
    }
}