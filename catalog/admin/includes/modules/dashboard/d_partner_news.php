<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Cache;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class d_partner_news
{
    public $code = 'd_partner_news';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_admin_dashboard_partner_news_title');
        $this->description = OSCOM::get_def('module_admin_dashboard_partner_news_description');
        if (defined('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS')) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER;
            $this->enabled = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS == 'True';
        }
        if (!function_exists('json_decode')) {
            $this->description .= '<p style="color: #ff0000; font-weight: bold;">' . OSCOM::get_def('module_admin_dashboard_partner_news_error_json_decode') . '</p>';
            $this->enabled = false;
        }
    }
    public function get_output()
    {
        $result = $this->_get_content();
        $output = null;
        if (is_array($result) && !empty($result)) {
            $output = '<table class="table table-hover">
                    <thead>
                      <tr class="info">
                        <th>' . OSCOM::get_def('module_admin_dashboard_partner_news_title') . '</th>
                      </tr>
                    </thead>
                    <tbody>';
            foreach ($result as $p) {
                $output .= '    <tr>
                            <td><a href="' . $p['url'] . '" target="_blank"><strong>' . $p['title'] . '</strong></a> <span class="label label-info">' . $p['category_title'] . '</span><br />' . $p['status_update'] . '</td>
                          </tr>';
            }
            $output .= '    <tr>
                          <td class="text-right"><a href="https://www.oscommerce.com/Services" target="_blank">' . OSCOM::get_def('module_admin_dashboard_partner_news_more_title') . '</a></td>
                        </tr>
                      </tbody>
                    </table>';
        }
        return $output;
    }
    public function _get_content()
    {
        $result = null;
        $news_cache = new Cache('oscommerce_website-partner_news');
        if ($news_cache->exists(60)) {
            $result = $news_cache->get();
        } else {
            $response = HTTP::get_response(['url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerStatusUpdates']);
            if (!empty($response)) {
                $response = json_decode((string) $response, true);
                if (is_array($response) && !empty($response)) {
                    $result = $response;
                    $news_cache->save($result);
                }
            }
        }
        return $result;
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Partner News Module', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to show the latest osCommerce Partner News on the dashboard?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER'];
    }
}