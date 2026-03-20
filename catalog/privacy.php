<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$OSCOM_Language->load_definitions('privacy');
$breadcrumb->add(OSCOM::get_def('navbar_title'), OSCOM::link('privacy.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">
    <?php 
echo OSCOM::get_def('text_information');
?>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php 
echo HTML::button(OSCOM::get_def('image_button_continue'), 'fa fa-angle-right', OSCOM::link('index.php'));
?></div>
  </div>
</div>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';