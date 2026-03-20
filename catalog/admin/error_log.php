<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\DateTime;
use OSC\OM\Error_Handler;
use OSC\OM\File_System;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$files = [];
foreach (glob(Error_Handler::get_directory() . 'errors-*.txt') as $f) {
    $key = basename($f, '.txt');
    if (preg_match('/^errors-([0-9]{4})([0-9]{2})([0-9]{2})$/', $key, $matches)) {
        $files[$key] = ['path' => $f, 'key' => $key, 'date' => DateTime::to_short($matches[1] . '-' . $matches[2] . '-' . $matches[3]), 'size' => filesize($f)];
    }
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'delete':
            if (isset($_GET['log']) && array_key_exists($_GET['log'], $files)) {
                if (unlink($files[$_GET['log']]['path'])) {
                    $oscom_message_stack->add(OSCOM::get_def('ms_success_delete', ['log' => $files[$_GET['log']]['key']]), 'success');
                } else {
                    $oscom_message_stack->add(OSCOM::get_def('ms_error_delete', ['log' => $files[$_GET['log']]['key']]), 'error');
                }
            }
            OSCOM::redirect('error_log.php');
            break;
        case 'deleteAll':
            $result = true;
            foreach ($files as $f) {
                if (!unlink($f['path'])) {
                    $result = false;
                }
            }
            if ($result === true) {
                $oscom_message_stack->add(OSCOM::get_def('ms_success_delete_all'), 'success');
            } else {
                $oscom_message_stack->add(OSCOM::get_def('ms_error_delete_all'), 'success');
            }
            OSCOM::redirect('error_log.php');
            break;
    }
}
require $osc_template->get_file('template_top.php');
if ($action == 'view' && isset($_GET['log']) && array_key_exists($_GET['log'], $files)) {
    $log = $files[$_GET['log']];
    ?>

<div class="pull-right">
  <?php 
    echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link('error_log.php'), null, 'btn-info') . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash-o', OSCOM::link('error_log.php', 'action=delete&log=' . $log['key']), null, 'btn-danger');
    ?>
</div>

<h2><i class="fa fa-exclamation-circle"></i> <a href="<?php 
    echo OSCOM::link('error_log.php');
    ?>"><?php 
    echo OSCOM::get_def('heading_title');
    ?></a></h2>

<h3><?php 
    echo HTML::output_protected($log['date']);
    ?></h3>

<p>
  <?php 
    echo HTML::textarea_field('log', '100', '30', file_get_contents($log['path']), 'readonly', false);
    ?>
</p>

<?php 
} else {
    ?>

<div class="pull-right">
  <?php 
    echo HTML::button(OSCOM::get_def('button_delete_all'), 'fa fa-trash', OSCOM::link('error_log.php', 'action=deleteAll'), null, 'btn-danger');
    ?>
</div>

<h2><i class="fa fa-exclamation-circle"></i> <a href="<?php 
    echo OSCOM::link('error_log.php');
    ?>"><?php 
    echo OSCOM::get_def('heading_title');
    ?></a></h2>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
    echo OSCOM::get_def('table_heading_filename');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_filesize');
    ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
    foreach ($files as $f) {
        ?>

    <tr>
      <td><?php 
        echo $f['date'];
        ?></td>
      <td class="text-right"><?php 
        echo $f['size'];
        ?></td>
      <td class="action"><a href="<?php 
        echo OSCOM::link('error_log.php', 'action=view&log=' . $f['key']);
        ?>"><i class="fa fa-file-text-o" title="<?php 
        echo OSCOM::get_def('button_view');
        ?>"></i></a></td>
    </tr>

<?php 
    }
    ?>

  </tbody>
</table>

<p>
  <?php 
    echo OSCOM::get_def('log_directory', ['path' => File_System::display_path(Error_Handler::get_directory())]);
    ?>
</p>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';