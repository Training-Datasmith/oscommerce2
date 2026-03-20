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
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'insert':
            $tax_class_title = HTML::sanitize($_POST['tax_class_title']);
            $tax_class_description = HTML::sanitize($_POST['tax_class_description']);
            $OSCOM_Db->save('tax_class', ['tax_class_title' => $tax_class_title, 'tax_class_description' => $tax_class_description, 'date_added' => 'now()']);
            OSCOM::redirect(FILENAME_TAX_CLASSES);
            break;
        case 'save':
            $tax_class_id = HTML::sanitize($_GET['tID']);
            $tax_class_title = HTML::sanitize($_POST['tax_class_title']);
            $tax_class_description = HTML::sanitize($_POST['tax_class_description']);
            $OSCOM_Db->save('tax_class', ['tax_class_title' => $tax_class_title, 'tax_class_description' => $tax_class_description, 'last_modified' => 'now()'], ['tax_class_id' => (int) $tax_class_id]);
            OSCOM::redirect(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tax_class_id);
            break;
        case 'deleteconfirm':
            $tax_class_id = HTML::sanitize($_GET['tID']);
            $OSCOM_Db->delete('tax_class', ['tax_class_id' => (int) $tax_class_id]);
            OSCOM::redirect(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']);
            break;
    }
}
require $osc_template->get_file('template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
echo OSCOM::get_def('table_heading_tax_classes');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_action');
?>&nbsp;</td>
              </tr>
<?php 
$Qclasses = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from :table_tax_class order by tax_class_title limit :page_set_offset, :page_set_max_results');
$Qclasses->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
$Qclasses->execute();
while ($Qclasses->fetch()) {
    if ((!isset($_GET['tID']) || isset($_GET['tID']) && (int) $_GET['tID'] === $Qclasses->value_int('tax_class_id')) && !isset($tc_info) && !str_starts_with($action, 'new')) {
        $tc_info = new Object_Info($Qclasses->to_array());
    }
    if (isset($tc_info) && is_object($tc_info) && $Qclasses->value_int('tax_class_id') === (int) $tc_info->tax_class_id) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id . '&action=edit') . '\'">' . "\n";
    } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $Qclasses->value_int('tax_class_id')) . '\'">' . "\n";
    }
    ?>
                <td class="dataTableContent"><?php 
    echo $Qclasses->value('tax_class_title');
    ?></td>
                <td class="dataTableContent" align="right"><?php 
    if (isset($tc_info) && is_object($tc_info) && $Qclasses->value_int('tax_class_id') === (int) $tc_info->tax_class_id) {
        echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'), '');
    } else {
        echo '<a href="' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $Qclasses->value_int('tax_class_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
    }
    ?>&nbsp;</td>
              </tr>
<?php 
}
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
echo $Qclasses->get_page_set_label(OSCOM::get_def('text_display_number_of_tax_classes'));
?></td>
                    <td class="smallText" align="right"><?php 
echo $Qclasses->get_page_set_links();
?></td>
                  </tr>
<?php 
if (empty($action)) {
    ?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php 
    echo HTML::button(OSCOM::get_def('image_new_tax_class'), 'fa fa-plus', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new'));
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
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_new_tax_class') . '</strong>'];
        $contents = ['form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_insert_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_title') . '<br />' . HTML::input_field('tax_class_title')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_description') . '<br />' . HTML::input_field('tax_class_description')];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']))];
        break;
    case 'edit':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_edit_tax_class') . '</strong>'];
        $contents = ['form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id . '&action=save'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_edit_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_title') . '<br />' . HTML::input_field('tax_class_title', $tc_info->tax_class_title)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_description') . '<br />' . HTML::input_field('tax_class_description', $tc_info->tax_class_description)];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id))];
        break;
    case 'delete':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_tax_class') . '</strong>'];
        $contents = ['form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id . '&action=deleteconfirm'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
        $contents[] = ['text' => '<br /><strong>' . $tc_info->tax_class_title . '</strong>'];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id))];
        break;
    default:
        if (isset($tc_info) && is_object($tc_info)) {
            $heading[] = ['text' => '<strong>' . $tc_info->tax_class_title . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tc_info->tax_class_id . '&action=delete'))];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_added') . ' ' . DateTime::to_short($tc_info->date_added)];
            if (isset($tc_info->last_modified)) {
                $contents[] = ['text' => OSCOM::get_def('text_info_last_modified') . ' ' . DateTime::to_short($tc_info->last_modified)];
            }
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_description') . '<br />' . $tc_info->tax_class_description];
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