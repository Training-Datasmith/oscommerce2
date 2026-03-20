<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Session
{
    public static function load($name = null)
    {
        $class_name = 'OSC\OM\Session\\' . OSCOM::get_config('store_sessions');
        if (!class_exists($class_name)) {
            trigger_error('Session Handler \'' . $class_name . '\' does not exist, using default \'OSC\OM\Session\File\'', E_USER_NOTICE);
            $class_name = \OSC\OM\Session\File::class;
        } elseif (!is_subclass_of($class_name, \OSC\OM\Session_Abstract::class)) {
            trigger_error('Session Handler \'' . $class_name . '\' does not extend OSC\OM\SessionAbstract, using default \'OSC\OM\Session\File\'', E_USER_NOTICE);
            $class_name = \OSC\OM\Session\File::class;
        }
        $obj = new $class_name();
        if (!isset($name)) {
            $name = 'oscomid';
        }
        $obj->set_name($name);
        return $obj;
    }
}