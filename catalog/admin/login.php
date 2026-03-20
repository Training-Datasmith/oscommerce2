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
$login_request = true;
require 'includes/application_top.php';
$action = $_GET['action'] ?? '';
// prepare to logout an active administrator if the login page is accessed again
if (isset($_SESSION['admin'])) {
    $action = 'logoff';
}
if (tep_not_null($action)) {
    switch ($action) {
        case 'process':
            if (isset($_SESSION['redirect_origin']) && isset($_SESSION['redirect_origin']['auth_user']) && !isset($_POST['username'])) {
                $username = HTML::sanitize($_SESSION['redirect_origin']['auth_user']);
                $password = HTML::sanitize($_SESSION['redirect_origin']['auth_pw']);
            } else {
                $username = HTML::sanitize($_POST['username']);
                $password = HTML::sanitize($_POST['password']);
            }
            $action_recorder = new Action_Recorder_Admin('ar_admin_login', null, $username);
            if ($action_recorder->can_perform()) {
                $Qadmin = $OSCOM_Db->get('administrators', ['id', 'user_name', 'user_password'], ['user_name' => $username]);
                if ($Qadmin->fetch() !== false) {
                    if (Hash::verify($password, $Qadmin->value('user_password'))) {
                        // migrate old hashed password to new php password_hash
                        if (Hash::needs_rehash($Qadmin->value('user_password'))) {
                            $OSCOM_Db->save('administrators', ['user_password' => Hash::encrypt($password)], ['id' => $Qadmin->value_int('id')]);
                        }
                        $_SESSION['admin'] = ['id' => $Qadmin->value_int('id'), 'username' => $Qadmin->value('user_name')];
                        $action_recorder->_user_id = $_SESSION['admin']['id'];
                        $action_recorder->record();
                        if (isset($_SESSION['redirect_origin'])) {
                            $page = $_SESSION['redirect_origin']['page'];
                            $get_string = http_build_query($_SESSION['redirect_origin']['get']);
                            unset($_SESSION['redirect_origin']);
                            OSCOM::redirect($page, $get_string);
                        } else {
                            OSCOM::redirect(FILENAME_DEFAULT);
                        }
                    }
                }
                if (isset($_POST['username'])) {
                    $oscom_message_stack->add(OSCOM::get_def('error_invalid_administrator'), 'error');
                }
            } else {
                $oscom_message_stack->add(OSCOM::get_def('error_action_recorder', ['module_action_recorder_admin_login_minutes' => defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int) MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5]));
            }
            if (isset($_POST['username'])) {
                $action_recorder->record(false);
            }
            break;
        case 'logoff':
            $OSCOM_Hooks->call('Account', 'LogoutBefore');
            unset($_SESSION['admin']);
            if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
                $_SESSION['auth_ignore'] = true;
            }
            $OSCOM_Hooks->call('Account', 'LogoutAfter');
            OSCOM::redirect(FILENAME_DEFAULT);
            break;
        case 'create':
            $Qcheck = $OSCOM_Db->get('administrators', 'id', null, null, 1);
            if (!$Qcheck->check()) {
                $username = HTML::sanitize($_POST['username']);
                $password = HTML::sanitize($_POST['password']);
                if (!empty($username)) {
                    $OSCOM_Db->save('administrators', ['user_name' => $username, 'user_password' => Hash::encrypt($password)]);
                }
            }
            OSCOM::redirect(FILENAME_LOGIN);
            break;
    }
}
$Qcheck = $OSCOM_Db->get('administrators', 'id', null, null, 1);
if (!$Qcheck->check()) {
    $oscom_message_stack->add(OSCOM::get_def('text_create_first_administrator'), 'warning');
}
require $osc_template->get_file('template_top.php');
?>

<h2><i class="fa fa-home"></i> <a href="<?php 
echo OSCOM::link('login.php');
?>"><?php 
echo STORE_NAME;
?></a></h3>

<?php 
$heading = [];
$contents = [];
if ($Qcheck->check()) {
    $heading[] = ['text' => OSCOM::get_def('heading_title')];
    $contents = ['form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=process'))];
    $contents[] = ['text' => OSCOM::get_def('text_username') . '<br />' . HTML::input_field('username')];
    $contents[] = ['text' => OSCOM::get_def('text_password') . '<br />' . HTML::password_field('password')];
    $contents[] = ['text' => HTML::button(OSCOM::get_def('button_login'), 'fa fa-sign-in', null, null, 'btn-primary')];
} else {
    $heading[] = ['text' => OSCOM::get_def('heading_title')];
    $contents = ['form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=create'))];
    $contents[] = ['text' => OSCOM::get_def('text_create_first_administrator')];
    $contents[] = ['text' => OSCOM::get_def('text_username') . '<br />' . HTML::input_field('username')];
    $contents[] = ['text' => OSCOM::get_def('text_password') . '<br />' . HTML::password_field('password')];
    $contents[] = ['text' => HTML::button(OSCOM::get_def('button_create_administrator'), 'fa fa-sign-in', null, null, 'btn-primary')];
}
echo HTML::panel($heading, $contents);
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';