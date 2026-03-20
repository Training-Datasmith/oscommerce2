<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\Is;
use OSC\OM\Mail;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$OSCOM_Language->load_definitions('contact_us');
if (isset($_GET['action']) && $_GET['action'] == 'send' && isset($_POST['formid']) && $_POST['formid'] == $_SESSION['sessiontoken']) {
    $error = false;
    $name = HTML::sanitize($_POST['name']);
    $email_address = HTML::sanitize($_POST['email']);
    $enquiry = HTML::sanitize($_POST['enquiry']);
    if (!Is::email($email_address)) {
        $error = true;
        $message_stack->add('contact', OSCOM::get_def('entry_email_address_check_error'));
    }
    $action_recorder = new Action_Recorder('ar_contact_us', $_SESSION['customer_id'] ?? null, $name);
    if (!$action_recorder->can_perform()) {
        $error = true;
        $action_recorder->record(false);
        $message_stack->add('contact', OSCOM::get_def('error_action_recorder', ['module_action_recorder_contact_us_email_minutes' => defined('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES') ? (int) MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES : 15]));
    }
    if ($error == false) {
        $contact_email = new Mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, $email_address, $name, OSCOM::get_def('email_subject', ['store_name' => STORE_NAME]));
        $contact_email->set_body($enquiry);
        $contact_email->send();
        $action_recorder->record();
        OSCOM::redirect('contact_us.php', 'action=success');
    }
}
$breadcrumb->add(OSCOM::get_def('navbar_title'), OSCOM::link('contact_us.php'));
require $osc_template->get_file('template_top.php');
?>

<div class="page-header">
  <h1><?php 
echo OSCOM::get_def('heading_title');
?></h1>
</div>

<?php 
if ($message_stack->size('contact') > 0) {
    echo $message_stack->output('contact');
}
if (isset($_GET['action']) && $_GET['action'] == 'success') {
    ?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-info"><?php 
    echo OSCOM::get_def('text_success');
    ?></div>
  </div>

  <div class="pull-right">
    <?php 
    echo HTML::button(OSCOM::get_def('image_button_continue'), 'fa fa-angle-right', OSCOM::link('index.php'));
    ?>
  </div>
</div>

<?php 
} else {
    ?>

<?php 
    echo HTML::form('contact_us', OSCOM::link('contact_us.php', 'action=send'), 'post', 'class="form-horizontal"', ['tokenize' => true]);
    ?>

<div class="contentContainer">
  <div class="contentText">

    <p class="text-danger text-right"><?php 
    echo OSCOM::get_def('form_required_information');
    ?></p>
    <div class="clearfix"></div>

    <div class="form-group has-feedback">
      <label for="inputFromName" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_name');
    ?></label>
      <div class="col-sm-9">
        <?php 
    echo HTML::input_field('name', null, 'required autofocus="autofocus" aria-required="true" id="inputFromName" placeholder="' . OSCOM::get_def('entry_name_text') . '"');
    echo OSCOM::get_def('form_required_input');
    ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputFromEmail" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_email');
    ?></label>
      <div class="col-sm-9">
        <?php 
    echo HTML::input_field('email', null, 'required aria-required="true" id="inputFromEmail" placeholder="' . OSCOM::get_def('entry_email_address_text') . '"', 'email');
    echo OSCOM::get_def('form_required_input');
    ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_enquiry');
    ?></label>
      <div class="col-sm-9">
        <?php 
    echo HTML::textarea_field('enquiry', 50, 15, null, 'required aria-required="true" id="inputEnquiry" placeholder="' . OSCOM::get_def('entry_enquiry_text') . '"');
    echo OSCOM::get_def('form_required_input');
    ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php 
    echo HTML::button(OSCOM::get_def('image_button_continue'), 'fa fa-send', null, null, 'btn-success');
    ?></div>
  </div>
</div>

</form>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';