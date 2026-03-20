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
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'insert':
            $username = HTML::sanitize($_POST['username']);
            $password = HTML::sanitize($_POST['password']);
            $Qcheck = $OSCOM_Db->get('administrators', 'id', ['user_name' => $username], null, 1);
            if (!$Qcheck->check()) {
                $OSCOM_Db->save('administrators', ['user_name' => $username, 'user_password' => Hash::encrypt($password)]);
            } else {
                $oscom_message_stack->add(OSCOM::get_def('error_administrator_exists'), 'error');
            }
            OSCOM::redirect(FILENAME_ADMINISTRATORS);
            break;
        case 'save':
            $username = HTML::sanitize($_POST['username']);
            $password = HTML::sanitize($_POST['password']);
            $Qcheck = $OSCOM_Db->get('administrators', ['id', 'user_name'], ['id' => (int) $_GET['aID']]);
            // update username in current session if changed
            if ($Qcheck->value_int('id') === $_SESSION['admin']['id'] && $username !== $_SESSION['admin']['username']) {
                $_SESSION['admin']['username'] = $username;
            }
            $OSCOM_Db->save('administrators', ['user_name' => $username], ['id' => (int) $_GET['aID']]);
            if (tep_not_null($password)) {
                $OSCOM_Db->save('administrators', ['user_password' => Hash::encrypt($password)], ['id' => (int) $_GET['aID']]);
            }
            OSCOM::redirect(FILENAME_ADMINISTRATORS, 'aID=' . (int) $_GET['aID']);
            break;
        case 'deleteconfirm':
            $id = (int) $_GET['aID'];
            $Qcheck = $OSCOM_Db->get('administrators', ['id', 'user_name'], ['id' => $id]);
            if ($_SESSION['admin']['id'] === $Qcheck->value_int('id')) {
                unset($_SESSION['admin']);
            }
            $OSCOM_Db->delete('administrators', ['id' => $id]);
            OSCOM::redirect(FILENAME_ADMINISTRATORS);
            break;
    }
}
$show_listing = true;
require $osc_template->get_file('template_top.php');
if (empty($action)) {
    ?>

<div class="pull-right">
  <?php 
    echo HTML::button(OSCOM::get_def('image_insert'), 'fa fa-plus', OSCOM::link('administrators.php', 'action=new'), null, 'btn-info');
    ?>
</div>

<?php 
}
?>

<h2><i class="fa fa-users"></i> <a href="<?php 
echo OSCOM::link('administrators.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<?php 
if (!empty($action)) {
    $heading = $contents = [];
    if ($action != 'new') {
        if (isset($_GET['aID'])) {
            $Qadmin = $OSCOM_Db->get('administrators', ['id', 'user_name'], ['id' => (int) $_GET['aID']]);
            if ($Qadmin->fetch() !== false) {
                $a_info = new Object_Info($Qadmin->to_array());
                switch ($action) {
                    case 'edit':
                        $heading[] = ['text' => HTML::output_protected($a_info->user_name)];
                        $contents = ['form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $a_info->id . '&action=save'), 'post', 'autocomplete="off"')];
                        $contents[] = ['text' => OSCOM::get_def('text_info_edit_intro')];
                        $contents[] = ['text' => OSCOM::get_def('text_info_username') . '<br />' . HTML::input_field('username', $a_info->user_name)];
                        $contents[] = ['text' => OSCOM::get_def('text_info_new_password') . '<br />' . HTML::password_field('password')];
                        $contents[] = ['text' => HTML::button(OSCOM::get_def('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link')];
                        break;
                    case 'delete':
                        $heading[] = ['text' => HTML::output_protected($a_info->user_name)];
                        $contents = ['form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $a_info->id . '&action=deleteconfirm'))];
                        $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
                        $contents[] = ['text' => '<strong>' . HTML::output_protected($a_info->user_name) . '</strong>'];
                        $contents[] = ['text' => HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link')];
                        break;
                }
            }
        }
    } else {
        $heading[] = ['text' => OSCOM::get_def('text_info_heading_new_administrator')];
        $contents = ['form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'action=insert'), 'post', 'autocomplete="off"')];
        $contents[] = ['text' => OSCOM::get_def('text_info_insert_intro')];
        $contents[] = ['text' => OSCOM::get_def('text_info_username') . '<br />' . HTML::input_field('username')];
        $contents[] = ['text' => OSCOM::get_def('text_info_password') . '<br />' . HTML::password_field('password')];
        $contents[] = ['text' => HTML::button(OSCOM::get_def('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link')];
    }
    if (tep_not_null($heading) && tep_not_null($contents)) {
        $show_listing = false;
        echo HTML::panel($heading, $contents, ['type' => 'info']);
    }
}
if ($show_listing === true) {
    ?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
    echo OSCOM::get_def('table_heading_administrators');
    ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
    $Qadmins = $OSCOM_Db->get('administrators', ['id', 'user_name'], null, 'user_name');
    while ($Qadmins->fetch()) {
        ?>

    <tr>
      <td><?php 
        echo $Qadmins->value_protected('user_name');
        ?></td>
      <td class="action"><a href="<?php 
        echo OSCOM::link('administrators.php', 'aID=' . $Qadmins->value_int('id') . '&action=edit');
        ?>"><i class="fa fa-pencil" title="<?php 
        echo OSCOM::get_def('image_edit');
        ?>"></i></a><a href="<?php 
        echo OSCOM::link('administrators.php', 'aID=' . $Qadmins->value_int('id') . '&action=delete');
        ?>"><i class="fa fa-trash" title="<?php 
        echo OSCOM::get_def('image_delete');
        ?>"></i></a></td>
    </tr>

<?php 
    }
    ?>

  </tbody>
</table>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';