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
class sb_twitter
{
    public $code = 'sb_twitter';
    public $title;
    public $description;
    public $sort_order;
    public $icon = 'twitter.png';
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->title = OSCOM::get_def('module_social_bookmarks_twitter_title');
        $this->public_title = OSCOM::get_def('module_social_bookmarks_twitter_public_title');
        $this->description = OSCOM::get_def('module_social_bookmarks_twitter_description');
        if (defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS')) {
            $this->sort_order = MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER;
            $this->enabled = MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS == 'True';
        }
    }
    public function get_output(): string
    {
        return '<a href="http://twitter.com/home?status=' . urlencode(OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], false)) . '" target="_blank"><img src="' . OSCOM::link_image('social_bookmarks/' . $this->icon) . '" border="0" title="' . HTML::output_protected($this->public_title) . '" alt="' . HTML::output_protected($this->public_title) . '" /></a>';
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function get_icon()
    {
        return $this->icon;
    }
    public function get_public_title()
    {
        return $this->public_title;
    }
    public function check(): bool
    {
        return defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Twitter Module', 'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to allow products to be shared through Twitter?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER'];
    }
}