<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\Mail;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
require 'includes/application_top.php';
$OSCOM_Hooks = Registry::get('Hooks');
require 'includes/classes/currencies.php';
$currencies = new currencies();
$orders_statuses = [];
$orders_status_array = [];
$Qstatus = $OSCOM_Db->get('orders_status', ['orders_status_id', 'orders_status_name'], ['language_id' => $OSCOM_Language->get_id()]);
while ($Qstatus->fetch()) {
    $orders_statuses[] = ['id' => $Qstatus->value_int('orders_status_id'), 'text' => $Qstatus->value('orders_status_name')];
    $orders_status_array[$Qstatus->value_int('orders_status_id')] = $Qstatus->value('orders_status_name');
}
include 'includes/classes/order.php';
if (isset($_GET['oID']) && is_numeric($_GET['oID']) && $_GET['oID'] > 0) {
    $o_id = HTML::sanitize($_GET['oID']);
    $Qorders = $OSCOM_Db->get('orders', 'orders_id', ['orders_id' => (int) $o_id]);
    if ($Qorders->fetch()) {
        $order = new order($Qorders->value_int('orders_id'));
    } else {
        $oscom_message_stack->add(OSCOM::get_def('error_order_does_not_exist', ['order_id' => $o_id]), 'error');
    }
}
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
$OSCOM_Hooks->call('Orders', 'PreAction');
if (tep_not_null($action)) {
    switch ($action) {
        case 'update_order':
            $o_id = HTML::sanitize($_GET['oID']);
            $status = HTML::sanitize($_POST['status']);
            $comments = HTML::sanitize($_POST['comments']);
            $order_updated = false;
            $Qcheck = $OSCOM_Db->get('orders', ['customers_name', 'customers_email_address', 'orders_status', 'date_purchased'], ['orders_id' => (int) $o_id]);
            if ($Qcheck->value('orders_status') != $status || tep_not_null($comments)) {
                $OSCOM_Db->save('orders', ['orders_status' => $status, 'last_modified' => 'now()'], ['orders_id' => (int) $o_id]);
                $customer_notified = '0';
                if (isset($_POST['notify']) && $_POST['notify'] == 'on') {
                    $notify_comments = '';
                    $notify_comments_html = '';
                    if (isset($_POST['notify_comments']) && $_POST['notify_comments'] == 'on') {
                        $notify_comments = OSCOM::get_def('email_text_comments_update', ['comments' => $comments]) . "\n\n";
                        $notify_comments_html = OSCOM::get_def('email_text_comments_update_html', ['comments' => nl2br($comments)]);
                    }
                    $invoice_url = OSCOM::link('Shop/' . FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $o_id);
                    $email = STORE_NAME . "\n" . OSCOM::get_def('email_separator') . "\n" . OSCOM::get_def('email_text_order_number') . ' ' . $o_id . "\n" . OSCOM::get_def('email_text_invoice_url') . ' ' . $invoice_url . "\n" . OSCOM::get_def('email_text_date_ordered') . ' ' . DateTime::to_long($Qcheck->value('date_purchased')) . "\n\n" . $notify_comments . OSCOM::get_def('email_text_status_update', ['status' => $orders_status_array[$status]]) . "\n";
                    $email_html = '<p>' . STORE_NAME . '</p>' . OSCOM::get_def('email_separator_html') . '<p>' . OSCOM::get_def('email_text_order_number_html') . ' ' . $o_id . '</p><p>' . OSCOM::get_def('email_text_invoice_url_html') . ' <a href="' . $invoice_url . '">' . $invoice_url . '</a></p><p>' . OSCOM::get_def('email_text_date_ordered_html') . ' ' . DateTime::to_long($Qcheck->value('date_purchased')) . '</p>' . $notify_comments_html . OSCOM::get_def('email_text_status_update_html', ['status' => $orders_status_array[$status]]);
                    $order_email = new Mail($Qcheck->value('customers_email_address'), $Qcheck->value('customers_name'), STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::get_def('email_text_subject'));
                    $order_email->set_body_plain($email);
                    $order_email->set_body_html($email_html);
                    $order_email->send();
                    $customer_notified = '1';
                }
                $OSCOM_Db->save('orders_status_history', ['orders_id' => (int) $o_id, 'orders_status_id' => $status, 'date_added' => 'now()', 'customer_notified' => $customer_notified, 'comments' => $comments]);
                $order_updated = true;
            }
            if ($order_updated == true) {
                $oscom_message_stack->add(OSCOM::get_def('success_order_updated'), 'success');
            } else {
                $oscom_message_stack->add(OSCOM::get_def('warning_order_not_updated'), 'warning');
            }
            OSCOM::redirect(FILENAME_ORDERS, tep_get_all_get_params(['action']) . 'action=edit');
            break;
        case 'deleteconfirm':
            $o_id = HTML::sanitize($_GET['oID']);
            tep_remove_order($o_id, $_POST['restock']);
            OSCOM::redirect(FILENAME_ORDERS, tep_get_all_get_params(['oID', 'action']));
            break;
    }
}
$OSCOM_Hooks->call('Orders', 'Action');
$show_listing = true;
require $osc_template->get_file('template_top.php');
?>

