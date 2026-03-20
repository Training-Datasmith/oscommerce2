<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
$directory_array = [];
if ($dir = @dir(OSCOM::get_config('dir_root', 'Shop') . 'includes/modules/action_recorder/')) {
    while ($file = $dir->read()) {
        if (!is_dir(OSCOM::get_config('dir_root', 'Shop') . 'includes/modules/action_recorder/' . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
                $directory_array[] = $file;
            }
        }
    }
    sort($directory_array);
    $dir->close();
}
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    $file = $directory_array[$i];
    if ($OSCOM_Language->definitions_exist('Shop/modules/action_recorder/' . pathinfo($file, PATHINFO_FILENAME))) {
        $OSCOM_Language->load_definitions('Shop/modules/action_recorder/' . pathinfo($file, PATHINFO_FILENAME));
    }
    include OSCOM::get_config('dir_root', 'Shop') . 'includes/modules/action_recorder/' . $file;
    $class = substr($file, 0, strrpos($file, '.'));
    if (class_exists($class)) {
        $GLOBALS[$class] = new $class();
    }
}
$modules_array = [];
$modules_list_array = [['id' => '', 'text' => OSCOM::get_def('text_all_modules')]];
$Qmodules = $OSCOM_Db->get('action_recorder', 'distinct module', null, 'module');
while ($Qmodules->fetch()) {
    $modules_array[] = $Qmodules->value('module');
    $modules_list_array[] = ['id' => $Qmodules->value('module'), 'text' => is_object($GLOBALS[$Qmodules->value('module')]) ? $GLOBALS[$Qmodules->value('module')]->title : $Qmodules->value('module')];
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'expire':
            $expired_entries = 0;
            if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
                if (is_object($GLOBALS[$_GET['module']])) {
                    $expired_entries += $GLOBALS[$_GET['module']]->expire_entries();
                } else {
                    $expired_entries = $OSCOM_Db->delete('action_recorder', ['module' => $_GET['module']]);
                }
            } else {
                foreach ($modules_array as $module) {
                    if (is_object($GLOBALS[$module])) {
                        $expired_entries += $GLOBALS[$module]->expire_entries();
                    }
                }
            }
            $oscom_message_stack->add(OSCOM::get_def('success_expired_entries', ['expired_entries' => $expired_entries]), 'success');
            OSCOM::redirect(FILENAME_ACTION_RECORDER);
            break;
    }
}
require $osc_template->get_file('template_top.php');
?>

<div class="pull-right">
  <?php 
echo HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link('action_recorder.php', 'action=expire' . (isset($_GET['module']) && in_array($_GET['module'], $modules_array) ? '&module=' . $_GET['module'] : '')), null, 'btn-danger');
?>
</div>

<h2><i class="fa fa-tasks"></i> <a href="<?php 
echo OSCOM::link('action_recorder.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<?php 
echo HTML::form('search', OSCOM::link('action_recorder.php'), 'get', 'class="form-inline"', ['session_id' => true]) . HTML::input_field('search', null, 'placeholder="' . OSCOM::get_def('text_filter_search') . '"') . HTML::select_field('module', $modules_list_array, null, 'onchange="this.form.submit();"') . '</form>';
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
echo OSCOM::get_def('table_heading_module');
?></th>
      <th><?php 
echo OSCOM::get_def('table_heading_customer');
?></th>
      <th><?php 
echo OSCOM::get_def('table_heading_identifier');
?></th>
      <th class="text-right"><?php 
echo OSCOM::get_def('table_heading_date_added');
?></th>
    </tr>
  </thead>
  <tbody>

<?php 
$filter = [];
if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
    $filter[] = 'module = :module';
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filter[] = 'identifier like :identifier';
}
$sql_query = 'select SQL_CALC_FOUND_ROWS * from :table_action_recorder';
if (!empty($filter)) {
    $sql_query .= ' where ' . implode(' and ', $filter);
}
$sql_query .= ' order by date_added desc limit :page_set_offset, :page_set_max_results';
$Qactions = $OSCOM_Db->prepare($sql_query);
if (!empty($filter)) {
    if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
        $Qactions->bind_value(':module', $_GET['module']);
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $Qactions->bind_value(':identifier', '%' . $_GET['search'] . '%');
    }
}
$Qactions->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
$Qactions->execute();
while ($Qactions->fetch()) {
    $module = $Qactions->value('module');
    $module_title = $Qactions->value('module');
    if (is_object($GLOBALS[$module])) {
        $module_title = $GLOBALS[$module]->title;
    }
    ?>

    <tr>
      <td><i class="fa <?php 
    echo $Qactions->value_int('success') === 1 ? 'fa-check text-success' : 'fa-times text-danger';
    ?>"></i> <?php 
    echo $module_title;
    ?></td>
      <td><?php 
    echo $Qactions->value_protected('user_name') . ' [' . $Qactions->value_int('user_id') . ']';
    ?></td>
      <td><?php 
    echo tep_not_null($Qactions->value('identifier')) ? '<a href="' . OSCOM::link('action_recorder.php', 'search=' . $Qactions->value('identifier')) . '"><u>' . $Qactions->value_protected('identifier') . '</u></a>' : '(empty)';
    ?></td>
      <td class="text-right"><?php 
    echo DateTime::to_short($Qactions->value('date_added'), true);
    ?></td>
    </tr>

<?php 
}
?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?php 
echo $Qactions->get_page_set_links((isset($_GET['module']) && in_array($_GET['module'], $modules_array) && is_object($GLOBALS[$_GET['module']]) ? 'module=' . $_GET['module'] : null) . '&' . (isset($_GET['search']) && !empty($_GET['search']) ? 'search=' . $_GET['search'] : null));
?></span>
  <?php 
echo $Qactions->get_page_set_label(OSCOM::get_def('text_display_number_of_entries'));
?>
</div>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';