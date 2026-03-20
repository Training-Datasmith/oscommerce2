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
$OSCOM_Language->load_definitions('cookie_usage');
$breadcrumb->add(OSCOM::get_def('navbar_title'), OSCOM::link('cookie_usage.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">

    <div class="panel panel-danger">
      <div class="panel-heading"><?php 
echo OSCOM::get_def('box_information_heading');
?></div>
      <div class="panel-body">
        <?php 
echo OSCOM::get_def('box_information');
?>
      </div>
    </div>

    <div class="panel panel-danger">
      <div class="panel-body">
        <?php 
echo OSCOM::get_def('text_information');
?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php 
echo HTML::button(OSCOM::get_def('image_button_continue'), 'fa fa-angle-right', OSCOM::link('login.php'));
?></div>
  </div>
</div>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';