<h2><i class="fa fa-shopping-cart"></i> <a href="<?php 
echo OSCOM::link('orders.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<?php 
if (!empty($action)) {
    if ($action == 'edit' && isset($order)) {
        $show_listing = false;
        ?>

<h3><?php 
        echo '#' . $order->info['id'] . ' (' . strip_tags((string) $order->info['total']) . ')';
        ?></h3>

<div style="text-align: right; padding-bottom: 15px;"><?php 
        echo HTML::button(OSCOM::get_def('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(['action'])), null, 'btn-info') . HTML::button(OSCOM::get_def('image_orders_invoice'), 'fa fa-file-text-o', OSCOM::link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']), ['newwindow' => true], 'btn-primary') . HTML::button(OSCOM::get_def('image_orders_packingslip'), 'fa fa-clipboard', OSCOM::link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']), ['newwindow' => true], 'btn-primary');
        ?></div>

<div id="orderTabs">
  <ul class="nav nav-tabs">
    <li class="active"><a data-target="#section_summary_content" data-toggle="tab"><?php 
        echo 'Summary';
        ?></a></li>
    <li><a data-target="#section_products_content" data-toggle="tab"><?php 
        echo 'Products';
        ?></a></li>
    <li><a data-target="#section_status_history_content" data-toggle="tab"><?php 
        echo 'History';
        ?></a></li>
  </ul>

  <div class="tab-content">
    <div id="section_summary_content" class="tab-pane active oscom-m-top-15">
      <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_customer');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />');
        ?></p>
              <p><?php 
        echo '<i class="fa fa-fw fa-phone"></i> ' . $order->customer['telephone'] . '<br /><i class="fa fa-fw fa-envelope-o"></i> ' . '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>';
        ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_shipping_address');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />');
        ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_billing_address');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />');
        ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_payment_method');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo $order->info['payment_method'];
        ?></p>

<?php 
        if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
            ?>

              <table class="oscom-table table oscom-table-borderless table-condensed">
                <tbody>
                  <tr>
                    <td><?php 
            echo OSCOM::get_def('entry_credit_card_type');
            ?></td>
                    <td><?php 
            echo $order->info['cc_type'];
            ?></td>
                  </tr>
                  <tr>
                    <td><?php 
            echo OSCOM::get_def('entry_credit_card_owner');
            ?></td>
                    <td><?php 
            echo $order->info['cc_owner'];
            ?></td>
                  </tr>
                  <tr>
                    <td><?php 
            echo OSCOM::get_def('entry_credit_card_number');
            ?></td>
                    <td><?php 
            echo $order->info['cc_number'];
            ?></td>
                  </tr>
                  <tr>
                    <td><?php 
            echo OSCOM::get_def('entry_credit_card_expires');
            ?></td>
                    <td><?php 
            echo $order->info['cc_expires'];
            ?></td>
                  </tr>
                </tbody>
              </table>

<?php 
        }
        ?>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_status');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo $order->info['status'] . '<br />' . (empty($order->info['last_modified']) ? DateTime::to_short($order->info['date_purchased'], true) : DateTime::to_short($order->info['last_modified'], true));
        ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?php 
        echo OSCOM::get_def('entry_total');
        ?></h3>
            </div>

            <div class="panel-body">
              <p><?php 
        echo strip_tags((string) $order->info['total']);
        ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="section_products_content" class="tab-pane">
      <table class="oscom-table table table-hover">
        <thead>
          <tr class="info">
            <th colspan="2"><?php 
        echo OSCOM::get_def('table_heading_products');
        ?></th>
            <th><?php 
        echo OSCOM::get_def('table_heading_products_model');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_tax');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_price_excluding_tax');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_price_including_tax');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_total_excluding_tax');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_total_including_tax');
        ?></th>
          </tr>
        </thead>
        <tbody>

<?php 
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            echo '          <tr>' . "\n" . '            <td class="text-right" valign="top">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" . '            <td valign="top">' . $order->products[$i]['name'];
            if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
                for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
                    echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
                    if ($order->products[$i]['attributes'][$j]['price'] != '0') {
                        echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
                    }
                    echo '</i></small></nobr>';
                }
            }
            echo '</td>' . "\n" . '            <td valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" . '            <td class="text-right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" . '            <td class="text-right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" . '            <td class="text-right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" . '            <td class="text-right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" . '            <td class="text-right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" . '          </tr>' . "\n";
        }
        ?>

        </tbody>
      </table>

      <table class="oscom-table table oscom-table-borderless table-condensed">
        <tbody>

