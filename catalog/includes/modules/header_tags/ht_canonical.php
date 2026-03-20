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
class ht_canonical
{
    public $code = 'ht_canonical';
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
        $this->title = OSCOM::get_def('module_header_tags_canonical_title');
        $this->description = OSCOM::get_def('module_header_tags_canonical_description');
        if (defined('MODULE_HEADER_TAGS_CANONICAL_STATUS')) {
            $this->sort_order = MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER;
            $this->enabled = MODULE_HEADER_TAGS_CANONICAL_STATUS == 'True';
        }
    }
    public function execute(): void
    {
        global $PHP_SELF, $c_path, $osc_template, $category_depth;
        if (basename((string) $PHP_SELF) == 'product_info.php') {
            $osc_template->add_block('<link rel="canonical" href="' . OSCOM::link('product_info.php', 'products_id=' . (int) $_GET['products_id'], false) . '" />' . "\n", $this->group);
        } elseif (basename((string) $PHP_SELF) == 'index.php') {
            if (isset($c_path) && tep_not_null($c_path) && $category_depth == 'products') {
                $osc_template->add_block('<link rel="canonical" href="' . OSCOM::link('index.php', 'view=all&cPath=' . $c_path, false) . '" />' . "\n", $this->group);
            } elseif (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
                $osc_template->add_block('<link rel="canonical" href="' . OSCOM::link('index.php', 'view=all&manufacturers_id=' . (int) $_GET['manufacturers_id'], false) . '" />' . "\n", $this->group);
            }
        } else {
            $view_all_pages = ['products_new.php', 'specials.php'];
            if (in_array(basename((string) $PHP_SELF), $view_all_pages)) {
                $osc_template->add_block('<link rel="canonical" href="' . OSCOM::link($PHP_SELF, 'view=all', false) . '" />' . "\n", $this->group);
            }
        }
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_HEADER_TAGS_CANONICAL_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Canonical Module', 'configuration_key' => 'MODULE_HEADER_TAGS_CANONICAL_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to enable the Canonical module?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_HEADER_TAGS_CANONICAL_STATUS', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER'];
    }
}