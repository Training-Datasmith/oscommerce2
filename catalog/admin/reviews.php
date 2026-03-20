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
        case 'setflag':
            if ($_GET['flag'] == '0' || $_GET['flag'] == '1') {
                if (isset($_GET['rID'])) {
                    tep_set_review_status($_GET['rID'], $_GET['flag']);
                }
            }
            OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID']);
            break;
        case 'update':
            $reviews_id = HTML::sanitize($_GET['rID']);
            $reviews_rating = HTML::sanitize($_POST['reviews_rating']);
            $reviews_text = HTML::sanitize($_POST['reviews_text']);
            $reviews_status = HTML::sanitize($_POST['reviews_status']);
            $OSCOM_Db->save('reviews', ['reviews_rating' => $reviews_rating, 'reviews_status' => $reviews_status, 'last_modified' => 'now()'], ['reviews_id' => (int) $reviews_id]);
            $OSCOM_Db->save('reviews_description', ['reviews_text' => $reviews_text], ['reviews_id' => (int) $reviews_id]);
            OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews_id);
            break;
        case 'deleteconfirm':
            $reviews_id = HTML::sanitize($_GET['rID']);
            $OSCOM_Db->delete('reviews', ['reviews_id' => (int) $reviews_id]);
            $OSCOM_Db->delete('reviews_description', ['reviews_id' => (int) $reviews_id]);
            OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page']);
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
if ($action == 'edit' || $action == 'preview') {
    $r_id = HTML::sanitize($_GET['rID']);
    $Qreviews = $OSCOM_Db->get(['reviews r', 'reviews_description rd'], ['r.reviews_id', 'r.products_id', 'r.customers_name', 'r.date_added', 'r.last_modified', 'r.reviews_read', 'rd.reviews_text', 'r.reviews_rating', 'r.reviews_status'], ['r.reviews_id' => ['val' => (int) $r_id, 'ref' => 'rd.reviews_id']]);
    $Qproducts = $OSCOM_Db->get(['products p', 'products_description pd'], ['pd.products_name', 'p.products_image'], ['p.products_id' => ['val' => $Qreviews->value_int('products_id'), 'ref' => 'pd.products_id'], 'pd.language_id' => $OSCOM_Language->get_id()]);
    $r_info_array = array_merge($Qreviews->to_array(), $Qproducts->to_array());
    $r_info = new Object_Info($r_info_array);
    if ($action == 'edit') {
        if (!isset($r_info->reviews_status)) {
            $r_info->reviews_status = '1';
        }
        switch ($r_info->reviews_status) {
            case '0':
                $in_status = false;
                $out_status = true;
                break;
            case '1':
            default:
                $in_status = true;
                $out_status = false;
        }
        ?>
      <tr><?php 
        echo HTML::form('review', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=preview'));
        ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php 
        echo OSCOM::get_def('entry_product');
        ?></strong> <?php 
        echo $r_info->products_name;
        ?><br /><strong><?php 
        echo OSCOM::get_def('entry_from');
        ?></strong> <?php 
        echo $r_info->customers_name;
        ?><br /><br /><strong><?php 
        echo OSCOM::get_def('entry_date');
        ?></strong> <?php 
        echo DateTime::to_short($r_info->date_added);
        ?></td>
            <td class="main" align="right" valign="top"><?php 
        echo HTML::image(OSCOM::link_image('Shop/' . $r_info->products_image), $r_info->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"');
        ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php 
        echo OSCOM::get_def('text_info_review_status');
        ?></strong> <?php 
        echo HTML::radio_field('reviews_status', '1', $in_status) . '&nbsp;' . OSCOM::get_def('text_review_published') . '&nbsp;' . HTML::radio_field('reviews_status', '0', $out_status) . '&nbsp;' . OSCOM::get_def('text_review_not_published');
        ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php 
        echo OSCOM::get_def('entry_review');
        ?></strong><br /><br /><?php 
        echo HTML::textarea_field('reviews_text', '60', '15', $r_info->reviews_text);
        ?></td>
          </tr>
          <tr>
            <td class="smallText" align="right"><?php 
        echo OSCOM::get_def('entry_review_text');
        ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main"><strong><?php 
        echo OSCOM::get_def('entry_rating');
        ?></strong>&nbsp;<?php 
        echo OSCOM::get_def('text_bad');
        ?>&nbsp;<?php 
        for ($i = 1; $i <= 5; $i++) {
            echo HTML::radio_field('reviews_rating', $i, $r_info->reviews_rating == $i) . '&nbsp;';
        }
        echo OSCOM::get_def('text_good');
        ?></td>
      </tr>
      <tr>
        <td align="right" class="smallText"><?php 
        echo HTML::button(OSCOM::get_def('image_preview'), 'fa fa-file-o') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID']));
        ?></td>
      </form></tr>
<?php 
    } else {
        if (tep_not_null($_POST)) {
            $r_info->reviews_rating = HTML::sanitize($_POST['reviews_rating']);
            $r_info->reviews_text = HTML::sanitize($_POST['reviews_text']);
            $r_info->reviews_status = HTML::sanitize($_POST['reviews_status']);
        }
        ?>
      <tr><?php 
        if (tep_not_null($_POST)) {
            echo HTML::form('update', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=update'));
        }
        ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php 
        echo OSCOM::get_def('entry_product');
        ?></strong> <?php 
        echo $r_info->products_name;
        ?><br /><strong><?php 
        echo OSCOM::get_def('entry_from');
        ?></strong> <?php 
        echo $r_info->customers_name;
        ?><br /><br /><strong><?php 
        echo OSCOM::get_def('entry_date');
        ?></strong> <?php 
        echo DateTime::to_short($r_info->date_added);
        ?></td>
            <td class="main" align="right" valign="top"><?php 
        echo HTML::image(OSCOM::link_image('Shop/' . $r_info->products_image), $r_info->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"');
        ?></td>
          </tr>
        </table>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top" class="main"><strong><?php 
        echo OSCOM::get_def('entry_review');
        ?></strong><br /><br /><?php 
        echo nl2br((string) HTML::output(tep_break_string($r_info->reviews_text, 15)));
        ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main"><strong><?php 
        echo OSCOM::get_def('entry_rating');
        ?></strong>&nbsp;<?php 
        echo HTML::image(OSCOM::link_image('Shop/stars_' . $r_info->reviews_rating . '.gif'), OSCOM::get_def('text_of_5_stars', ['reviews_rating' => $r_info->reviews_rating]));
        ?>&nbsp;<small>[<?php 
        echo OSCOM::get_def('text_of_5_stars', ['reviews_rating' => $r_info->reviews_rating]);
        ?>]</small></td>
      </tr>
<?php 
        if (tep_not_null($_POST)) {
            echo HTML::hidden_field('reviews_rating', $r_info->reviews_rating);
            echo HTML::hidden_field('reviews_text', $r_info->reviews_text);
            echo HTML::hidden_field('reviews_status', $r_info->reviews_status);
            ?>
      <tr>
        <td align="right" class="smallText"><?php 
            echo HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id));
            ?></td>
      </form></tr>
<?php 
        } else {
            if (isset($_GET['origin'])) {
                $back_url = $_GET['origin'];
                $back_url_params = '';
            } else {
                $back_url = FILENAME_REVIEWS;
                $back_url_params = 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id;
            }
            ?>
      <tr>
        <td align="right" class="smallText"><?php 
            echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link($back_url, $back_url_params));
            ?></td>
      </tr>
<?php 
        }
    }
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
    echo OSCOM::get_def('table_heading_rating');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_date_added');
    ?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
    echo OSCOM::get_def('table_heading_status');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_action');
    ?>&nbsp;</td>
              </tr>
<?php 
    $Qreviews = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS reviews_id, products_id, date_added, last_modified, reviews_rating, reviews_status from :table_reviews order by date_added desc limit :page_set_offset, :page_set_max_results');
    $Qreviews->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qreviews->execute();
    while ($Qreviews->fetch()) {
        if ((!isset($_GET['rID']) || isset($_GET['rID']) && (int) $_GET['rID'] === $Qreviews->value_int('reviews_id')) && !isset($r_info)) {
            $Qextra = $OSCOM_Db->get(['reviews r', 'reviews_description rd'], ['r.reviews_read', 'r.customers_name', 'length(rd.reviews_text) as reviews_text_size'], ['r.reviews_id' => ['val' => $Qreviews->value_int('reviews_id'), 'ref' => 'rd.reviews_id']]);
            $Qproducts = $OSCOM_Db->get(['products p', 'products_description pd'], ['pd.products_name', 'p.products_image'], ['p.products_id' => ['val' => $Qreviews->value_int('products_id'), 'ref' => 'pd.products_id'], 'pd.language_id' => $OSCOM_Language->get_id()]);
            $Qaverage = $OSCOM_Db->get('reviews', ['(avg(reviews_rating) / 5 * 100) as average_rating'], ['products_id' => $Qreviews->value_int('products_id')]);
            $r_info_array = array_merge($Qreviews->to_array(), $Qextra->to_array(), $Qproducts->to_array(), $Qaverage->to_array());
            $r_info = new Object_Info($r_info_array);
        }
        if (isset($r_info) && is_object($r_info) && $Qreviews->value_int('reviews_id') === (int) $r_info->reviews_id) {
            echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id . '&action=preview') . '\'">' . "\n";
        } else {
            echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->value_int('reviews_id')) . '\'">' . "\n";
        }
        ?>
                <td class="dataTableContent"><?php 
        echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->value_int('reviews_id') . '&action=preview') . '">' . HTML::image(OSCOM::link_image('icons/preview.gif'), OSCOM::get_def('icon_preview')) . '</a>&nbsp;' . tep_get_products_name($Qreviews->value_int('products_id'));
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        echo HTML::image(OSCOM::link_image('Shop/stars_' . $Qreviews->value_int('reviews_rating') . '.gif'));
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        echo DateTime::to_short($Qreviews->value('date_added'));
        ?></td>
                <td class="dataTableContent" align="center">
