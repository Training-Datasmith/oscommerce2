<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
$cl_box_groups[] = ['heading' => OSCOM::get_def('box_heading_modules'), 'apps' => []];
foreach ($cfg_modules->get_all() as $m) {
    $cl_box_groups[sizeof($cl_box_groups) - 1]['apps'][] = ['code' => FILENAME_MODULES, 'title' => $m['title'], 'link' => OSCOM::link(FILENAME_MODULES, 'set=' . $m['code'])];
}