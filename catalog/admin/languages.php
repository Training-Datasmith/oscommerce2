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
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'insert':
            $name = HTML::sanitize($_POST['name']);
            $code = HTML::sanitize(substr((string) $_POST['code'], 0, 2));
            $image = HTML::sanitize($_POST['image']);
            $directory = HTML::sanitize($_POST['directory']);
            $sort_order = (int) HTML::sanitize($_POST['sort_order']);
            $OSCOM_Db->save('languages', ['name' => $name, 'code' => $code, 'image' => $image, 'directory' => $directory, 'sort_order' => $sort_order]);
            $insert_id = $OSCOM_Db->last_insert_id();
            // create additional categories_description records
            $Qcategories = $OSCOM_Db->prepare('select c.categories_id as orig_category_id, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where cd.language_id = :language_id');
            $Qcategories->bind_int(':language_id', $OSCOM_Language->get_id());
            $Qcategories->execute();
            while ($Qcategories->fetch()) {
                $cols = $Qcategories->to_array();
                $cols['categories_id'] = $cols['orig_category_id'];
                $cols['language_id'] = $insert_id;
                unset($cols['orig_category_id']);
                $OSCOM_Db->save('categories_description', $cols);
            }
            // create additional products_description records
            $Qproducts = $OSCOM_Db->prepare('select p.products_id as orig_product_id, pd.* from :table_products p left join :table_products_description pd on p.products_id = pd.products_id where pd.language_id = :language_id');
            $Qproducts->bind_int(':language_id', $OSCOM_Language->get_id());
            $Qproducts->execute();
            while ($Qproducts->fetch()) {
                $cols = $Qproducts->to_array();
                $cols['products_id'] = $cols['orig_product_id'];
                $cols['language_id'] = $insert_id;
                $cols['products_viewed'] = 0;
                unset($cols['orig_product_id']);
                $OSCOM_Db->save('products_description', $cols);
            }
            // create additional products_options records
            $Qoptions = $OSCOM_Db->get('products_options', '*', ['language_id' => $OSCOM_Language->get_id()]);
            while ($Qoptions->fetch()) {
                $cols = $Qoptions->to_array();
                $cols['language_id'] = $insert_id;
                $OSCOM_Db->save('products_options', $cols);
            }
            // create additional products_options_values records
            $Qvalues = $OSCOM_Db->get('products_options_values', '*', ['language_id' => $OSCOM_Language->get_id()]);
            while ($Qvalues->fetch()) {
                $cols = $Qvalues->to_array();
                $cols['language_id'] = $insert_id;
                $OSCOM_Db->save('products_options_values', $cols);
            }
            // create additional manufacturers_info records
            $Qmanufacturers = $OSCOM_Db->prepare('select m.manufacturers_id as orig_manufacturer_id, mi.* from :table_manufacturers m left join :table_manufacturers_info mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = :languages_id');
            $Qmanufacturers->bind_int(':languages_id', $OSCOM_Language->get_id());
            $Qmanufacturers->execute();
            while ($Qmanufacturers->fetch()) {
                $cols = $Qmanufacturers->to_array();
                $cols['manufacturers_id'] = $cols['orig_manufacturer_id'];
                $cols['languages_id'] = $insert_id;
                unset($cols['orig_manufacturer_id']);
                unset($cols['url_clicks']);
                unset($cols['date_last_click']);
                $OSCOM_Db->save('manufacturers_info', $cols);
            }
            // create additional orders_status records
            $Qstatus = $OSCOM_Db->get('orders_status', '*', ['language_id' => $OSCOM_Language->get_id()]);
            while ($Qstatus->fetch()) {
                $cols = $Qstatus->to_array();
                $cols['language_id'] = $insert_id;
                $OSCOM_Db->save('orders_status', $cols);
            }
            if (isset($_POST['default']) && $_POST['default'] == 'on') {
                $OSCOM_Db->save('configuration', ['configuration_value' => $code], ['configuration_key' => 'DEFAULT_LANGUAGE']);
            }
            OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $insert_id);
            break;
        case 'save':
            $l_id = HTML::sanitize($_GET['lID']);
            $name = HTML::sanitize($_POST['name']);
            $code = HTML::sanitize(substr((string) $_POST['code'], 0, 2));
            $image = HTML::sanitize($_POST['image']);
            $directory = HTML::sanitize($_POST['directory']);
            $sort_order = (int) HTML::sanitize($_POST['sort_order']);
            $OSCOM_Db->save('languages', ['name' => $name, 'code' => $code, 'image' => $image, 'directory' => $directory, 'sort_order' => $sort_order], ['languages_id' => (int) $l_id]);
            if (isset($_POST['default']) && $_POST['default'] == 'on') {
                $OSCOM_Db->save('configuration', ['configuration_value' => $code], ['configuration_key' => 'DEFAULT_LANGUAGE']);
            }
            OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']);
            break;
        case 'deleteconfirm':
            $l_id = HTML::sanitize($_GET['lID']);
            $Qlanguage = $OSCOM_Db->get('languages', 'languages_id', ['code' => DEFAULT_LANGUAGE]);
            if ($Qlanguage->value_int('languages_id') === (int) $l_id) {
                $OSCOM_Db->save('configuration', ['configuration_value' => ''], ['configuration_key' => 'DEFAULT_CURRENCY']);
            }
            $OSCOM_Db->delete('categories_description', ['language_id' => $l_id]);
            $OSCOM_Db->delete('products_description', ['language_id' => $l_id]);
            $OSCOM_Db->delete('products_options', ['language_id' => $l_id]);
            $OSCOM_Db->delete('products_options_values', ['language_id' => $l_id]);
            $OSCOM_Db->delete('manufacturers_info', ['languages_id' => $l_id]);
            $OSCOM_Db->delete('orders_status', ['language_id' => $l_id]);
            $OSCOM_Db->delete('languages', ['languages_id' => $l_id]);
            OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page']);
            break;
        case 'delete':
            $l_id = HTML::sanitize($_GET['lID']);
            $Qlanguage = $OSCOM_Db->get('languages', 'code', ['languages_id' => $l_id]);
            $remove_language = true;
            if ($Qlanguage->value('code') == DEFAULT_LANGUAGE) {
                $remove_language = false;
                $oscom_message_stack->add(OSCOM::get_def('error_remove_default_language'), 'error');
            }
            break;
    }
}
$icons = [];
foreach (glob(OSCOM::get_config('dir_root', 'Shop') . 'public/third_party/flag-icon-css/flags/4x3/*.svg') as $file) {
    $code = basename($file, '.svg');
    $icons[] = ['id' => $code, 'text' => $code];
}
$directories = [];
foreach (glob(OSCOM::get_config('dir_root', 'Shop') . 'includes/languages/*', GLOB_ONLYDIR) as $dir) {
    $code = basename($dir);
    $directories[] = ['id' => $code, 'text' => $code];
}
foreach (glob(OSCOM::get_config('dir_root', 'Admin') . 'includes/languages/*', GLOB_ONLYDIR) as $dir) {
    $code = basename($dir);
    if (array_search($code, array_column($directories, 'id')) === false) {
        $directories[] = ['id' => $code, 'text' => $code];
    }
}
uasort($directories, fn($a, $b) => $a['id'] <=> $b['id']);
require $osc_template->get_file('template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php 
echo OSCOM::get_def('heading_title');
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_language_name');
?></td>
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_language_code');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_action');
?>&nbsp;</td>
              </tr>
