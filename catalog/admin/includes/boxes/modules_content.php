<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
foreach ($cl_box_groups as &$group) {
    if ($group['heading'] == OSCOM::get_def('box_heading_modules')) {
        $group['apps'][] = ['code' => 'modules_content.php', 'title' => OSCOM::get_def('modules_admin_menu_modules_content'), 'link' => OSCOM::link('modules_content.php')];
        break;
    }
}