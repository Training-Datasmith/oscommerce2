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
$g_id = $_GET['gID'] ?? 1;
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'save':
            $c_id = HTML::sanitize($_GET['cID']);
            if (isset($_POST['configuration_value'])) {
                $configuration_value = $_POST['configuration_value'];
            } else {
                $configuration_value = '';
            }
            $OSCOM_Db->save('configuration', ['configuration_value' => $configuration_value, 'last_modified' => 'now()'], ['configuration_id' => (int) $c_id]);
            OSCOM::redirect(FILENAME_CONFIGURATION, 'gID=' . $g_id . '&cID=' . $c_id);
            break;
    }
}
$Qgroup = $OSCOM_Db->get('configuration_group', 'configuration_group_title', ['configuration_group_id' => (int) $g_id]);
$show_listing = true;
require $osc_template->get_file('template_top.php');
?>

<h2><i class="fa fa-cog"></i> <a href="<?php 
echo OSCOM::link('configuration.php', 'gID=' . $g_id);
?>"><?php 
echo $Qgroup->value_protected('configuration_group_title');
?></a></h2>

<?php 
if (!empty($action)) {
    $heading = $contents = [];
    if (isset($_GET['cID'])) {
        $Qcfg = $OSCOM_Db->get('configuration', ['configuration_id', 'configuration_title', 'configuration_key', 'configuration_value', 'configuration_description', 'set_function'], ['configuration_id' => (int) $_GET['cID']]);
        if ($Qcfg->fetch() !== false) {
            $c_info = new Object_Info($Qcfg->to_array());
            if ($action == 'edit') {
                $heading[] = ['text' => $c_info->configuration_title];
                if (!empty($c_info->set_function)) {
                    eval('$value_field = ' . $c_info->set_function . '"' . htmlspecialchars($c_info->configuration_value) . '");');
                } else {
                    $value_field = HTML::input_field('configuration_value', $c_info->configuration_value);
                }
                $contents = ['form' => HTML::form('configuration', OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $g_id . '&cID=' . $c_info->configuration_id . '&action=save'))];
                $contents[] = ['text' => OSCOM::get_def('text_info_edit_intro')];
                $contents[] = ['text' => $c_info->configuration_description];
                $contents[] = ['text' => $value_field];
                $contents[] = ['text' => HTML::button(OSCOM::get_def('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::get_def('image_cancel'), null, OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $g_id), null, 'link')];
            }
        }
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
    echo OSCOM::get_def('table_heading_configuration_title');
    ?></th>
      <th><?php 
    echo OSCOM::get_def('table_heading_configuration_value');
    ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
    $Qcfg = $OSCOM_Db->get('configuration', ['configuration_id', 'configuration_title', 'configuration_value', 'use_function'], ['configuration_group_id' => (int) $g_id], 'sort_order');
    while ($Qcfg->fetch()) {
        if ($Qcfg->has_value('use_function') && tep_not_null($Qcfg->value('use_function'))) {
            $use_function = $Qcfg->value('use_function');
            if (preg_match('/->/', (string) $use_function)) {
                $class_method = explode('->', (string) $use_function);
                if (!is_object(${$class_method[0]})) {
                    include 'includes/classes/' . $class_method[0] . '.php';
                    ${$class_method[0]} = new $class_method[0]();
                }
                $cfg_value = tep_call_function($class_method[1], $Qcfg->value('configuration_value'), ${$class_method[0]});
            } else {
                $cfg_value = tep_call_function($use_function, $Qcfg->value('configuration_value'));
            }
        } else {
            $cfg_value = $Qcfg->value('configuration_value');
        }
        ?>

    <tr>
      <td><?php 
        echo $Qcfg->value('configuration_title');
        ?></td>
      <td><?php 
        echo htmlspecialchars((string) $cfg_value);
        ?></td>
      <td class="action"><a href="<?php 
        echo OSCOM::link('configuration.php', 'gID=' . $g_id . '&cID=' . $Qcfg->value_int('configuration_id') . '&action=edit');
        ?>"><i class="fa fa-pencil" title="<?php 
        echo OSCOM::get_def('image_edit');
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