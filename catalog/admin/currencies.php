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

require('includes/application_top.php');

require('includes/classes/currencies.php');
$currencies = new currencies();

if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}

$action = ($_GET['action'] ?? '');

if (tep_not_null($action)) {
    switch ($action) {
        case 'insert':
        case 'save':
            if (isset($_GET['cID'])) {
                $currency_id = HTML::sanitize($_GET['cID']);
            }
            $title = HTML::sanitize($_POST['title']);
            $code = HTML::sanitize($_POST['code']);
            $symbol_left = HTML::sanitize($_POST['symbol_left']);
            $symbol_right = HTML::sanitize($_POST['symbol_right']);
            $decimal_point = HTML::sanitize($_POST['decimal_point']);
            $thousands_point = HTML::sanitize($_POST['thousands_point']);
            $decimal_places = HTML::sanitize($_POST['decimal_places']);
            $value = HTML::sanitize($_POST['value']);

            $sql_data_array = ['title' => $title,
                                    'code' => $code,
                                    'symbol_left' => $symbol_left,
                                    'symbol_right' => $symbol_right,
                                    'decimal_point' => $decimal_point,
                                    'thousands_point' => $thousands_point,
                                    'decimal_places' => $decimal_places,
                                    'value' => $value];

            if ($action == 'insert') {
                $OSCOM_Db->save('currencies', $sql_data_array);
                $currency_id = $OSCOM_Db->lastInsertId();
            } elseif ($action == 'save') {
                $OSCOM_Db->save('currencies', $sql_data_array, ['currencies_id' => (int)$currency_id]);
            }

            if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
                $OSCOM_Db->save('configuration', [
                  'configuration_value' => $code,
                ], [
                  'configuration_key' => 'DEFAULT_CURRENCY',
                ]);
            }

            OSCOM::redirect(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency_id);
            break;
        case 'deleteconfirm':
            $currencies_id = HTML::sanitize($_GET['cID']);

            $Qcurrency = $OSCOM_Db->get('currencies', 'currencies_id', ['code' => DEFAULT_CURRENCY]);

            if ($Qcurrency->valueInt('currencies_id') === (int)$currencies_id) {
                $OSCOM_Db->save('configuration', ['configuration_value' => ''], ['configuration_key' => 'DEFAULT_CURRENCY']);
            }

            $OSCOM_Db->delete('currencies', ['currencies_id' => (int)$currencies_id]);

            OSCOM::redirect(FILENAME_CURRENCIES, 'page=' . $_GET['page']);
            break;
        case 'delete':
            $currencies_id = HTML::sanitize($_GET['cID']);

            $Qcurrency = $OSCOM_Db->get('currencies', 'code', ['currencies_id' => (int)$currencies_id]);

            $remove_currency = true;
            if ($Qcurrency->value('code') == DEFAULT_CURRENCY) {
                $remove_currency = false;
                $OSCOM_MessageStack->add(OSCOM::getDef('error_remove_default_currency'), 'error');
            }
            break;
    }
}