<?php 
        foreach ($order->totals as $ot) {
            echo '          <tr>' . "\n" . '            <td class="text-right">' . $ot['title'] . '</td>' . "\n" . '            <td class="text-right">' . strip_tags((string) $ot['text']) . '</td>' . "\n" . '          </tr>' . "\n";
        }
        ?>

        </tbody>
      </table>
    </div>

    <div id="section_status_history_content" class="tab-pane oscom-m-top-15">
      <?php 
        echo HTML::form('status', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(['action']) . 'action=update_order'));
        ?>

        <div class="form-group">
          <label for="inputOrderStatus" class="control-label"><?php 
        echo OSCOM::get_def('entry_status');
        ?></label>

          <?php 
        echo HTML::select_field('status', $orders_statuses, $order->info['orders_status'], 'id="inputOrderStatus" class="form-control"');
        ?>
        </div>

        <div class="form-group">
          <label for="inputOrderComment" class="control-label"><?php 
        echo OSCOM::get_def('entry_add_comment');
        ?></label>

          <?php 
        echo HTML::textarea_field('comments', '60', '6', null, 'id="inputOrderComment" class="form-control"');
        ?>
        </div>

        <div class="form-group">
          <div class="checkbox">
            <label>
              <?php 
        echo HTML::checkbox_field('notify', '', true) . ' ' . OSCOM::get_def('entry_notify_customer');
        ?>
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="checkbox">
            <label>
              <?php 
        echo HTML::checkbox_field('notify_comments', '', true) . ' ' . OSCOM::get_def('entry_notify_comments');
        ?>
            </label>
          </div>
        </div>

        <div class="form-group">
          <?php 
        echo HTML::button(OSCOM::get_def('image_update'), 'fa fa-save', null, null, 'btn-success');
        ?>
        </div>
      </form>

      <table class="oscom-table table table-hover">
        <thead>
          <tr class="info">
            <th><?php 
        echo OSCOM::get_def('table_heading_date_added');
        ?></th>
            <th><?php 
        echo OSCOM::get_def('table_heading_status');
        ?></th>
            <th><?php 
        echo OSCOM::get_def('table_heading_comments');
        ?></th>
            <th class="text-right"><?php 
        echo OSCOM::get_def('table_heading_customer_notified');
        ?></th>
          </tr>
        </thead>
        <tbody>

<?php 
        $Qhistory = $OSCOM_Db->get('orders_status_history', ['orders_status_id', 'date_added', 'customer_notified', 'comments'], ['orders_id' => $o_id], 'date_added desc');
        if ($Qhistory->fetch() !== false) {
            do {
                echo '          <tr>' . "\n" . '            <td valign="top">' . DateTime::to_short($Qhistory->value('date_added'), true) . '</td>' . "\n" . '            <td valign="top">' . $orders_status_array[$Qhistory->value_int('orders_status_id')] . '</td>' . "\n" . '            <td valign="top">' . nl2br((string) HTML::output($Qhistory->value('comments'))) . '&nbsp;</td>' . "\n" . '            <td class="text-right" valign="top">';
                if ($Qhistory->value_int('customer_notified') === 1) {
                    echo HTML::image(OSCOM::link_image('icons/tick.gif'), OSCOM::get_def('icon_tick'));
                } else {
                    echo HTML::image(OSCOM::link_image('icons/cross.gif'), OSCOM::get_def('icon_cross'));
                }
                echo '</td>' . "\n" . '          </tr>' . "\n";
            } while ($Qhistory->fetch());
        } else {
            echo '          <tr>' . "\n" . '            <td colspan="4">' . OSCOM::get_def('text_no_order_history') . '</td>' . "\n" . '          </tr>' . "\n";
        }
        ?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<?php 
        echo $OSCOM_Hooks->output('Orders', 'Page', null, 'display');
        ?>

