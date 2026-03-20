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
class bm_product_social_bookmarks
{
    public $code = 'bm_product_social_bookmarks';
    /**
     * @var 'boxes_column_left'|'boxes_column_right'
     */
    public $group = 'boxes';
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->title = OSCOM::get_def('module_boxes_product_social_bookmarks_title');
        $this->description = OSCOM::get_def('module_boxes_product_social_bookmarks_description');
        if (defined('MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS')) {
            $this->sort_order = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER;
            $this->enabled = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS == 'True';
            $this->group = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT == 'Left Column' ? 'boxes_column_left' : 'boxes_column_right';
        }
    }
    public function execute(): void
    {
        global $osc_template;
        if (isset($_GET['products_id']) && defined('MODULE_SOCIAL_BOOKMARKS_INSTALLED') && tep_not_null(MODULE_SOCIAL_BOOKMARKS_INSTALLED)) {
            $sbm_array = explode(';', (string) MODULE_SOCIAL_BOOKMARKS_INSTALLED);
            $social_bookmarks = [];
            foreach ($sbm_array as $sbm) {
                $class = basename($sbm, '.php');
                if (!class_exists($class)) {
                    $this->lang->load_definitions('modules/social_bookmarks/' . pathinfo($sbm, PATHINFO_FILENAME));
                    include 'includes/modules/social_bookmarks/' . $class . '.php';
                }
                $sb = new $class();
                if ($sb->is_enabled()) {
                    $social_bookmarks[] = $sb->get_output();
                }
            }
            if (!empty($social_bookmarks)) {
                ob_start();
                include 'includes/modules/boxes/templates/product_social_bookmarks.php';
                $data = ob_get_clean();
                $osc_template->add_block($data, $this->group);
            }
        }
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Product Social Bookmarks Module', 'configuration_key' => 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Do you want to add the module to your shop?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Content Placement', 'configuration_key' => 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT', 'configuration_value' => 'Right Column', 'configuration_description' => 'Should the module be loaded in the left or right column?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER'];
    }
}