$currency_select = ['USD' => ['title' => 'U.S. Dollar', 'code' => 'USD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'EUR' => ['title' => 'Euro', 'code' => 'EUR', 'symbol_left' => '', 'symbol_right' => '€', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'JPY' => ['title' => 'Japanese Yen', 'code' => 'JPY', 'symbol_left' => '¥', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'GBP' => ['title' => 'Pounds Sterling', 'code' => 'GBP', 'symbol_left' => '£', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'CHF' => ['title' => 'Swiss Franc', 'code' => 'CHF', 'symbol_left' => '', 'symbol_right' => 'CHF', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'AUD' => ['title' => 'Australian Dollar', 'code' => 'AUD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'CAD' => ['title' => 'Canadian Dollar', 'code' => 'CAD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'SEK' => ['title' => 'Swedish Krona', 'code' => 'SEK', 'symbol_left' => '', 'symbol_right' => 'kr', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'HKD' => ['title' => 'Hong Kong Dollar', 'code' => 'HKD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'NOK' => ['title' => 'Norwegian Krone', 'code' => 'NOK', 'symbol_left' => 'kr', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'NZD' => ['title' => 'New Zealand Dollar', 'code' => 'NZD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'MXN' => ['title' => 'Mexican Peso', 'code' => 'MXN', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'SGD' => ['title' => 'Singapore Dollar', 'code' => 'SGD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'BRL' => ['title' => 'Brazilian Real', 'code' => 'BRL', 'symbol_left' => 'R$', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'CNY' => ['title' => 'Chinese RMB', 'code' => 'CNY', 'symbol_left' => '￥', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'CZK' => ['title' => 'Czech Koruna', 'code' => 'CZK', 'symbol_left' => '', 'symbol_right' => 'Kč', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'DKK' => ['title' => 'Danish Krone', 'code' => 'DKK', 'symbol_left' => '', 'symbol_right' => 'kr', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'HUF' => ['title' => 'Hungarian Forint', 'code' => 'HUF', 'symbol_left' => '', 'symbol_right' => 'Ft', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'ILS' => ['title' => 'Israeli New Shekel', 'code' => 'ILS', 'symbol_left' => '₪', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'INR' => ['title' => 'Indian Rupee', 'code' => 'INR', 'symbol_left' => 'Rs.', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'MYR' => ['title' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol_left' => 'RM', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'PHP' => ['title' => 'Philippine Peso', 'code' => 'PHP', 'symbol_left' => 'Php', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'PLN' => ['title' => 'Polish Zloty', 'code' => 'PLN', 'symbol_left' => '', 'symbol_right' => 'zł', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'],
                         'THB' => ['title' => 'Thai Baht', 'code' => 'THB', 'symbol_left' => '', 'symbol_right' => '฿', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'],
                         'TWD' => ['title' => 'Taiwan New Dollar', 'code' => 'TWD', 'symbol_left' => 'NT$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2']];

$currency_select_array = [['id' => '', 'text' => OSCOM::getDef('text_info_common_currencies')]];
foreach ($currency_select as $cs) {
    if (!isset($currencies->currencies[$cs['code']])) {
        $currency_select_array[] = ['id' => $cs['code'], 'text' => '[' . $cs['code'] . '] ' . $cs['title']];
    }
}

require($oscTemplate->getFile('template_top.php'));
?>

<script type="text/javascript">
var currency_select = new Array();
<?php
  foreach ($currency_select_array as $cs) {
      if (!empty($cs['id'])) {
          echo 'currency_select["' . $cs['id'] . '"] = new Array("' . $currency_select[$cs['id']]['title'] . '", "' . $currency_select[$cs['id']]['symbol_left'] . '", "' . $currency_select[$cs['id']]['symbol_right'] . '", "' . $currency_select[$cs['id']]['decimal_point'] . '", "' . $currency_select[$cs['id']]['thousands_point'] . '", "' . $currency_select[$cs['id']]['decimal_places'] . '");' . "\n";
      }
  }
?>

function updateForm() {
  var cs = document.forms["currencies"].cs[document.forms["currencies"].cs.selectedIndex].value;

  document.forms["currencies"].title.value = currency_select[cs][0];
  document.forms["currencies"].code.value = cs;
  document.forms["currencies"].symbol_left.value = currency_select[cs][1];
  document.forms["currencies"].symbol_right.value = currency_select[cs][2];
  document.forms["currencies"].decimal_point.value = currency_select[cs][3];
  document.forms["currencies"].thousands_point.value = currency_select[cs][4];
  document.forms["currencies"].decimal_places.value = currency_select[cs][5];
  document.forms["currencies"].value.value = 1;
}
</script>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_currency_name'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_currency_codes'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_currency_value'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qcurrencies = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value from :table_currencies order by title limit :page_set_offset, :page_set_max_results');
$Qcurrencies->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
$Qcurrencies->execute();

while ($Qcurrencies->fetch()) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ((int)$_GET['cID'] === $Qcurrencies->valueInt('currencies_id')))) && !isset($cInfo) && (!str_starts_with($action, 'new'))) {
        $cInfo = new objectInfo($Qcurrencies->toArray());
    }

    if (isset($cInfo) && is_object($cInfo) && ($Qcurrencies->valueInt('currencies_id') === (int)$cInfo->currencies_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'">' . "\n";
    } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $Qcurrencies->valueInt('currencies_id')) . '\'">' . "\n";
    }

    if (DEFAULT_CURRENCY == $Qcurrencies->value('code')) {
        echo '                <td class="dataTableContent"><strong>' . $Qcurrencies->value('title') . ' (' . OSCOM::getDef('text_default') . ')</strong></td>' . "\n";
    } else {
        echo '                <td class="dataTableContent">' . $Qcurrencies->value('title') . '</td>' . "\n";
    }
    ?>
                <td class="dataTableContent"><?php echo $Qcurrencies->value('code'); ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($Qcurrencies->value('value'), 8); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($Qcurrencies->valueInt('currencies_id') === $cInfo->currencies_id)) {
                    echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'));
                } else {
                    echo '<a href="' . OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $Qcurrencies->valueInt('currencies_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>';
                } ?>&nbsp;</td>
              </tr>
<?php
}
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qcurrencies->getPageSetLabel(OSCOM::getDef('text_display_number_of_currencies')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qcurrencies->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
      ?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(OSCOM::getDef('image_new_currency'), 'fa fa-plus', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=new')); ?></td>
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
        $heading[] = ['text' => '<strong>' . OSCOM::getDef('text_info_heading_new_currency') . '</strong>'];

        $contents = ['form' => HTML::form('currencies', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . (isset($cInfo) ? '&cID=' . $cInfo->currencies_id : '') . '&action=insert'))];
        $contents[] = ['text' => OSCOM::getDef('text_info_insert_intro')];
        $contents[] = ['text' => '<br />' . HTML::selectField('cs', $currency_select_array, '', 'onchange="updateForm();"')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_title') . '<br />' . HTML::inputField('title')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_code') . '<br />' . HTML::inputField('code')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_symbol_left') . '<br />' . HTML::inputField('symbol_left')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_symbol_right') . '<br />' . HTML::inputField('symbol_right')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_decimal_point') . '<br />' . HTML::inputField('decimal_point')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_thousands_point') . '<br />' . HTML::inputField('thousands_point')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_decimal_places') . '<br />' . HTML::inputField('decimal_places')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_value') . '<br />' . HTML::inputField('value')];
        $contents[] = ['text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default')];
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']))];
        break;
    case 'edit':
        $heading[] = ['text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_currency') . '</strong>'];

        $contents = ['form' => HTML::form('currencies', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=save'))];
        $contents[] = ['text' => OSCOM::getDef('text_info_edit_intro')];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_title') . '<br />' . HTML::inputField('title', $cInfo->title)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_code') . '<br />' . HTML::inputField('code', $cInfo->code)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_symbol_left') . '<br />' . HTML::inputField('symbol_left', $cInfo->symbol_left)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_symbol_right') . '<br />' . HTML::inputField('symbol_right', $cInfo->symbol_right)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_decimal_point') . '<br />' . HTML::inputField('decimal_point', $cInfo->decimal_point)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_thousands_point') . '<br />' . HTML::inputField('thousands_point', $cInfo->thousands_point)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_decimal_places') . '<br />' . HTML::inputField('decimal_places', $cInfo->decimal_places)];
        $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_value') . '<br />' . HTML::inputField('value', $cInfo->value)];
        if (DEFAULT_CURRENCY != $cInfo->code) {
            $contents[] = ['text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default')];
        }
        $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id))];
        break;
    case 'delete':
        $heading[] = ['text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_currency') . '</strong>'];

        $contents[] = ['text' => OSCOM::getDef('text_info_delete_intro')];
        $contents[] = ['text' => '<br /><strong>' . $cInfo->title . '</strong>'];
        $contents[] = ['align' => 'center', 'text' => '<br />' . (($remove_currency) ? HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=deleteconfirm')) : '') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id))];
        break;
    default:
        if (is_object($cInfo)) {
            $heading[] = ['text' => '<strong>' . $cInfo->title . '</strong>'];

            $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=delete'))];
            $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_title') . ' ' . $cInfo->title];
            $contents[] = ['text' => OSCOM::getDef('text_info_currency_code') . ' ' . $cInfo->code];
            $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_symbol_left') . ' ' . $cInfo->symbol_left];
            $contents[] = ['text' => OSCOM::getDef('text_info_currency_symbol_right') . ' ' . $cInfo->symbol_right];
            $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_decimal_point') . ' ' . $cInfo->decimal_point];
            $contents[] = ['text' => OSCOM::getDef('text_info_currency_thousands_point') . ' ' . $cInfo->thousands_point];
            $contents[] = ['text' => OSCOM::getDef('text_info_currency_decimal_places') . ' ' . $cInfo->decimal_places];
            if (isset($cInfo->last_updated)) {
                $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_last_updated') . ' ' . DateTime::toShort($cInfo->last_updated)];
            }
            $contents[] = ['text' => OSCOM::getDef('text_info_currency_value') . ' ' . number_format($cInfo->value, 8)];
            $contents[] = ['text' => '<br />' . OSCOM::getDef('text_info_currency_example') . '<br />' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code)];
        }
        break;
}

if ((tep_not_null($heading)) && (tep_not_null($contents))) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box();
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
}
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
require('includes/application_bottom.php');
?>