<?php 
        if ($Qreviews->value_int('reviews_status') === 1) {
            echo HTML::image(OSCOM::link_image('icon_status_green.gif'), OSCOM::get_def('image_icon_status_green'), 10, 10) . '&nbsp;&nbsp;<a href="' . OSCOM::link(FILENAME_REVIEWS, 'action=setflag&flag=0&rID=' . $Qreviews->value_int('reviews_id') . '&page=' . $_GET['page']) . '">' . HTML::image(OSCOM::link_image('icon_status_red_light.gif'), OSCOM::get_def('image_icon_status_red_light'), 10, 10) . '</a>';
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'action=setflag&flag=1&rID=' . $Qreviews->value_int('reviews_id') . '&page=' . $_GET['page']) . '">' . HTML::image(OSCOM::link_image('icon_status_green_light.gif'), OSCOM::get_def('image_icon_status_green_light'), 10, 10) . '</a>&nbsp;&nbsp;' . HTML::image(OSCOM::link_image('icon_status_red.gif'), OSCOM::get_def('image_icon_status_red'), 10, 10);
        }
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (is_object($r_info) && $Qreviews->value_int('reviews_id') === (int) $r_info->reviews_id) {
            echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'));
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->value_int('reviews_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
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
    echo $Qreviews->get_page_set_label(OSCOM::get_def('text_display_number_of_reviews'));
    ?></td>
                    <td class="smallText" align="right"><?php 
    echo $Qreviews->get_page_set_links();
    ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php 
    $heading = [];
    $contents = [];
    switch ($action) {
        case 'delete':
            $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_review') . '</strong>'];
            $contents = ['form' => HTML::form('reviews', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id . '&action=deleteconfirm'))];
            $contents[] = ['text' => OSCOM::get_def('text_info_delete_review_intro')];
            $contents[] = ['text' => '<br /><strong>' . $r_info->products_name . '</strong>'];
            $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id))];
            break;
        default:
            if (isset($r_info) && is_object($r_info)) {
                $heading[] = ['text' => '<strong>' . $r_info->products_name . '</strong>'];
                $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $r_info->reviews_id . '&action=delete'))];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_added') . ' ' . DateTime::to_short($r_info->date_added)];
                if (tep_not_null($r_info->last_modified)) {
                    $contents[] = ['text' => OSCOM::get_def('text_info_last_modified') . ' ' . DateTime::to_short($r_info->last_modified)];
                }
                $contents[] = ['text' => '<br />' . tep_info_image($r_info->products_image, $r_info->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_review_author') . ' ' . $r_info->customers_name];
                $contents[] = ['text' => OSCOM::get_def('text_info_review_rating') . ' ' . HTML::image(OSCOM::link_image('Shop/stars_' . $r_info->reviews_rating . '.gif'))];
                $contents[] = ['text' => OSCOM::get_def('text_info_review_read') . ' ' . $r_info->reviews_read];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_review_size') . ' ' . $r_info->reviews_text_size . ' bytes'];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_products_average_rating') . ' ' . number_format($r_info->average_rating, 2) . '%'];
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
<?php 
}
?>
    </table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';