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
$OSCOM_Db->exec('update :table_products set products_date_available = "" where to_days(now()) > to_days(products_date_available)');
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
echo OSCOM::get_def('table_heading_products');
?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
echo OSCOM::get_def('table_heading_date_expected');
?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
echo OSCOM::get_def('table_heading_action');
?>&nbsp;</td>
              </tr>
<?php 
$Qproducts = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS pd.products_id, pd.products_name, p.products_date_available from :table_products_description pd, :table_products p where p.products_id = pd.products_id and p.products_date_available != "" and pd.language_id = :language_id order by p.products_date_available desc limit :page_set_offset, :page_set_max_results');
$Qproducts->bind_int(':language_id', $OSCOM_Language->get_id());
$Qproducts->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
$Qproducts->execute();
while ($Qproducts->fetch()) {
    if ((!isset($_GET['pID']) || isset($_GET['pID']) && (int) $_GET['pID'] === $Qproducts->value_int('products_id')) && !isset($p_info)) {
        $p_info = new Object_Info($Qproducts->to_array());
    }
    if (isset($p_info) && is_object($p_info) && $Qproducts->value_int('products_id') === (int) $p_info->products_id) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CATEGORIES, 'pID=' . $Qproducts->value_int('products_id') . '&action=new_product') . '\'">' . "\n";
    } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $Qproducts->value_int('products_id')) . '\'">' . "\n";
    }
    ?>
                <td class="dataTableContent"><?php 
    echo $Qproducts->value('products_name');
    ?></td>
                <td class="dataTableContent" align="center"><?php 
    echo DateTime::to_short($Qproducts->value('products_date_available'));
    ?></td>
                <td class="dataTableContent" align="right"><?php 
    if (isset($p_info) && is_object($p_info) && $Qproducts->value_int('products_id') === (int) $p_info->products_id) {
        echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'));
    } else {
        echo '<a href="' . OSCOM::link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $Qproducts->value_int('products_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
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
echo $Qproducts->get_page_set_label(OSCOM::get_def('text_display_number_of_products_expected'));
?></td>
                    <td class="smallText" align="right"><?php 
echo $Qproducts->get_page_set_links();
?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php 
$heading = [];
$contents = [];
if (isset($p_info) && is_object($p_info)) {
    $heading[] = ['text' => '<strong>' . $p_info->products_name . '</strong>'];
    $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_CATEGORIES, 'pID=' . $p_info->products_id . '&action=new_product'))];
    $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_expected') . ' ' . DateTime::to_short($p_info->products_date_available)];
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