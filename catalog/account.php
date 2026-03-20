<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
}
$OSCOM_Language->load_definitions('account');
$breadcrumb->add(OSCOM::get_def('navbar_title'), OSCOM::link('account.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<?php 
if ($message_stack->size('account') > 0) {
    echo $message_stack->output('account');
}
?>

<div class="contentContainer">
  <div class="row">

    <?php 
echo $osc_template->get_content('account');
?>

  </div>
</div>


<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';