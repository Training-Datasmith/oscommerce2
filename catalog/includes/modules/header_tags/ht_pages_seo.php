<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class ht_pages_seo
{
    public $code = 'ht_pages_seo';
    public $group = 'header_tags';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_header_tags_pages_seo_title');
        $this->description = OSCOM::get_def('module_header_tags_pages_seo_description');
        $this->description .= '<div class="secWarning">' . OSCOM::get_def('module_header_tags_pages_seo_helper') . '</div>';
        if (defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS')) {
            $this->sort_order = MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER;
            $this->enabled = MODULE_HEADER_TAGS_PAGES_SEO_STATUS == 'True';
        }
    }
    public function execute(): void
    {
        global $osc_template;
        if (defined('META_SEO_TITLE') && strlen((string) META_SEO_TITLE) > 0) {
            $osc_template->set_title(HTML::output(META_SEO_TITLE) . OSCOM::get_def('module_header_tags_pages_seo_separator') . $osc_template->get_title());
        }
        if (defined('META_SEO_DESCRIPTION') && strlen((string) META_SEO_DESCRIPTION) > 0) {
            $osc_template->add_block('<meta name="description" content="' . HTML::output(META_SEO_DESCRIPTION) . '" />' . "\n", $this->group);
        }
        if (defined('META_SEO_KEYWORDS') && strlen((string) META_SEO_KEYWORDS) > 0) {
            $osc_template->add_block('<meta name="keywords" content="' . HTML::output(META_SEO_KEYWORDS) . '" />' . "\n", $this->group);
        }
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Pages SEO Module', 'configuration_key' => 'MODULE_HEADER_TAGS_PAGES_SEO_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to allow this module to write SEO to your Pages?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_HEADER_TAGS_PAGES_SEO_STATUS', 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER'];
    }
}