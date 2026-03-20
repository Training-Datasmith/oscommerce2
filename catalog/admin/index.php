<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Apps;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (OSCOM::has_site_page()) {
    if (OSCOM::is_rpc() === false) {
        $page_file = OSCOM::get_site_page_file();
        if (empty($page_file) || !is_file($page_file)) {
            $page_file = OSCOM::get_config('dir_root', 'Shop') . 'includes/error_documents/404.php';
        }
        if (OSCOM::use_site_template_with_page_file()) {
            include $osc_template->get_file('template_top.php');
        }
        include $page_file;
        if (OSCOM::use_site_template_with_page_file()) {
            include $osc_template->get_file('template_bottom.php');
        }
    }
    goto main_sub3;
}
require $osc_template->get_file('template_top.php');
?>

<h2><i class="fa fa-home"></i> <a href="<?php 
echo OSCOM::link(FILENAME_DEFAULT);
?>"><?php 
echo STORE_NAME;
?></a></h2>

<?php 
if (defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED)) {
    $adm_array = explode(';', (string) MODULE_ADMIN_DASHBOARD_INSTALLED);
    $col = 0;
    foreach ($adm_array as $adm) {
        if (str_contains($adm, '\\')) {
            $class = Apps::get_module_class($adm, 'AdminDashboard');
        } else {
            $class = substr($adm, 0, strrpos($adm, '.'));
            if (!class_exists($class)) {
                $OSCOM_Language->load_definitions('modules/dashboard/' . pathinfo($adm, PATHINFO_FILENAME));
                include 'includes/modules/dashboard/' . $class . '.php';
            }
        }
        $ad = new $class();
        if ($ad->is_enabled()) {
            $col += 1;
            if ($col === 1) {
                echo '<div class="row">';
            }
            echo '<div class="col-md-6">' . $ad->get_output() . '</div>';
            if ($col === 2) {
                $col = 0;
                echo '</div>';
            }
        }
    }
    if ($col === 1) {
        echo '</div>';
    }
}
require $osc_template->get_file('template_bottom.php');
main_sub3:
// Sites and Apps skip to here
require 'includes/application_bottom.php';