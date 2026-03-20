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
function tep_sort_secmodules(array $a, array $b): int
{
    return strcasecmp((string) $a['title'], (string) $b['title']);
}
$types = ['info', 'warning', 'error'];
$modules = [];
if ($secdir = @dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/')) {
    while ($file = $secdir->read()) {
        if (!is_dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/' . $file)) {
            if (substr($file, strrpos($file, '.')) == '.php') {
                $class = 'securityCheck_' . substr($file, 0, strrpos($file, '.'));
                include OSCOM::get_config('dir_root') . 'includes/modules/security_check/' . $file;
                ${$class} = new $class();
                $modules[] = ['title' => ${$class}->title ?? substr($file, 0, strrpos($file, '.')), 'class' => $class, 'code' => substr($file, 0, strrpos($file, '.'))];
            }
        }
    }
    $secdir->close();
}
if ($extdir = @dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/extended/')) {
    while ($file = $extdir->read()) {
        if (!is_dir(OSCOM::get_config('dir_root') . 'includes/modules/security_check/extended/' . $file)) {
            if (substr($file, strrpos($file, '.')) == '.php') {
                $class = 'securityCheckExtended_' . substr($file, 0, strrpos($file, '.'));
                include OSCOM::get_config('dir_root') . 'includes/modules/security_check/extended/' . $file;
                ${$class} = new $class();
                $modules[] = ['title' => ${$class}->title ?? substr($file, 0, strrpos($file, '.')), 'class' => $class, 'code' => substr($file, 0, strrpos($file, '.'))];
            }
        }
    }
    $extdir->close();
}
usort($modules, tep_sort_secmodules(...));
require $osc_template->get_file('template_top.php');
?>

<div style="float: right;"><?php 
echo HTML::button('Reload', 'fa fa-refresh', OSCOM::link('security_checks.php'));
?></div>

<h1 class="pageHeading"><?php 
echo OSCOM::get_def('heading_title');
?></h1>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
    <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_title');
?></td>
    <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_module');
?></td>
    <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_info');
?></td>
    <td class="dataTableHeadingContent" width="20" align="right">&nbsp;</td>
  </tr>

<?php 
foreach ($modules as $module) {
    $sec_check = $GLOBALS[$module['class']];
    if (!in_array($sec_check->type, $types)) {
        $sec_check->type = 'info';
    }
    $output = '';
    if ($sec_check->pass()) {
        $sec_check->type = 'success';
    } else {
        $output = $sec_check->get_message();
    }
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n" . '    <td class="dataTableContent" align="center" valign="top">' . HTML::image(OSCOM::link_image('ms_' . $sec_check->type . '.png'), '', 16, 16) . '</td>' . "\n" . '    <td class="dataTableContent" valign="top" style="white-space: nowrap;">' . HTML::output_protected($module['title']) . '</td>' . "\n" . '    <td class="dataTableContent" valign="top">' . HTML::output_protected($module['code']) . '</td>' . "\n" . '    <td class="dataTableContent" valign="top">' . $output . '</td>' . "\n" . '    <td class="dataTableContent" align="center" valign="top">' . (isset($sec_check->has_doc) && $sec_check->has_doc ? '<a href="http://library.oscommerce.com/Wiki&oscom_2_3&security_checks&' . $module['code'] . '" target="_blank">' . HTML::image(OSCOM::link_image('icons/preview.gif')) . '</a>' : '') . '</td>' . "\n" . '  </tr>' . "\n";
}
?>

</table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';