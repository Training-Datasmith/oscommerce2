<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Cache;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class Security_Check_Extended_version_check
{
    public $type = 'warning';
    public $has_doc = true;
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/extended/version_check');
        $this->title = OSCOM::get_def('module_security_check_extended_version_check_title');
    }
    public function pass(): bool
    {
        $version_cache = new Cache('core_version_check');
        return $version_cache->exists() && $version_cache->get_time() > strtotime('-30 days');
    }
    public function get_message(): string
    {
        return '<a href="' . OSCOM::link('online_update.php') . '">' . OSCOM::get_def('module_security_check_extended_version_check_error') . '</a>';
    }
}