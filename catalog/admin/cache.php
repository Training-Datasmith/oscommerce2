<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Cache;
use OSC\OM\File_System;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'reset':
            Cache::clear($_GET['block']);
            break;
        case 'resetAll':
            Cache::clear_all();
            break;
    }
    OSCOM::redirect(FILENAME_CACHE);
}
// check if the cache directory exists
if (is_dir(Cache::get_path())) {
    if (!File_System::is_writable(Cache::get_path())) {
        $oscom_message_stack->add(OSCOM::get_def('error_cache_directory_not_writeable'), 'error');
    }
} else {
    $oscom_message_stack->add(OSCOM::get_def('error_cache_directory_does_not_exist'), 'error');
}
$cache_files = [];
foreach (glob(Cache::get_path() . '*.cache') as $c) {
    $key = basename($c, '.cache');
    if (($pos = strpos($key, '-')) !== false) {
        $cache_files[substr($key, 0, $pos)][] = $key;
    } else {
        $cache_files[$key][] = $key;
    }
}
require $osc_template->get_file('template_top.php');
?>

<div class="pull-right">
  <?php 
echo HTML::button(OSCOM::get_def('image_delete'), 'fa fa-recycle', OSCOM::link('cache.php', 'action=resetAll'), null, 'btn-danger');
?>
</div>

<h2><i class="fa fa-database"></i> <a href="<?php 
echo OSCOM::link('cache.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
echo OSCOM::get_def('table_heading_cache');
?></th>
      <th class="text-right"><?php 
echo OSCOM::get_def('table_heading_cache_number_of_files');
?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
foreach (array_keys($cache_files) as $key) {
    ?>

    <tr>
      <td><?php 
    echo $key;
    ?></td>
      <td class="text-right"><?php 
    echo count($cache_files[$key]);
    ?></td>
      <td class="action"><a href="<?php 
    echo OSCOM::link(FILENAME_CACHE, 'action=reset&block=' . $key);
    ?>"><i class="fa fa-recycle" title="<?php 
    echo OSCOM::get_def('image_delete');
    ?>"></i></a></td>
    </tr>

<?php 
}
?>

  </tbody>
</table>

<p>
  <?php 
echo '<strong>' . OSCOM::get_def('text_cache_directory') . '</strong> ' . File_System::display_path(Cache::get_path());
?>
</p>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';