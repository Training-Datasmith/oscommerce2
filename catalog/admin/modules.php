<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Apps;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
require 'includes/application_top.php';
$set = $_GET['set'] ?? '';
$modules = $cfg_modules->get_all();
if (empty($set) || !$cfg_modules->exists($set)) {
    $set = $modules[0]['code'];
}
$module_type = $cfg_modules->get($set, 'code');
$module_directory = $cfg_modules->get($set, 'directory');
$module_language_directory = $cfg_modules->get($set, 'language_directory');
$module_site = $cfg_modules->get($set, 'site');
$module_key = $cfg_modules->get($set, 'key');
define('HEADING_TITLE', $cfg_modules->get($set, 'title'));
$template_integration = $cfg_modules->get($set, 'template_integration');
$app_module_type = null;
switch ($module_type) {
    case 'dashboard':
        $app_module_type = 'AdminDashboard';
        break;
    case 'payment':
        $app_module_type = 'Payment';
        break;
    case 'shipping':
        $app_module_type = 'Shipping';
        break;
    case 'order_total':
        $app_module_type = 'OrderTotal';
        break;
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'save':
            foreach ($_POST['configuration'] as $key => $value) {
                $key = HTML::sanitize($key);
                $value = HTML::sanitize($value);
                $OSCOM_Db->save('configuration', ['configuration_value' => $value], ['configuration_key' => $key]);
            }
            OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module']);
            break;
        case 'install':
        case 'remove':
            if (str_contains((string) $_GET['module'], '\\')) {
                $class = Apps::get_module_class($_GET['module'], $app_module_type);
                if (class_exists($class)) {
                    $file_extension = '';
                    $module = new $class();
                    $class = $_GET['module'];
                }
            } else {
                $file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
                $class = basename((string) $_GET['module']);
                if (is_file($module_directory . $class . $file_extension)) {
                    include $module_directory . $class . $file_extension;
                    $module = new $class();
                }
            }
            if (isset($module)) {
                if ($action == 'install') {
                    if ($module->check() > 0) {
                        // remove module if already installed
                        $module->remove();
                    }
                    $module->install();
                    $modules_installed = explode(';', (string) constant($module_key));
                    if (!in_array($class . $file_extension, $modules_installed)) {
                        $modules_installed[] = $class . $file_extension;
                    }
                    Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed)], ['configuration_key' => $module_key]);
                    OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $class);
                } elseif ($action == 'remove') {
                    $module->remove();
                    $modules_installed = explode(';', (string) constant($module_key));
                    if (in_array($class . $file_extension, $modules_installed)) {
                        unset($modules_installed[array_search($class . $file_extension, $modules_installed)]);
                    }
                    Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed)], ['configuration_key' => $module_key]);
                    OSCOM::redirect(FILENAME_MODULES, 'set=' . $set);
                }
            }
            OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $class);
            break;
    }
}
require $osc_template->get_file('template_top.php');
$modules_installed = defined($module_key) ? explode(';', (string) constant($module_key)) : [];
$new_modules_counter = 0;
$file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
$directory_array = [];
if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
        if (!is_dir($module_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
                if (isset($_GET['list']) && $_GET['list'] == 'new') {
                    if (!in_array($file, $modules_installed)) {
                        $directory_array[] = $file;
                    }
                } else if (in_array($file, $modules_installed)) {
                    $directory_array[] = $file;
                } else {
                    $new_modules_counter++;
                }
            }
        }
    }
    $dir->close();
}
if (isset($app_module_type)) {
    foreach (Apps::get_modules($app_module_type) as $k => $v) {
        if (isset($_GET['list']) && $_GET['list'] == 'new') {
            if (!in_array($k, $modules_installed)) {
                $directory_array[] = $k;
            }
        } else if (in_array($k, $modules_installed)) {
            $directory_array[] = $k;
        } else {
            $new_modules_counter++;
        }
    }
}
sort($directory_array);
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php 
echo HEADING_TITLE;
?></td>
<?php 
if (isset($_GET['list'])) {
    echo '            <td class="smallText" align="right">' . HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_MODULES, 'set=' . $set)) . '</td>';
} else {
    echo '            <td class="smallText" align="right">' . HTML::button(OSCOM::get_def('image_module_install') . ' (' . $new_modules_counter . ')', 'fa fa-plus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&list=new')) . '</td>';
}
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_modules');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_sort_order');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_action');
?>&nbsp;</td>
              </tr>
