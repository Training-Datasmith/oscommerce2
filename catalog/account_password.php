<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Hash;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
}
$OSCOM_Language->load_definitions('account_password');
if (isset($_POST['action']) && $_POST['action'] == 'process' && isset($_POST['formid']) && $_POST['formid'] == $_SESSION['sessiontoken']) {
    $password_current = HTML::sanitize($_POST['password_current']);
    $password_new = HTML::sanitize($_POST['password_new']);
    $password_confirmation = HTML::sanitize($_POST['password_confirmation']);
    $error = false;
    if (strlen((string) $password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $message_stack->add('account_password', OSCOM::get_def('entry_password_new_error', ['min_length' => ENTRY_PASSWORD_MIN_LENGTH]));
    } elseif ($password_new != $password_confirmation) {
        $error = true;
        $message_stack->add('account_password', OSCOM::get_def('entry_password_new_error_not_matching'));
    }
    if ($error == false) {
        $Qcheck = $OSCOM_Db->prepare('select customers_password from :table_customers where customers_id = :customers_id');
        $Qcheck->bind_int(':customers_id', $_SESSION['customer_id']);
        $Qcheck->execute();
        if (Hash::verify($password_current, $Qcheck->value('customers_password'))) {
            $OSCOM_Db->save('customers', ['customers_password' => Hash::encrypt($password_new)], ['customers_id' => (int) $_SESSION['customer_id']]);
            $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => (int) $_SESSION['customer_id']]);
            $message_stack->add_session('account', OSCOM::get_def('success_password_updated'), 'success');
            OSCOM::redirect('account.php');
        } else {
            $error = true;
            $message_stack->add('account_password', OSCOM::get_def('error_current_password_not_matching'));
        }
    }
}
$breadcrumb->add(OSCOM::get_def('navbar_title_1'), OSCOM::link('account.php'));
$breadcrumb->add(OSCOM::get_def('navbar_title_2'), OSCOM::link('account_password.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<?php 
if ($message_stack->size('account_password') > 0) {
    echo $message_stack->output('account_password');
}
?>

<?php 
echo HTML::form('account_password', OSCOM::link('account_password.php'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']);
?>

<?php 
$Qcustomer = $OSCOM_Db->get('customers', 'customers_email_address', ['customers_id' => (int) $_SESSION['customer_id']]);
echo HTML::hidden_field('username', $Qcustomer->value('customers_email_address'), 'readonly autocomplete="username"');
?>

<div class="contentContainer">
  <p class="text-danger text-right"><?php 
echo OSCOM::get_def('form_required_information');
?></p>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputCurrent" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_password_current');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::password_field('password_current', null, 'required aria-required="true" autofocus="autofocus" id="inputCurrent" autocomplete="current-password" placeholder="' . OSCOM::get_def('entry_password_current_text') . '"');
?>
        <?php 
echo OSCOM::get_def('form_required_input');
?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_password_new');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::password_field('password_new', null, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . OSCOM::get_def('entry_password_new_text') . '"');
?>
        <?php 
echo OSCOM::get_def('form_required_input');
?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_password_confirmation');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::password_field('password_confirmation', null, 'required aria-required="true" id="inputConfirmation" autocomplete="new-password" placeholder="' . OSCOM::get_def('entry_password_confirmation_text') . '"');
?>
        <?php 
echo OSCOM::get_def('form_required_input');
?>
      </div>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php 
echo HTML::button(OSCOM::get_def('image_button_back'), 'fa fa-angle-left', OSCOM::link('account.php'));
?></div>
    <div class="col-xs-6 text-right"><?php 
echo HTML::button(OSCOM::get_def('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success');
?></div>
  </div>

</div>

</form>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';