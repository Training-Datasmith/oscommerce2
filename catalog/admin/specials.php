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
require 'includes/classes/currencies.php';
$currencies = new currencies();
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'setflag':
            tep_set_specials_status($_GET['id'], $_GET['flag']);
            OSCOM::redirect(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $_GET['id']);
            break;
        case 'insert':
            $products_id = HTML::sanitize($_POST['products_id']);
            $products_price = HTML::sanitize($_POST['products_price']);
            $specials_price = HTML::sanitize($_POST['specials_price']);
            $expdate = HTML::sanitize($_POST['expdate']);
            if (str_ends_with((string) $specials_price, '%')) {
                $Qproduct = $OSCOM_Db->get('products', 'products_price', ['products_id' => (int) $products_id]);
                $products_price = $Qproduct->value('products_price');
                $specials_price = $products_price - $specials_price / 100 * $products_price;
            }
            $expires_date = '';
            if (tep_not_null($expdate)) {
                $expires_date = substr((string) $expdate, 0, 4) . substr((string) $expdate, 5, 2) . substr((string) $expdate, 8, 2);
            }
            $OSCOM_Db->save('specials', ['products_id' => (int) $products_id, 'specials_new_products_price' => $specials_price, 'specials_date_added' => 'now()', 'expires_date' => tep_not_null($expires_date) ? $expires_date : 'null', 'status' => 1]);
            OSCOM::redirect(FILENAME_SPECIALS, 'page=' . $_GET['page']);
            break;
        case 'update':
            $specials_id = HTML::sanitize($_POST['specials_id']);
            $products_price = HTML::sanitize($_POST['products_price']);
            $specials_price = HTML::sanitize($_POST['specials_price']);
            $expdate = HTML::sanitize($_POST['expdate']);
            if (str_ends_with((string) $specials_price, '%')) {
                $specials_price = $products_price - $specials_price / 100 * $products_price;
            }
            $expires_date = '';
            if (tep_not_null($expdate)) {
                $expires_date = substr((string) $expdate, 0, 4) . substr((string) $expdate, 5, 2) . substr((string) $expdate, 8, 2);
            }
            $OSCOM_Db->save('specials', ['specials_new_products_price' => $specials_price, 'specials_last_modified' => 'now()', 'expires_date' => tep_not_null($expires_date) ? $expires_date : 'null', 'status' => 1], ['specials_id' => (int) $specials_id]);
            OSCOM::redirect(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials_id);
            break;
        case 'deleteconfirm':
            $specials_id = HTML::sanitize($_GET['sID']);
            $OSCOM_Db->delete('specials', ['specials_id' => (int) $specials_id]);
            OSCOM::redirect(FILENAME_SPECIALS, 'page=' . $_GET['page']);
            break;
    }
}
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
<?php 
if ($action == 'new' || $action == 'edit') {
    $form_action = 'insert';
    if ($action == 'edit' && isset($_GET['sID'])) {
        $form_action = 'update';
        $Qproduct = $OSCOM_Db->get(['products p', 'products_description pd', 'specials s'], ['p.products_id', 'pd.products_name', 'p.products_price', 's.specials_new_products_price', 's.expires_date'], ['p.products_id' => ['rel' => ['pd.products_id', 's.products_id']], 'pd.language_id' => $OSCOM_Language->get_id(), 's.specials_id' => (int) $_GET['sID']]);
        $s_info = new Object_Info($Qproduct->to_array());
    } else {
        $s_info = new Object_Info([]);
        // create an array of products on special, which will be excluded from the pull down menu of products
        // (when creating a new product on special)
        $specials_array = [];
        $Qspecials = $OSCOM_Db->get(['products p', 'specials s'], ['p.products_id'], ['s.products_id' => ['rel' => 'p.products_id']]);
        while ($Qspecials->fetch()) {
            $specials_array[] = $Qspecials->value_int('products_id');
        }
    }
    ?>
      <tr><form name="new_special" action="<?php 
    echo OSCOM::link(FILENAME_SPECIALS, tep_get_all_get_params(['action', 'info', 'sID']) . 'action=' . $form_action);
    ?>" method="post"><?php 
    if ($form_action == 'update') {
        echo HTML::hidden_field('specials_id', $_GET['sID']);
    }
    ?>
        <td><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('text_specials_product');
    ?>&nbsp;</td>
            <td class="main"><?php 
    echo isset($s_info->products_name) ? $s_info->products_name . ' <small>(' . $currencies->format($s_info->products_price) . ')</small>' : tep_draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array);
    echo HTML::hidden_field('products_price', $s_info->products_price ?? '');
    ?></td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('text_specials_special_price');
    ?>&nbsp;</td>
            <td class="main"><?php 
    echo HTML::input_field('specials_price', $s_info->specials_new_products_price ?? '');
    ?></td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('text_specials_expires_date');
    ?>&nbsp;</td>
            <td class="main"><?php 
    echo HTML::input_field('expdate', isset($s_info->expires_date) && tep_not_null($s_info->expires_date) ? substr($s_info->expires_date, 0, 4) . '-' . substr($s_info->expires_date, 5, 2) . '-' . substr($s_info->expires_date, 8, 2) : '', 'id="expdate"') . ' <small>(YYYY-MM-DD)</small>';
    ?></td>
          </tr>
        </table>

