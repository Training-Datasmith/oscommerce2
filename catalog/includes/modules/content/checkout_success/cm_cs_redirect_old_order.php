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
class cm_cs_redirect_old_order
{
    /**
     * @var class-string<\cm_cs_redirect_old_order>
     */
    public $code;
    /**
     * @var string
     */
    public $group;
    public $title;
    public $description;
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled = false;
    public function __construct()
    {
        $this->code = static::class;
        $this->group = basename(__DIR__);
        $this->title = OSCOM::get_def('module_content_checkout_success_redirect_old_order_title');
        $this->description = OSCOM::get_def('module_content_checkout_success_redirect_old_order_description');
        if (defined('MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_STATUS')) {
            $this->sort_order = MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_SORT_ORDER;
            $this->enabled = MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_STATUS == 'True';
        }
    }
    public function execute(): void
    {
        global $order_id;
        $OSCOM_Db = Registry::get('Db');
        if ((int) MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_MINUTES > 0) {
            $Qcheck = $OSCOM_Db->prepare('select 1 from :table_orders where orders_id = :orders_id and date_purchased < date_sub(now(), interval :limit_minutes minute) limit 1');
            $Qcheck->bind_int(':orders_id', $order_id);
            $Qcheck->bind_int(':limit_minutes', MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_MINUTES);
            $Qcheck->execute();
            if ($Qcheck->fetch() !== false) {
                OSCOM::redirect('account.php');
            }
        }
    }
    public function is_enabled()
    {
        return $this->enabled;
    }
    public function check(): bool
    {
        return defined('MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_STATUS');
    }
    public function install(): void
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Enable Redirect Old Order Module', 'configuration_key' => 'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_STATUS', 'configuration_value' => 'True', 'configuration_description' => 'Should customers be redirected when viewing old checkout success orders?', 'configuration_group_id' => '6', 'sort_order' => '1', 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Redirect Minutes', 'configuration_key' => 'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_MINUTES', 'configuration_value' => '60', 'configuration_description' => 'Redirect customers to the My Account page after an order older than this amount is viewed.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Sort Order', 'configuration_key' => 'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_SORT_ORDER', 'configuration_value' => '0', 'configuration_description' => 'Sort order of display. Lowest is displayed first.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    public function remove()
    {
        return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
    public function keys(): array
    {
        return ['MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_STATUS', 'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_MINUTES', 'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_SORT_ORDER'];
    }
}