<?php 
$installed_modules = [];
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    $file = $directory_array[$i];
    if (str_contains((string) $file, '\\')) {
        $file_extension = '';
        $class = Apps::get_module_class($file, $app_module_type);
        $module = new $class();
        $module->code = $file;
        $class = $file;
    } else {
        $file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
        $OSCOM_Language->load_definitions($module_site . '/modules/' . $module_type . '/' . pathinfo((string) $file, PATHINFO_FILENAME));
        include $module_directory . $file;
        $class = substr((string) $file, 0, strrpos((string) $file, '.'));
        if (class_exists($class)) {
            $module = new $class();
        }
    }
    if (isset($module)) {
        if ($module->check() > 0) {
            if ($module->sort_order > 0 && !isset($installed_modules[$module->sort_order])) {
                $installed_modules[$module->sort_order] = $file;
            } else {
                $installed_modules[] = $file;
            }
        }
        if ((!isset($_GET['module']) || isset($_GET['module']) && $_GET['module'] == $class) && !isset($m_info)) {
            $module_info = ['code' => $module->code, 'title' => $module->title, 'description' => $module->description, 'status' => $module->check(), 'signature' => $module->signature ?? null, 'api_version' => $module->api_version ?? null];
            $module_keys = $module->keys();
            $keys_extra = [];
            for ($j = 0, $k = sizeof($module_keys); $j < $k; $j++) {
                $Qkeys = $OSCOM_Db->get('configuration', ['configuration_title', 'configuration_value', 'configuration_description', 'use_function', 'set_function'], ['configuration_key' => $module_keys[$j]]);
                $keys_extra[$module_keys[$j]]['title'] = $Qkeys->value('configuration_title');
                $keys_extra[$module_keys[$j]]['value'] = $Qkeys->value('configuration_value');
                $keys_extra[$module_keys[$j]]['description'] = $Qkeys->value('configuration_description');
                $keys_extra[$module_keys[$j]]['use_function'] = $Qkeys->value('use_function');
                $keys_extra[$module_keys[$j]]['set_function'] = $Qkeys->value('set_function');
            }
            $module_info['keys'] = $keys_extra;
            $m_info = new \ArrayObject($module_info, \ArrayObject::ARRAY_AS_PROPS);
        }
        if (isset($m_info) && is_object($m_info) && $class == $m_info->code) {
            if ($module->check() > 0) {
                echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit') . '\'">' . "\n";
            } else {
                echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
            }
        } else {
            echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['list']) ? '&list=new' : '') . '&module=' . $class) . '\'">' . "\n";
        }
        ?>
                <td class="dataTableContent"><?php 
        echo $module->title;
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (in_array($module->code . $file_extension, $modules_installed) && is_numeric($module->sort_order)) {
            echo $module->sort_order;
        }
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (isset($m_info) && is_object($m_info) && $class == $m_info->code) {
            echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'));
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['list']) ? '&list=new' : '') . '&module=' . $class) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
        }
        ?>&nbsp;</td>
              </tr>
