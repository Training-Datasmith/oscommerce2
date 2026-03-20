<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\OSCOM;
chdir('../../../../../');
require 'includes/application_top.php';
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
}
if (defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('sage_pay_direct.php', explode(';', (string) MODULE_PAYMENT_INSTALLED))) {
    if (!class_exists('sage_pay_direct')) {
        $OSCOM_Language->load_definitions('modules/payment/sage_pay_direct');
        include 'includes/modules/payment/sage_pay_direct.php';
    }
    $sage_pay_direct = new sage_pay_direct();
    if (!$sage_pay_direct->enabled) {
        OSCOM::redirect('account.php');
    }
} else {
    OSCOM::redirect('account.php');
}
$OSCOM_Language->load_definitions('modules/content/account/cm_account_sage_pay_cards');
require 'includes/modules/content/account/cm_account_sage_pay_cards.php';
$sage_pay_cards = new cm_account_sage_pay_cards();
if (!$sage_pay_cards->is_enabled()) {
    OSCOM::redirect('account.php');
}
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['formid']) && $_GET['formid'] == md5((string) $_SESSION['sessiontoken'])) {
        $Qtoken = $OSCOM_Db->get('customers_sagepay_tokens', ['id', 'sagepay_token'], ['id' => $_GET['id'], 'customers_id' => $_SESSION['customer_id']]);
        if ($Qtoken->fetch() !== false) {
            $sage_pay_direct->delete_card($Qtoken->value('sagepay_token'), $Qtoken->value_int('id'));
            $message_stack->add_session('cards', OSCOM::get_def('module_content_account_sage_pay_cards_success_deleted'), 'success');
        }
    }
    OSCOM::redirect('ext/modules/content/account/sage_pay/cards.php');
}
$breadcrumb->add(OSCOM::get_def('module_content_account_sage_pay_cards_navbar_title_1'), OSCOM::link('account.php'));
$breadcrumb->add(OSCOM::get_def('module_content_account_sage_pay_cards_navbar_title_2'), OSCOM::link('ext/modules/content/account/sage_pay/cards.php'));
require $osc_template->get_file('template_top.php');
?>

<h1><?php 
echo OSCOM::get_def('module_content_account_sage_pay_cards_heading_title');
?></h1>

<?php 
if ($message_stack->size('cards') > 0) {
    echo $message_stack->output('cards');
}
?>

<div class="contentContainer">
  <?php 
echo OSCOM::get_def('module_content_account_sage_pay_cards_text_description');
?>

  <h2><?php 
echo OSCOM::get_def('module_content_account_sage_pay_cards_saved_cards_title');
?></h2>

  <div class="contentText">

<?php 
$Qtokens = $OSCOM_Db->get('customers_sagepay_tokens', ['id', 'card_type', 'number_filtered', 'expiry_date'], ['customers_id' => $_SESSION['customer_id']], 'date_added');
if ($Qtokens->fetch() !== false) {
    do {
        ?>

    <div>
      <span style="float: right;"><?php 
        echo HTML::button(OSCOM::get_def('small_image_button_delete'), 'glyphicon glyphicon-trash', OSCOM::link('ext/modules/content/account/sage_pay/cards.php', 'action=delete&id=' . $Qtokens->value_int('id') . '&formid=' . md5((string) $_SESSION['sessiontoken'])));
        ?></span>
      <p><strong><?php 
        echo $Qtokens->value_protected('card_type');
        ?></strong>&nbsp;&nbsp;****<?php 
        echo $Qtokens->value_protected('number_filtered') . '&nbsp;&nbsp;' . HTML::output_protected(substr((string) $Qtokens->value('expiry_date'), 0, 2) . '/' . substr((string) $Qtokens->value('expiry_date'), 2));
        ?></p>
    </div>

<?php 
    } while ($Qtokens->fetch());
} else {
    ?>

    <div style="background-color: #FEEFB3; border: 1px solid #9F6000; margin: 10px 0px; padding: 5px 10px; border-radius: 10px;">
      <?php 
    echo OSCOM::get_def('module_content_account_sage_pay_cards_text_no_cards');
    ?>
    </div>

<?php 
}
?>

  </div>

  <div class="buttonSet">
    <?php 
echo HTML::button(OSCOM::get_def('image_button_back'), 'glyphicon glyphicon-chevron-left', OSCOM::link('account.php'));
?>
  </div>
</div>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';