<script type="text/javascript">
$('#expdate').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>

        </td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><br /><?php 
    echo OSCOM::get_def('text_specials_price_tip');
    ?></td>
            <td class="smallText" align="right" valign="top"><br /><?php 
    echo HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '')));
    ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php 
} else {
    ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
    echo OSCOM::get_def('table_heading_products');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_products_price');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_status');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_action');
    ?>&nbsp;</td>
              </tr>
<?php 
    $Qspecials = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS p.products_id, pd.products_name, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status from :table_products p, :table_specials s, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = s.products_id order by pd.products_name limit :page_set_offset, :page_set_max_results');
    $Qspecials->bind_int(':language_id', $OSCOM_Language->get_id());
    $Qspecials->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qspecials->execute();
    while ($Qspecials->fetch()) {
        if ((!isset($_GET['sID']) || isset($_GET['sID']) && (int) $_GET['sID'] === $Qspecials->value_int('specials_id')) && !isset($s_info)) {
            $Qproduct = $OSCOM_Db->get('products', 'products_image', ['products_id' => $Qspecials->value_int('products_id')]);
            $s_info_array = array_merge($Qspecials->to_array(), $Qproduct->to_array());
            $s_info = new Object_Info($s_info_array);
        }
        if (isset($s_info) && is_object($s_info) && $Qspecials->value_int('specials_id') === (int) $s_info->specials_id) {
            echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $s_info->specials_id . '&action=edit') . '\'">' . "\n";
        } else {
            echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $Qspecials->value_int('specials_id')) . '\'">' . "\n";
        }
        ?>
                <td  class="dataTableContent"><?php 
        echo $Qspecials->value('products_name');
        ?></td>
                <td  class="dataTableContent" align="right"><span class="oldPrice"><?php 
        echo $currencies->format($Qspecials->value('products_price'));
        ?></span> <span class="specialPrice"><?php 
        echo $currencies->format($Qspecials->value('specials_new_products_price'));
        ?></span></td>
                <td  class="dataTableContent" align="right">
<?php 
        if ($Qspecials->value_int('status') === 1) {
            echo HTML::image(OSCOM::link_image('icon_status_green.gif'), OSCOM::get_def('image_icon_status_green'), 10, 10) . '&nbsp;&nbsp;<a href="' . OSCOM::link(FILENAME_SPECIALS, 'action=setflag&flag=0&id=' . $Qspecials->value_int('specials_id')) . '">' . HTML::image(OSCOM::link_image('icon_status_red_light.gif'), OSCOM::get_def('image_icon_status_red_light'), 10, 10) . '</a>';
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_SPECIALS, 'action=setflag&flag=1&id=' . $Qspecials->value_int('specials_id')) . '">' . HTML::image(OSCOM::link_image('icon_status_green_light.gif'), OSCOM::get_def('image_icon_status_green_light'), 10, 10) . '</a>&nbsp;&nbsp;' . HTML::image(OSCOM::link_image('icon_status_red.gif'), OSCOM::get_def('image_icon_status_red'), 10, 10);
        }
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (isset($s_info) && is_object($s_info) && $Qspecials->value_int('specials_id') === (int) $s_info->specials_id) {
            echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'), '');
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $Qspecials->value_int('specials_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
        }
        ?>&nbsp;</td>
      </tr>
<?php 
    }
    ?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
    echo $Qspecials->get_page_set_label(OSCOM::get_def('text_display_number_of_specials'));
    ?></td>
                    <td class="smallText" align="right"><?php 
    echo $Qspecials->get_page_set_links();
    ?></td>
                  </tr>
<?php 
    if (empty($action)) {
        ?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php 
        echo HTML::button(OSCOM::get_def('image_new_product'), 'fa fa-plus', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&action=new'));
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
        case 'delete':
            $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_specials') . '</strong>'];
            $contents = ['form' => HTML::form('specials', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $s_info->specials_id . '&action=deleteconfirm'))];
            $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro')];
            $contents[] = ['text' => '<br /><strong>' . $s_info->products_name . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $s_info->specials_id))];
            break;
        default:
            if (is_object($s_info)) {
                $heading[] = ['text' => '<strong>' . $s_info->products_name . '</strong>'];
                $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $s_info->specials_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $s_info->specials_id . '&action=delete'))];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_added') . ' ' . DateTime::to_short($s_info->specials_date_added)];
                if (isset($s_info->specials_last_modified)) {
                    $contents[] = ['text' => '' . OSCOM::get_def('text_info_last_modified') . ' ' . DateTime::to_short($s_info->specials_last_modified)];
                }
                $contents[] = ['align' => 'center', 'text' => '<br />' . tep_info_image($s_info->products_image, $s_info->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_original_price') . ' ' . $currencies->format($s_info->products_price)];
                $contents[] = ['text' => '' . OSCOM::get_def('text_info_new_price') . ' ' . $currencies->format($s_info->specials_new_products_price)];
                $contents[] = ['text' => '' . OSCOM::get_def('text_info_percentage') . ' ' . number_format(100 - $s_info->specials_new_products_price / $s_info->products_price * 100) . '%'];
                if (isset($s_info->expires_date)) {
                    $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_expires_date') . ' <strong>' . DateTime::to_short($s_info->expires_date) . '</strong>'];
                }
                if (isset($s_info->date_status_change)) {
                    $contents[] = ['text' => '' . OSCOM::get_def('text_info_status_change') . ' ' . DateTime::to_short($s_info->date_status_change)];
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
}
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';