<?php 
$Qlanguages = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS languages_id, name, code, image, directory, sort_order from :table_languages order by sort_order limit :page_set_offset, :page_set_max_results');
$Qlanguages->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
$Qlanguages->execute();
while ($Qlanguages->fetch()) {
    if ((!isset($_GET['lID']) || isset($_GET['lID']) && (int) $_GET['lID'] === $Qlanguages->value_int('languages_id')) && !isset($l_info) && !str_starts_with($action, 'new')) {
        $l_info = new Object_Info($Qlanguages->to_array());
    }
    if (isset($l_info) && is_object($l_info) && $Qlanguages->value_int('languages_id') === (int) $l_info->languages_id) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $Qlanguages->value_int('languages_id')) . '\'">' . "\n";
    }
    if (DEFAULT_LANGUAGE == $Qlanguages->value('code')) {
        echo '                <td class="dataTableContent"><strong>' . $Qlanguages->value('name') . ' (' . OSCOM::get_def('text_default') . ')</strong></td>' . "\n";
    } else {
        echo '                <td class="dataTableContent">' . $Qlanguages->value('name') . '</td>' . "\n";
    }
    ?>
                <td class="dataTableContent"><?php 
    echo $Qlanguages->value('code');
    ?></td>
                <td class="dataTableContent" align="right"><?php 
    if (isset($l_info) && is_object($l_info) && $Qlanguages->value_int('languages_id') == (int) $l_info->languages_id) {
        echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'));
    } else {
        echo '<a href="' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $Qlanguages->value_int('languages_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
    }
    ?>&nbsp;</td>
              </tr>
<?php 
}
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
echo $Qlanguages->get_page_set_label(OSCOM::get_def('text_display_number_of_languages'));
?></td>
                    <td class="smallText" align="right"><?php 
