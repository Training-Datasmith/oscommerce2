<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Cache;
use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class d_version_check
{
    public $code = 'd_version_check';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_admin_dashboard_version_check_title');
        $this->description = OSCOM::get_def('module_admin_dashboard_version_check_description');
        if (defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS')) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER;
            $this->enabled = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS == 'True';
        }
    }
    public function get_output(): string
    {
        $current_version = OSCOM::get_version();
        $new_version = false;
        $version_cache = new Cache('core_version_check');
        if ($version_cache->exists()) {
            $date_last_checked = DateTime::to_short(date('Y-m-d H:i:s', $version_cache->get_time()), true);
            $releases = $version_cache->get();
            foreach ($releases as $version) {
                $version_array = explode('|', (string) $version);
                if (version_compare($current_version, $version_array[0], '<')) {
                    $new_version = true;
                    break;
                }
            }
        } else {
            $date_last_checked = OSCOM::get_def('module_admin_dashboard_version_check_never');
        }
        $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::get_def('module_admin_dashboard_version_check_title') . '</th>
                       <th class="text-right">' . OSCOM::get_def('module_admin_dashboard_version_check_date') . '</th>
                     </tr>
                   </thead>
                   <tbody>';
        if ($new_version == true) {
            $output .= '    <tr class="success">
                          <td colspan="2">' . HTML::image(OSCOM::link_image('icons/warning.gif'), OSCOM::get_def('icon_warning')) . '&nbsp;<strong>' . OSCOM::get_def('module_admin_dashboard_version_check_update_available') . '</strong></td>
                        </tr>';
        }
        return $output . ('    <tr>
                        <td><a href="' . OSCOM::link('online_update.php') . '">' . OSCOM::get_def('module_admin_dashboard_version_check_check_now') . '</a></td>
                        <td class="text-right">' . $date_last_checked . '</td>
                      </tr>
                    </tbody>
                  </table>');
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Version Check Module', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to show the version check results on the dashboard?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS', 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER'];
    }
}