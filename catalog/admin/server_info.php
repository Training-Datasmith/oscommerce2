<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$OSCOM_Language->load_definitions('server_info');
$info = tep_get_system_information();
$server = parse_url((string) OSCOM::get_config('http_server'));
$action = $_GET['action'] ?? '';
switch ($action) {
    case 'getPhpInfo':
        phpinfo();
        exit;
    case 'submit':
        $response = HTTP::get_response(['url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&SaveUserServerInfo&v=2', 'parameters' => ['info' => json_encode($info)]]);
        if ($response != 'OK') {
            $oscom_message_stack->add(OSCOM::get_def('error_info_submit'), 'error');
        } else {
            $oscom_message_stack->add(OSCOM::get_def('success_info_submit'), 'success');
        }
        OSCOM::redirect('server_info.php');
        break;
    case 'save':
        $info_file = 'server_info-' . date('YmdHis') . '.txt';
        header('Content-type: text/plain');
        header('Content-disposition: attachment; filename=' . $info_file);
        echo tep_format_system_info_array($info);
        exit;
}
require $osc_template->get_file('template_top.php');
if (!isset($_GET['action'])) {
    ?>

<div class="pull-right">
  <?php 
    echo HTML::button(OSCOM::get_def('image_export'), 'fa fa-upload', OSCOM::link('server_info.php', 'action=export'), null, 'btn-info');
    ?>
  <?php 
    echo HTML::button(OSCOM::get_def('button_php_info'), 'fa fa-info-circle', OSCOM::link('server_info.php', 'action=getPhpInfo'), ['newwindow' => true], 'btn-info');
    ?>
</div>

<?php 
}
?>

<h2><i class="fa fa-tasks"></i> <a href="<?php 
echo OSCOM::link('server_info.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<?php 
if ($action == 'export') {
    ?>

<p>
  <?php 
    echo OSCOM::get_def('text_export_intro', ['button_submit_to_oscommerce' => OSCOM::get_def('button_submit_to_oscommerce'), 'button_save' => OSCOM::get_def('image_save')]);
    ?>
</p>

<p>
  <?php 
    echo HTML::textarea_field('server_settings', '100', '15', tep_format_system_info_array($info), 'readonly', false);
    ?>
</p>

<p>
  <?php 
    echo HTML::button(OSCOM::get_def('button_submit_to_oscommerce'), 'fa fa-upload', OSCOM::link('server_info.php', 'action=submit'), null, 'btn-info') . '&nbsp;' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save', OSCOM::link('server_info.php', 'action=save'), null, 'btn-info');
    ?>
</p>

<?php 
} else {
    ?>

<table class="table table-hover">
  <tbody>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_oscom_version');
    ?></strong></td>
      <td><?php 
    echo OSCOM::get_version();
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_http_server');
    ?></strong></td>
      <td><?php 
    echo $info['system']['http_server'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_php_version');
    ?></strong></td>
      <td><?php 
    echo $info['php']['version'] . ' (' . OSCOM::get_def('title_zend_version') . ' ' . $info['php']['zend'] . ')';
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_server_host');
    ?></strong></td>
      <td><?php 
    echo $server['host'] . ' (' . gethostbyname($server['host']) . ')';
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_server_os');
    ?></strong></td>
      <td><?php 
    echo $info['system']['os'] . ' ' . $info['system']['kernel'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_server_date');
    ?></strong></td>
      <td><?php 
    echo $info['system']['date'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_server_up_time');
    ?></strong></td>
      <td><?php 
    echo $info['system']['uptime'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_database_host');
    ?></strong></td>
      <td><?php 
    echo OSCOM::get_config('db_server') . ' (' . gethostbyname(OSCOM::get_config('db_server')) . ')';
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_database');
    ?></strong></td>
      <td><?php 
    echo 'MySQL ' . $info['mysql']['version'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_database_date');
    ?></strong></td>
      <td><?php 
    echo $info['mysql']['date'];
    ?></td>
    </tr>
    <tr>
      <td><strong><?php 
    echo OSCOM::get_def('title_database_name');
    ?></strong></td>
      <td><?php 
    echo OSCOM::get_config('db_database');
    ?></td>
    </tr>
  </tbody>
</table>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';