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
            $tax_zone_id = HTML::sanitize($_POST['tax_zone_id']);
            $tax_class_id = HTML::sanitize($_POST['tax_class_id']);
            $tax_rate = HTML::sanitize($_POST['tax_rate']);
            $tax_description = HTML::sanitize($_POST['tax_description']);
            $tax_priority = HTML::sanitize($_POST['tax_priority']);
            $OSCOM_Db->save('tax_rates', ['tax_zone_id' => (int) $tax_zone_id, 'tax_class_id' => (int) $tax_class_id, 'tax_rate' => $tax_rate, 'tax_description' => $tax_description, 'tax_priority' => (int) $tax_priority, 'date_added' => 'now()']);
            OSCOM::redirect(FILENAME_TAX_RATES);
            break;
        case 'save':
            $tax_rates_id = HTML::sanitize($_GET['tID']);
            $tax_zone_id = HTML::sanitize($_POST['tax_zone_id']);
            $tax_class_id = HTML::sanitize($_POST['tax_class_id']);
            $tax_rate = HTML::sanitize($_POST['tax_rate']);
            $tax_description = HTML::sanitize($_POST['tax_description']);
            $tax_priority = HTML::sanitize($_POST['tax_priority']);
            $OSCOM_Db->save('tax_rates', ['tax_zone_id' => (int) $tax_zone_id, 'tax_class_id' => (int) $tax_class_id, 'tax_rate' => $tax_rate, 'tax_description' => $tax_description, 'tax_priority' => (int) $tax_priority, 'last_modified' => 'now()'], ['tax_rates_id' => (int) $tax_rates_id]);
            OSCOM::redirect(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tax_rates_id);
            break;
        case 'deleteconfirm':
            $tax_rates_id = HTML::sanitize($_GET['tID']);
            $OSCOM_Db->delete('tax_rates', ['tax_rates_id' => (int) $tax_rates_id]);
            OSCOM::redirect(FILENAME_TAX_RATES, 'page=' . $_GET['page']);
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
echo OSCOM::get_def('table_heading_tax_rate_priority');
?></td>
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_tax_class_title');
?></td>
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_zone');
?></td>
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_tax_rate');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_action');
?>&nbsp;</td>
              </tr>
<?php 
$Qrates = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from :table_tax_class tc, :table_tax_rates r left join :table_geo_zones z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id limit :page_set_offset, :page_set_max_results');
$Qrates->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
$Qrates->execute();
while ($Qrates->fetch()) {
    if ((!isset($_GET['tID']) || isset($_GET['tID']) && (int) $_GET['tID'] === $Qrates->value_int('tax_rates_id')) && !isset($tr_info) && !str_starts_with($action, 'new')) {
        $tr_info = new Object_Info($Qrates->to_array());
    }
    if (isset($tr_info) && is_object($tr_info) && $Qrates->value_int('tax_rates_id') === (int) $tr_info->tax_rates_id) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id . '&action=edit') . '\'">' . "\n";
    } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $Qrates->value_int('tax_rates_id')) . '\'">' . "\n";
    }
    ?>
                <td class="dataTableContent"><?php 
    echo $Qrates->value('tax_priority');
    ?></td>
                <td class="dataTableContent"><?php 
    echo $Qrates->value('tax_class_title');
    ?></td>
                <td class="dataTableContent"><?php 
    echo $Qrates->value('geo_zone_name');
    ?></td>
                <td class="dataTableContent"><?php 
    echo tep_display_tax_value($Qrates->value('tax_rate'));
    ?>%</td>
                <td class="dataTableContent" align="right"><?php 
    if (isset($tr_info) && is_object($tr_info) && $Qrates->value_int('tax_rates_id') === (int) $tr_info->tax_rates_id) {
        echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'), '');
    } else {
        echo '<a href="' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $Qrates->value_int('tax_rates_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
    }
    ?>&nbsp;</td>
              </tr>
<?php 
}
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
echo $Qrates->get_page_set_label(OSCOM::get_def('text_display_number_of_tax_rates'));
?></td>
                    <td class="smallText" align="right"><?php 
echo $Qrates->get_page_set_links();
?></td>
                  </tr>
<?php 
if (empty($action)) {
    ?>
                  <tr>
                    <td class="smallText" colspan="5" align="right"><?php 
    echo HTML::button(OSCOM::get_def('image_new_tax_rate'), 'fa fa-plus', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=new'));
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
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_new_tax_rate') . '</strong>'];
        $contents = ['form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_insert_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_title') . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_zone_name') . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_tax_rate') . '<br />' . HTML::input_field('tax_rate')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_rate_description') . '<br />' . HTML::input_field('tax_description')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_tax_rate_priority') . '<br />' . HTML::input_field('tax_priority')];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page']))];
        break;
    case 'edit':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_edit_tax_rate') . '</strong>'];
        $contents = ['form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id . '&action=save'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_edit_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_class_title') . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $tr_info->tax_class_id)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_zone_name') . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $tr_info->geo_zone_id)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_tax_rate') . '<br />' . HTML::input_field('tax_rate', $tr_info->tax_rate)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_rate_description') . '<br />' . HTML::input_field('tax_description', $tr_info->tax_description)];
        $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_tax_rate_priority') . '<br />' . HTML::input_field('tax_priority', $tr_info->tax_priority)];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id))];
        break;
    case 'delete':
        $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_tax_rate') . '</strong>'];
        $contents = ['form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id . '&action=deleteconfirm'))];
        $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
        $contents[] = ['text' => '<br /><strong>' . $tr_info->tax_class_title . ' ' . number_format($tr_info->tax_rate, TAX_DECIMAL_PLACES) . '%</strong>'];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id))];
        break;
    default:
        if (is_object($tr_info)) {
            $heading[] = ['text' => '<strong>' . $tr_info->tax_class_title . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tr_info->tax_rates_id . '&action=delete'))];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_added') . ' ' . DateTime::to_short($tr_info->date_added)];
            $contents[] = ['text' => '' . OSCOM::get_def('text_info_last_modified') . ' ' . DateTime::to_short($tr_info->last_modified)];
            $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_rate_description') . '<br />' . $tr_info->tax_description];
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