<?php 
    }
}
if (!isset($_GET['list'])) {
    ksort($installed_modules);
    $Qcheck = $OSCOM_Db->get('configuration', 'configuration_value', ['configuration_key' => $module_key]);
    if ($Qcheck->fetch() !== false) {
        if ($Qcheck->value('configuration_value') != implode(';', $installed_modules)) {
            $OSCOM_Db->save('configuration', ['configuration_value' => implode(';', $installed_modules), 'last_modified' => 'now()'], ['configuration_key' => $module_key]);
        }
    } else {
        $OSCOM_Db->save('configuration', ['configuration_title' => 'Installed Modules', 'configuration_key' => $module_key, 'configuration_value' => implode(';', $installed_modules), 'configuration_description' => 'This is automatically updated. No need to edit.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
    }
    if ($template_integration == true) {
        $Qcheck = $OSCOM_Db->get('configuration', 'configuration_value', ['configuration_key' => 'TEMPLATE_BLOCK_GROUPS']);
        if ($Qcheck->fetch() !== false) {
            $tbgroups_array = explode(';', (string) $Qcheck->value('configuration_value'));
            if (!in_array($module_type, $tbgroups_array)) {
                $tbgroups_array[] = $module_type;
                sort($tbgroups_array);
                $OSCOM_Db->save('configuration', ['configuration_value' => implode(';', $tbgroups_array), 'last_modified' => 'now()'], ['configuration_key' => 'TEMPLATE_BLOCK_GROUPS']);
            }
        } else {
            $OSCOM_Db->save('configuration', ['configuration_title' => 'Installed Template Block Groups', 'configuration_key' => 'TEMPLATE_BLOCK_GROUPS', 'configuration_value' => $module_type, 'configuration_description' => 'This is automatically updated. No need to edit.', 'configuration_group_id' => '6', 'sort_order' => '0', 'date_added' => 'now()']);
        }
    }
}
?>
              <tr>
                <td colspan="3" class="smallText"><?php 
echo OSCOM::get_def('text_module_directory') . ' ' . $module_directory;
?></td>
              </tr>
            </table></td>
<?php 
$heading = [];
$contents = [];
if (isset($m_info) && str_contains((string) $m_info->code, '\\')) {
    $file_extension = '';
} else {
    $file_extension = substr((string) $PHP_SELF, strrpos((string) $PHP_SELF, '.'));
}
switch ($action) {
    case 'edit':
        $keys = '';
        foreach ($m_info->keys as $key => $value) {
            $keys .= '<strong>' . $value['title'] . '</strong><br />' . $value['description'] . '<br />';
            if ($value['set_function']) {
                eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
            } else {
                $keys .= HTML::input_field('configuration[' . $key . ']', $value['value']);
            }
            $keys .= '<br /><br />';
        }
        $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
        $heading[] = ['text' => '<strong>' . $m_info->title . '</strong>'];
        $contents = ['form' => HTML::form('modules', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module'] . '&action=save'))];
        $contents[] = ['text' => $keys];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module']))];
        break;
    default:
        $heading[] = ['text' => '<strong>' . $m_info->title . '</strong>'];
        if (in_array($m_info->code . $file_extension, $modules_installed) && $m_info->status > 0) {
            $keys = '';
            foreach ($m_info->keys as $value) {
                $keys .= '<strong>' . $value['title'] . '</strong><br />';
                if ($value['use_function']) {
                    $use_function = $value['use_function'];
                    if (preg_match('/->/', (string) $use_function)) {
                        $class_method = explode('->', (string) $use_function);
                        if (!isset(${$class_method[0]}) || !is_object(${$class_method[0]})) {
                            include 'includes/classes/' . $class_method[0] . '.php';
                            ${$class_method[0]} = new $class_method[0]();
                        }
                        $keys .= tep_call_function($class_method[1], $value['value'], ${$class_method[0]});
                    } else {
                        $keys .= tep_call_function($use_function, $value['value']);
                    }
                } else {
                    $keys .= $value['value'];
                }
                $keys .= '<br /><br />';
            }
            $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
            $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $m_info->code . '&action=edit')) . HTML::button(OSCOM::get_def('image_module_remove'), 'fa fa-minus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $m_info->code . '&action=remove'))];
            if (isset($m_info->signature) && [$scode, $smodule, $sversion, $soscversion] = explode('|', $m_info->signature)) {
                $contents[] = ['text' => '<br />' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '&nbsp;<strong>' . OSCOM::get_def('text_info_version') . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $m_info->signature . '" target="_blank">' . OSCOM::get_def('text_info_online_status') . '</a>)'];
            }
            if (isset($m_info->api_version)) {
                $contents[] = ['text' => HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '&nbsp;<strong>' . OSCOM::get_def('text_info_api_version') . '</strong> ' . $m_info->api_version];
            }
            $contents[] = ['text' => '<br />' . $m_info->description];
            $contents[] = ['text' => '<br />' . $keys];
        } elseif (isset($_GET['list']) && $_GET['list'] == 'new') {
            if (isset($m_info)) {
                $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_module_install'), 'fa fa-plus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $m_info->code . '&action=install'))];
                if (isset($m_info->signature) && [$scode, $smodule, $sversion, $soscversion] = explode('|', $m_info->signature)) {
                    $contents[] = ['text' => '<br />' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '&nbsp;<strong>' . OSCOM::get_def('text_info_version') . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $m_info->signature . '" target="_blank">' . OSCOM::get_def('text_info_online_status') . '</a>)'];
                }
                if (isset($m_info->api_version)) {
                    $contents[] = ['text' => HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '&nbsp;<strong>' . OSCOM::get_def('text_info_api_version') . '</strong> ' . $m_info->api_version];
                }
                $contents[] = ['text' => '<br />' . $m_info->description];
            }
        }
        break;
}
if (tep_not_null($heading) && tep_not_null($contents)) {
    echo '            <td width="25%" valign="top">' . "\n";
    $box = new box();
    echo $box->info_box($heading, $contents);
    echo '            </td>' . "\n";
}
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';