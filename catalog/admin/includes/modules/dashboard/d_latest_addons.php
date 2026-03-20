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
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class d_latest_addons
{
    public $code = 'd_latest_addons';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_admin_dashboard_latest_addons_title');
        $this->description = OSCOM::get_def('module_admin_dashboard_latest_addons_description');
        if (defined('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS')) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER;
            $this->enabled = MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS == 'True';
        }
    }
    public function get_output(): string
    {
        $entries = [];
        $addons_cache = new Cache('oscommerce_website-addons-latest5');
        if ($addons_cache->exists(360)) {
            $entries = $addons_cache->get();
        } else {
            $response = HTTP::get_response(['url' => 'https://www.oscommerce.com/index.php?RPC&GetLatestAddons']);
            if (!empty($response)) {
                $response = json_decode((string) $response, true);
                if (is_array($response) && count($response) === 5) {
                    $entries = $response;
                }
            }
            $addons_cache->save($entries);
        }
        $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::get_def('module_admin_dashboard_latest_addons_title') . '</th>
                       <th class="text-right">' . OSCOM::get_def('module_admin_dashboard_latest_addons_date') . '</th>
                     </tr>
                   </thead>
                   <tbody>';
        if (is_array($entries) && count($entries) === 5) {
            foreach ($entries as $item) {
                $output .= '    <tr>
                            <td><a href="' . HTML::output_protected($item['link']) . '" target="_blank">' . HTML::output_protected($item['title']) . '</a></td>
                            <td class="text-right" style="white-space: nowrap;">' . HTML::output_protected(DateTime::to_short($item['date'])) . '</td>
                          </tr>';
            }
        } else {
            $output .= '    <tr>
                          <td colspan="2">' . OSCOM::get_def('module_admin_dashboard_latest_addons_feed_error') . '</td>
                        </tr>';
        }
        return $output . ('    <tr>
                        <td class="text-right" colspan="2"><a href="http://addons.oscommerce.com" target="_blank" title="' . HTML::output_protected(OSCOM::get_def('module_admin_dashboard_latest_addons_icon_site')) . '"><span class="fa fa-fw fa-home"></span></a></td>
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
        return defined('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Latest Add-Ons Module', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to show the latest osCommerce Add-Ons on the dashboard?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS', 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER'];
    }
}