echo $Qlanguages->get_page_set_links();
?></td>
                  </tr>
<?php 
if (empty($action)) {
    ?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php 
    echo HTML::button(OSCOM::get_def('image_new_language'), 'fa fa-plus', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . (isset($l_info) ? '&lID=' . $l_info->languages_id : '') . '&action=new'));
    ?></td>
                  </tr>
<?php 
}
?>
                </table></td>
              </tr>
            </table></td>
<?php 
$heading = [];
$contents = [];
switch ($action) {
    case 'new':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_new_language') . '</strong>'];
        $contents = ['form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'action=insert'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_insert_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_name') . '<br />' . HTML::input_field('name')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_code') . '<br />' . HTML::input_field('code')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_image') . '<br />' . HTML::select_field('image', $icons)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_directory') . '<br />' . HTML::select_field('directory', $directories)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_sort_order') . '<br />' . HTML::input_field('sort_order')];
        $contents[] = ['text' => '<br />' . HTML::checkbox_field('default') . ' ' . OSCOM::get_def('text_set_default')];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']))];
        break;
    case 'edit':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_edit_language') . '</strong>'];
        $contents = ['form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id . '&action=save'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_edit_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_name') . '<br />' . HTML::input_field('name', $l_info->name)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_code') . '<br />' . HTML::input_field('code', $l_info->code)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_image') . '<br />' . HTML::select_field('image', $icons, $l_info->image)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_directory') . '<br />' . HTML::select_field('directory', $directories, $l_info->directory)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_sort_order') . '<br />' . HTML::input_field('sort_order', $l_info->sort_order)];
        if (DEFAULT_LANGUAGE != $l_info->code) {
            $contents[] = ['text' => '<br />' . HTML::checkbox_field('default') . ' ' . OSCOM::get_def('text_set_default')];
        }
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id))];
        break;
    case 'delete':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_language') . '</strong>'];
        $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
        $contents[] = ['text' => '<br /><strong>' . $l_info->name . '</strong>'];
        $contents[] = ['align' => 'center', 'text' => '<br />' . ($remove_language ? HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id . '&action=deleteconfirm')) : '') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id))];
        break;
    default:
        if (is_object($l_info)) {
            $heading[] = ['text' => '<strong>' . $l_info->name . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $l_info->languages_id . '&action=delete')) . HTML::button(OSCOM::get_def('image_details'), 'fa fa-info', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $l_info->directory))];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_name') . ' ' . $l_info->name];
            $contents[] = ['text' => OSCOM::get_def('text_info_language_code') . ' ' . $l_info->code];
            $contents[] = ['text' => '<br />' . $OSCOM_Language->get_image($l_info->code, 32, 24)];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_directory') . '<br />includes/languages/<strong>' . $l_info->directory . '</strong>'];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_language_sort_order') . ' ' . $l_info->sort_order];
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