<?php 
    } else {
        $heading = $contents = [];
        switch ($action) {
            case 'delete':
                if (isset($order)) {
                    $heading[] = ['text' => OSCOM::get_def('text_info_heading_delete_order')];
                    $contents = ['form' => HTML::form('orders', OSCOM::link('orders.php', tep_get_all_get_params(['action']) . '&action=deleteconfirm'))];
                    $contents[] = ['text' => OSCOM::get_def('text_info_delete_intro') . '<br /><br /><strong>#' . $order->info['id'] . '</strong> ' . HTML::output_protected($order->customer['name']) . ' (' . strip_tags((string) $order->info['total']) . ')'];
                    $contents[] = ['text' => HTML::checkbox_field('restock') . ' ' . OSCOM::get_def('text_info_restock_product_quantity')];
                    $contents[] = ['text' => HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link('orders.php', tep_get_all_get_params(['action'])), null, 'btn-link')];
                }
                break;
        }
        if (tep_not_null($heading) && tep_not_null($contents)) {
            $show_listing = false;
            echo HTML::panel($heading, $contents, ['type' => 'info']);
        }
    }
}
if ($show_listing === true) {
    echo HTML::form('orders', OSCOM::link('orders.php'), 'get', 'class="form-inline"', ['session_id' => true]) . HTML::input_field('oID', null, 'placeholder="' . OSCOM::get_def('heading_title_search') . '"') . HTML::hidden_field('action', 'edit') . '</form>' . HTML::form('status', OSCOM::link('orders.php'), 'get', 'class="form-inline"', ['session_id' => true]) . HTML::select_field('status', array_merge([['id' => '', 'text' => OSCOM::get_def('text_all_orders')]], $orders_statuses), '', 'onchange="this.form.submit();"') . '</form>';
    ?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php 
    echo OSCOM::get_def('table_heading_customers');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_order_total');
    ?></th>
      <th></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_date_purchased');
    ?></th>
      <th class="text-right"><?php 
    echo OSCOM::get_def('table_heading_status');
    ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php 
    if (isset($_GET['cID'])) {
        $c_id = HTML::sanitize($_GET['cID']);
        $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.customers_id = :customers_id and o.orders_status = s.orders_status_id and s.language_id = :language_id and ot.class = "ot_total" order by orders_id desc limit :page_set_offset, :page_set_max_results');
        $Qorders->bind_int(':customers_id', $_GET['cID']);
    } elseif (isset($_GET['status']) && is_numeric($_GET['status']) && $_GET['status'] > 0) {
        $status = HTML::sanitize($_GET['status']);
        $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and s.orders_status_id = :orders_status_id and ot.class = "ot_total" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
        $Qorders->bind_int(':orders_status_id', $status);
    } else {
        $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and ot.class = "ot_total" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
    }
    $Qorders->bind_int(':language_id', $OSCOM_Language->get_id());
    $Qorders->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qorders->execute();
    while ($Qorders->fetch()) {
        ?>

    <tr>
      <td><?php 
        echo '<a href="' . OSCOM::link('orders.php', tep_get_all_get_params(['oID', 'action']) . 'oID=' . $Qorders->value_int('orders_id') . '&action=edit') . '">' . $Qorders->value('customers_name') . '</a> <small class="text-muted">#' . $Qorders->value_int('orders_id') . '</small>';
        ?></td>
      <td class="text-right"><?php 
        echo strip_tags((string) $Qorders->value('order_total')) . ' <small class="text-muted">' . $Qorders->value('currency') . '</small>';
        ?></td>
      <td><div class="oscom-truncate" style="width: 150px;"><small class="text-muted"><?php 
        echo $Qorders->value('payment_method');
        ?></small></div></td>
      <td class="text-right"><?php 
        echo DateTime::to_short($Qorders->value('date_purchased'), true);
        ?></td>
      <td class="text-right"><?php 
        echo $Qorders->value('orders_status_name');
        ?></td>
      <td class="action"><?php 
        echo '<a href="' . OSCOM::link('orders.php', tep_get_all_get_params(['oID', 'action']) . 'oID=' . $Qorders->value_int('orders_id') . '&action=edit') . '"><i class="fa fa-pencil" title="' . OSCOM::get_def('image_edit') . '"></i></a>
         <a href="' . OSCOM::link('orders.php', tep_get_all_get_params(['oID', 'action']) . 'oID=' . $Qorders->value_int('orders_id') . '&action=delete') . '"><i class="fa fa-trash" title="' . OSCOM::get_def('image_delete') . '"></i></a>
         <a href="' . OSCOM::link('invoice.php', 'oID=' . $Qorders->value_int('orders_id')) . '" target="_blank"><i class="fa fa-file-text-o" title="' . OSCOM::get_def('image_orders_invoice') . '"></i></a>
         <a href="' . OSCOM::link('packingslip.php', 'oID=' . $Qorders->value_int('orders_id')) . '" target="_blank"><i class="fa fa-clipboard" title="' . OSCOM::get_def('image_orders_packingslip') . '"></i></a>';
        ?></td>
    </tr>

<?php 
    }
    ?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?php 
    echo $Qorders->get_page_set_links(tep_get_all_get_params());
    ?></span>
  <?php 
    echo $Qorders->get_page_set_label(OSCOM::get_def('text_display_number_of_orders'));
    ?>
</div>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';