<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\Is;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
}
$action = $_GET['action'] ?? '';
$error = false;
$processed = false;
if (tep_not_null($action)) {
    switch ($action) {
        case 'update':
            $customers_id = HTML::sanitize($_GET['cID']);
            $customers_firstname = HTML::sanitize($_POST['customers_firstname']);
            $customers_lastname = HTML::sanitize($_POST['customers_lastname']);
            $customers_email_address = HTML::sanitize($_POST['customers_email_address']);
            $customers_telephone = HTML::sanitize($_POST['customers_telephone']);
            $customers_fax = HTML::sanitize($_POST['customers_fax']);
            $customers_newsletter = HTML::sanitize($_POST['customers_newsletter']);
            if (ACCOUNT_GENDER == 'true') {
                $customers_gender = HTML::sanitize($_POST['customers_gender']);
            }
            if (ACCOUNT_DOB == 'true') {
                $customers_dob = HTML::sanitize($_POST['customers_dob']);
            }
            $customers_default_address_id = HTML::sanitize($_POST['customers_default_address_id']);
            $entry_street_address = HTML::sanitize($_POST['entry_street_address']);
            $entry_suburb = HTML::sanitize($_POST['entry_suburb']);
            $entry_postcode = HTML::sanitize($_POST['entry_postcode']);
            $entry_city = HTML::sanitize($_POST['entry_city']);
            $entry_country_id = HTML::sanitize($_POST['entry_country_id']);
            $entry_company = HTML::sanitize($_POST['entry_company']);
            $entry_state = HTML::sanitize($_POST['entry_state']);
            if (isset($_POST['entry_zone_id'])) {
                $entry_zone_id = HTML::sanitize($_POST['entry_zone_id']);
            }
            if (ACCOUNT_GENDER == 'true') {
                if ($customers_gender != 'm' && $customers_gender != 'f') {
                    $error = true;
                    $entry_gender_error = true;
                } else {
                    $entry_gender_error = false;
                }
            }
            if (strlen((string) $customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                $error = true;
                $entry_firstname_error = true;
            } else {
                $entry_firstname_error = false;
            }
            if (strlen((string) $customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
                $error = true;
                $entry_lastname_error = true;
            } else {
                $entry_lastname_error = false;
            }
            if (ACCOUNT_DOB == 'true') {
                $dob_date_time = new DateTime($customers_dob);
                if (strlen((string) $customers_dob) >= ENTRY_DOB_MIN_LENGTH && $dob_date_time->is_valid()) {
                    $entry_date_of_birth_error = false;
                } else {
                    $error = true;
                    $entry_date_of_birth_error = true;
                }
            }
            $entry_email_address_error = false;
            if (!Is::email($customers_email_address)) {
                $error = true;
                $entry_email_address_check_error = true;
            } else {
                $entry_email_address_check_error = false;
            }
            if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                $error = true;
                $entry_street_address_error = true;
            } else {
                $entry_street_address_error = false;
            }
            if (strlen((string) $entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
                $error = true;
                $entry_post_code_error = true;
            } else {
                $entry_post_code_error = false;
            }
            if (strlen((string) $entry_city) < ENTRY_CITY_MIN_LENGTH) {
                $error = true;
                $entry_city_error = true;
            } else {
                $entry_city_error = false;
            }
            if ($entry_country_id == false) {
                $error = true;
                $entry_country_error = true;
            } else {
                $entry_country_error = false;
            }
            if (ACCOUNT_STATE == 'true') {
                if ($entry_country_error == true) {
                    $entry_state_error = true;
                } else {
                    $zone_id = 0;
                    $entry_state_error = false;
                    $Qcheck = $OSCOM_Db->get('zones', 'zone_country_id', ['zone_country_id' => (int) $entry_country_id]);
                    $entry_state_has_zones = $Qcheck->fetch() !== false;
                    if ($entry_state_has_zones == true) {
                        $Qzone = $OSCOM_Db->get('zones', 'zone_id', ['zone_country_id' => (int) $entry_country_id, 'zone_name' => $entry_state]);
                        if ($Qzone->fetch() !== false) {
                            $entry_zone_id = $Qzone->value_int('zone_id');
                        } else {
                            $error = true;
                            $entry_state_error = true;
                        }
                    } else if (strlen((string) $entry_state) < ENTRY_STATE_MIN_LENGTH) {
                        $error = true;
                        $entry_state_error = true;
                    }
                }
            }
            if (strlen((string) $customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
                $error = true;
                $entry_telephone_error = true;
            } else {
                $entry_telephone_error = false;
            }
            $Qcheck = $OSCOM_Db->get('customers', 'customers_email_address', ['customers_email_address' => $customers_email_address, 'customers_id' => ['op' => '!=', 'val' => (int) $customers_id]]);
            if ($Qcheck->fetch() !== false) {
                $error = true;
                $entry_email_address_exists = true;
            } else {
                $entry_email_address_exists = false;
            }
            if ($error == false) {
                $sql_data_array = ['customers_firstname' => $customers_firstname, 'customers_lastname' => $customers_lastname, 'customers_email_address' => $customers_email_address, 'customers_telephone' => $customers_telephone, 'customers_fax' => $customers_fax, 'customers_newsletter' => $customers_newsletter];
                if (ACCOUNT_GENDER == 'true') {
                    $sql_data_array['customers_gender'] = $customers_gender;
                }
                if (ACCOUNT_DOB == 'true') {
                    $sql_data_array['customers_dob'] = $dob_date_time->get_raw(false);
                }
                $OSCOM_Db->save('customers', $sql_data_array, ['customers_id' => (int) $customers_id]);
                $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => (int) $customers_id]);
                if (isset($entry_zone_id) && $entry_zone_id > 0) {
                    $entry_state = '';
                }
                $sql_data_array = ['entry_firstname' => $customers_firstname, 'entry_lastname' => $customers_lastname, 'entry_street_address' => $entry_street_address, 'entry_postcode' => $entry_postcode, 'entry_city' => $entry_city, 'entry_country_id' => $entry_country_id];
                if (ACCOUNT_COMPANY == 'true') {
                    $sql_data_array['entry_company'] = $entry_company;
                }
                if (ACCOUNT_SUBURB == 'true') {
                    $sql_data_array['entry_suburb'] = $entry_suburb;
                }
                if (ACCOUNT_STATE == 'true') {
                    if (isset($entry_zone_id) && $entry_zone_id > 0) {
                        $sql_data_array['entry_zone_id'] = $entry_zone_id;
                        $sql_data_array['entry_state'] = '';
                    } else {
                        $sql_data_array['entry_zone_id'] = '0';
                        $sql_data_array['entry_state'] = $entry_state;
                    }
                }
                $OSCOM_Db->save('address_book', $sql_data_array, ['customers_id' => (int) $customers_id, 'address_book_id' => (int) $customers_default_address_id]);
                OSCOM::redirect(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $customers_id);
            } else {
                $c_info = new Object_Info($_POST);
                $processed = true;
            }
            break;
        case 'deleteconfirm':
            $customers_id = HTML::sanitize($_GET['cID']);
            if (isset($_POST['delete_reviews']) && $_POST['delete_reviews'] == 'on') {
                $Qreviews = $OSCOM_Db->get('reviews', 'reviews_id', ['customers_id' => (int) $customers_id]);
                while ($Qreviews->fetch()) {
                    $OSCOM_Db->delete('reviews_description', ['reviews_id' => (int) $reviews['reviews_id']]);
                }
                $OSCOM_Db->delete('reviews', ['customers_id' => (int) $customers_id]);
            } else {
                $OSCOM_Db->save('reviews', ['customers_id' => 'null'], ['customers_id' => (int) $customers_id]);
            }
            $OSCOM_Db->delete('address_book', ['customers_id' => (int) $customers_id]);
            $OSCOM_Db->delete('customers', ['customers_id' => (int) $customers_id]);
            $OSCOM_Db->delete('customers_info', ['customers_info_id' => (int) $customers_id]);
            $OSCOM_Db->delete('customers_basket', ['customers_id' => (int) $customers_id]);
            $OSCOM_Db->delete('customers_basket_attributes', ['customers_id' => (int) $customers_id]);
            $OSCOM_Db->delete('whos_online', ['customer_id' => (int) $customers_id]);
            OSCOM::redirect(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']));
            break;
        default:
            if ($action != 'confirm') {
                $Qcustomer = $OSCOM_Db->prepare('select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_default_address_id from :table_customers c left join :table_address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = :customers_id');
                $Qcustomer->bind_int(':customers_id', $_GET['cID']);
                $Qcustomer->execute();
                $c_info = new Object_Info($Qcustomer->to_array());
            }
    }
}
require $osc_template->get_file('template_top.php');
if ($action == 'edit' || $action == 'update') {
    ?>
<script type="text/javascript"><!--

function check_form() {
  var error = 0;
  var error_message = <?php 
    echo json_encode(OSCOM::get_def('js_error') . "\n\n");
    ?>;

  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
<?php 
    if (ACCOUNT_COMPANY == 'true') {
        echo 'var entry_company = document.customers.entry_company.value;' . "\n";
    }
    if (ACCOUNT_DOB == 'true') {
        echo 'var customers_dob = document.customers.customers_dob.value;' . "\n";
    }
    ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
  var entry_postcode = document.customers.entry_postcode.value;
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;

<?php 
    if (ACCOUNT_GENDER == 'true') {
        ?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
    error_message = error_message + <?php 
        echo json_encode(OSCOM::get_def('js_gender') . "\n");
        ?>;
    error = 1;
  }
<?php 
    }
    ?>

  if (customers_firstname.length < <?php 
    echo ENTRY_FIRST_NAME_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_first_name', ['min_length' => ENTRY_FIRST_NAME_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

  if (customers_lastname.length < <?php 
    echo ENTRY_LAST_NAME_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_last_name', ['min_length' => ENTRY_LAST_NAME_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

<?php 
    if (ACCOUNT_DOB == 'true') {
        ?>
  if (customers_dob.length < <?php 
        echo ENTRY_DOB_MIN_LENGTH;
        ?>) {
    error_message = error_message + <?php 
        echo json_encode(OSCOM::get_def('js_dob') . "\n");
        ?>;
    error = 1;
  }
<?php 
    }
    ?>

  if (entry_street_address.length < <?php 
    echo ENTRY_STREET_ADDRESS_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_address', ['min_length' => ENTRY_STREET_ADDRESS_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

  if (entry_postcode.length < <?php 
    echo ENTRY_POSTCODE_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_post_code', ['min_length' => ENTRY_POSTCODE_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

  if (entry_city.length < <?php 
    echo ENTRY_CITY_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_city', ['min_length' => ENTRY_CITY_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

<?php 
    if (ACCOUNT_STATE == 'true') {
        ?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value.length < <?php 
        echo ENTRY_STATE_MIN_LENGTH;
        ?>) {
       error_message = error_message + <?php 
        echo json_encode(OSCOM::get_def('js_state') . "\n");
        ?>;
       error = 1;
    }
  }
<?php 
    }
    ?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_country') . "\n");
    ?>;
      error = 1;
    }
  }

  if (customers_telephone.length < <?php 
    echo ENTRY_TELEPHONE_MIN_LENGTH;
    ?>) {
    error_message = error_message + <?php 
    echo json_encode(OSCOM::get_def('js_telephone', ['min_length' => ENTRY_TELEPHONE_MIN_LENGTH]) . "\n");
    ?>;
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php 
}
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php 
if ($action == 'edit' || $action == 'update') {
    $newsletter_array = [['id' => '1', 'text' => OSCOM::get_def('entry_newsletter_yes')], ['id' => '0', 'text' => OSCOM::get_def('entry_newsletter_no')]];
    ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php 
    echo OSCOM::get_def('heading_title');
    ?></td>
          </tr>
        </table></td>
      </tr>
      <tr><?php 
    echo HTML::form('customers', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['action']) . 'action=update'), 'post', 'onsubmit="return check_form();"') . HTML::hidden_field('customers_default_address_id', $c_info->customers_default_address_id);
    ?>
        <td class="formAreaTitle"><?php 
    echo OSCOM::get_def('category_personal');
    ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php 
    if (ACCOUNT_GENDER == 'true') {
        ?>
          <tr>
            <td class="main"><?php 
        echo OSCOM::get_def('entry_gender');
        ?></td>
            <td class="main">
<?php 
        if ($error == true) {
            if ($entry_gender_error == true) {
                echo HTML::radio_field('customers_gender', 'm', $c_info->customers_gender == 'm') . '&nbsp;&nbsp;' . OSCOM::get_def('male') . '&nbsp;&nbsp;' . HTML::radio_field('customers_gender', 'f', $c_info->customers_gender == 'f') . '&nbsp;&nbsp;' . OSCOM::get_def('female') . '&nbsp;' . OSCOM::get_def('entry_gender_error');
            } else {
                echo $c_info->customers_gender == 'm' ? OSCOM::get_def('male') : OSCOM::get_def('female');
                echo HTML::hidden_field('customers_gender');
            }
        } else {
            echo HTML::radio_field('customers_gender', 'm', $c_info->customers_gender == 'm') . '&nbsp;&nbsp;' . OSCOM::get_def('male') . '&nbsp;&nbsp;' . HTML::radio_field('customers_gender', 'f', $c_info->customers_gender == 'f') . '&nbsp;&nbsp;' . OSCOM::get_def('female');
        }
        ?></td>
          </tr>
<?php 
    }
    ?>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_first_name');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_firstname_error == true) {
            echo HTML::input_field('customers_firstname', $c_info->customers_firstname, 'maxlength="32"') . '&nbsp;' . OSCOM::get_def('entry_first_name_error', ['min_length' => ENTRY_FIRST_NAME_MIN_LENGTH]);
        } else {
            echo $c_info->customers_firstname . HTML::hidden_field('customers_firstname');
        }
    } else {
        echo HTML::input_field('customers_firstname', $c_info->customers_firstname, 'maxlength="32"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_last_name');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_lastname_error == true) {
            echo HTML::input_field('customers_lastname', $c_info->customers_lastname, 'maxlength="32"') . '&nbsp;' . OSCOM::get_def('entry_last_name_error', ['min_length' => ENTRY_LAST_NAME_MIN_LENGTH]);
        } else {
            echo $c_info->customers_lastname . HTML::hidden_field('customers_lastname');
        }
    } else {
        echo HTML::input_field('customers_lastname', $c_info->customers_lastname, 'maxlength="32"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
<?php 
    if (ACCOUNT_DOB == 'true') {
        ?>
          <tr>
            <td class="main"><?php 
        echo OSCOM::get_def('entry_date_of_birth');
        ?></td>
            <td class="main">
<?php 
        if ($error == true) {
            if ($entry_date_of_birth_error == true) {
                echo HTML::input_field('customers_dob', DateTime::to_short($c_info->customers_dob), 'maxlength="10"') . '&nbsp;' . OSCOM::get_def('entry_date_of_birth_error');
            } else {
                echo $c_info->customers_dob . HTML::hidden_field('customers_dob');
            }
        } else {
            echo HTML::input_field('customers_dob', DateTime::to_short($c_info->customers_dob), 'maxlength="10" id="customers_dob"') . OSCOM::get_def('text_field_required');
        }
        ?>
              <script type="text/javascript">$('#customers_dob').datepicker({dateFormat: '<?php 
        echo OSCOM::get_def('jquery_datepicker_format');
        ?>', changeMonth: true, changeYear: true, yearRange: '-100:+0'});</script>
            </td>
          </tr>
<?php 
    }
    ?>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_email_address');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_email_address_error == true) {
            echo HTML::input_field('customers_email_address', $c_info->customers_email_address, 'maxlength="96"') . '&nbsp;' . OSCOM::get_def('entry_email_address_error', ['min_length' => ENTRY_EMAIL_ADDRESS_MIN_LENGTH]);
        } elseif ($entry_email_address_check_error == true) {
            echo HTML::input_field('customers_email_address', $c_info->customers_email_address, 'maxlength="96"') . '&nbsp;' . OSCOM::get_def('entry_email_address_check_error');
        } elseif ($entry_email_address_exists == true) {
            echo HTML::input_field('customers_email_address', $c_info->customers_email_address, 'maxlength="96"') . '&nbsp;' . OSCOM::get_def('entry_email_address_error_exists');
        } else {
            echo $customers_email_address . HTML::hidden_field('customers_email_address');
        }
    } else {
        echo HTML::input_field('customers_email_address', $c_info->customers_email_address, 'maxlength="96"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
        </table></td>
      </tr>
<?php 
    if (ACCOUNT_COMPANY == 'true') {
        ?>
      <tr>
        <td class="formAreaTitle"><?php 
        echo OSCOM::get_def('category_company');
        ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php 
        echo OSCOM::get_def('entry_company');
        ?></td>
            <td class="main">
<?php 
        if ($error == true) {
            echo $c_info->entry_company . HTML::hidden_field('entry_company');
        } else {
            echo HTML::input_field('entry_company', $c_info->entry_company, 'maxlength="32"');
        }
        ?></td>
          </tr>
        </table></td>
      </tr>
<?php 
    }
    ?>
      <tr>
        <td class="formAreaTitle"><?php 
    echo OSCOM::get_def('category_address');
    ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_street_address');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_street_address_error == true) {
            echo HTML::input_field('entry_street_address', $c_info->entry_street_address, 'maxlength="64"') . '&nbsp;' . OSCOM::get_def('entry_street_address_error', ['min_length' => ENTRY_STREET_ADDRESS_MIN_LENGTH]);
        } else {
            echo $c_info->entry_street_address . HTML::hidden_field('entry_street_address');
        }
    } else {
        echo HTML::input_field('entry_street_address', $c_info->entry_street_address, 'maxlength="64"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
<?php 
    if (ACCOUNT_SUBURB == 'true') {
        ?>
          <tr>
            <td class="main"><?php 
        echo OSCOM::get_def('entry_suburb');
        ?></td>
            <td class="main">
<?php 
        if ($error == true) {
            echo $c_info->entry_suburb . HTML::hidden_field('entry_suburb');
        } else {
            echo HTML::input_field('entry_suburb', $c_info->entry_suburb, 'maxlength="32"');
        }
        ?></td>
          </tr>
<?php 
    }
    ?>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_post_code');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_post_code_error == true) {
            echo HTML::input_field('entry_postcode', $c_info->entry_postcode, 'maxlength="8"') . '&nbsp;' . OSCOM::get_def('entry_post_code_error', ['min_length' => ENTRY_POSTCODE_MIN_LENGTH]);
        } else {
            echo $c_info->entry_postcode . HTML::hidden_field('entry_postcode');
        }
    } else {
        echo HTML::input_field('entry_postcode', $c_info->entry_postcode, 'maxlength="8"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_city');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_city_error == true) {
            echo HTML::input_field('entry_city', $c_info->entry_city, 'maxlength="32"') . '&nbsp;' . OSCOM::get_def('entry_city_error', ['min_length' => ENTRY_CITY_MIN_LENGTH]);
        } else {
            echo $c_info->entry_city . HTML::hidden_field('entry_city');
        }
    } else {
        echo HTML::input_field('entry_city', $c_info->entry_city, 'maxlength="32"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
<?php 
    if (ACCOUNT_STATE == 'true') {
        ?>
          <tr>
            <td class="main"><?php 
        echo OSCOM::get_def('entry_state');
        ?></td>
            <td class="main">
<?php 
        if ($error == true) {
            if ($entry_state_error == true) {
                if ($entry_state_has_zones == true) {
                    $zones_array = [];
                    $Qzones = $OSCOM_Db->get('zones', 'zone_name', ['zone_country_id' => $c_info->entry_country_id], 'zone_name');
                    while ($Qzones->fetch()) {
                        $zones_array[] = ['id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name')];
                    }
                    echo HTML::select_field('entry_state', $zones_array) . '&nbsp;' . OSCOM::get_def('entry_state_error', ['min_length' => ENTRY_STATE_MIN_LENGTH]);
                } else {
                    echo HTML::input_field('entry_state', tep_get_zone_name($c_info->entry_country_id, $c_info->entry_zone_id, $c_info->entry_state)) . '&nbsp;' . OSCOM::get_def('entry_state_error', ['min_length' => ENTRY_STATE_MIN_LENGTH]);
                }
            } else {
                echo tep_get_zone_name($c_info->entry_country_id, $c_info->entry_zone_id, $c_info->entry_state) . HTML::hidden_field('entry_zone_id') . HTML::hidden_field('entry_state');
            }
        } else {
            echo HTML::input_field('entry_state', tep_get_zone_name($c_info->entry_country_id, $c_info->entry_zone_id, $c_info->entry_state));
        }
        ?></td>
         </tr>
<?php 
    }
    ?>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_country');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_country_error == true) {
            echo HTML::select_field('entry_country_id', tep_get_countries(), $c_info->entry_country_id) . '&nbsp;' . OSCOM::get_def('entry_country_error');
        } else {
            echo tep_get_country_name($c_info->entry_country_id) . HTML::hidden_field('entry_country_id');
        }
    } else {
        echo HTML::select_field('entry_country_id', tep_get_countries(), $c_info->entry_country_id);
    }
    ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php 
    echo OSCOM::get_def('category_contact');
    ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_telephone_number');
    ?></td>
            <td class="main">
<?php 
    if ($error == true) {
        if ($entry_telephone_error == true) {
            echo HTML::input_field('customers_telephone', $c_info->customers_telephone, 'maxlength="32"') . '&nbsp;' . OSCOM::get_def('entry_telephone_number_error', ['min_length' => ENTRY_TELEPHONE_MIN_LENGTH]);
        } else {
            echo $c_info->customers_telephone . HTML::hidden_field('customers_telephone');
        }
    } else {
        echo HTML::input_field('customers_telephone', $c_info->customers_telephone, 'maxlength="32"') . OSCOM::get_def('text_field_required');
    }
    ?></td>
          </tr>
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_fax_number');
    ?></td>
            <td class="main">
<?php 
    if ($processed == true) {
        echo $c_info->customers_fax . HTML::hidden_field('customers_fax');
    } else {
        echo HTML::input_field('customers_fax', $c_info->customers_fax, 'maxlength="32"');
    }
    ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php 
    echo OSCOM::get_def('category_options');
    ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php 
    echo OSCOM::get_def('entry_newsletter');
    ?></td>
            <td class="main">
<?php 
    if ($processed == true) {
        if ($c_info->customers_newsletter == '1') {
            echo OSCOM::get_def('entry_newsletter_yes');
        } else {
            echo OSCOM::get_def('entry_newsletter_no');
        }
        echo HTML::hidden_field('customers_newsletter');
    } else {
        echo HTML::select_field('customers_newsletter', $newsletter_array, $c_info->customers_newsletter == '1' ? '1' : '0');
    }
    ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="right" class="smallText"><?php 
    echo HTML::button(OSCOM::get_def('image_save'), 'fa fa-save') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['action'])));
    ?></td>
      </tr></form>
<?php 
} else {
    ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php 
    echo HTML::form('search', OSCOM::link(FILENAME_CUSTOMERS), 'get', null, ['session_id' => true]);
    ?>
            <td class="pageHeading"><?php 
    echo OSCOM::get_def('heading_title');
    ?></td>
            <td class="smallText" align="right"><?php 
    echo OSCOM::get_def('heading_title_search') . ' ' . HTML::input_field('search');
    ?></td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
    echo OSCOM::get_def('table_heading_lastname');
    ?></td>
                <td class="dataTableHeadingContent"><?php 
    echo OSCOM::get_def('table_heading_firstname');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_account_created');
    ?></td>
                <td class="dataTableHeadingContent" align="right"><?php 
    echo OSCOM::get_def('table_heading_action');
    ?>&nbsp;</td>
              </tr>
<?php 
    $sql_query = 'select SQL_CALC_FOUND_ROWS c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, a.entry_country_id from :table_customers c left join :table_address_book a on (c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id)';
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
        $sql_query .= ' where c.customers_lastname like :customers_lastname or c.customers_firstname like :customers_firstname or c.customers_email_address like :customers_email_address';
    }
    $sql_query .= ' order by c.customers_lastname, c.customers_firstname limit :page_set_offset, :page_set_max_results';
    $Qcustomers = $OSCOM_Db->prepare($sql_query);
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
        $Qcustomers->bind_value(':customers_lastname', '%' . $_GET['search'] . '%');
        $Qcustomers->bind_value(':customers_firstname', '%' . $_GET['search'] . '%');
        $Qcustomers->bind_value(':customers_email_address', '%' . $_GET['search'] . '%');
    }
    $Qcustomers->set_page_set(MAX_DISPLAY_SEARCH_RESULTS);
    $Qcustomers->execute();
    while ($Qcustomers->fetch()) {
        $Qinfo = $OSCOM_Db->get('customers_info', ['customers_info_date_account_created as date_account_created', 'customers_info_date_account_last_modified as date_account_last_modified', 'customers_info_date_of_last_logon as date_last_logon', 'customers_info_number_of_logons as number_of_logons'], ['customers_info_id' => $Qcustomers->value_int('customers_id')]);
        if ((!isset($_GET['cID']) || isset($_GET['cID']) && (int) $_GET['cID'] === $Qcustomers->value_int('customers_id')) && !isset($c_info)) {
            $Qcountry = $OSCOM_Db->get('countries', 'countries_name', ['countries_id' => $Qcustomers->value_int('entry_country_id')]);
            $Qreviews = $OSCOM_Db->get('reviews', 'count(*) as number_of_reviews', ['customers_id' => $Qcustomers->value_int('customers_id')]);
            $customer_info = array_merge($Qcountry->to_array(), $Qinfo->to_array(), $Qreviews->to_array());
            $c_info_array = array_merge($Qcustomers->to_array(), $customer_info);
            $c_info = new Object_Info($c_info_array);
        }
        if (isset($c_info) && is_object($c_info) && $Qcustomers->value_int('customers_id') === (int) $c_info->customers_id) {
            echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $c_info->customers_id . '&action=edit') . '\'">' . "\n";
        } else {
            echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID']) . 'cID=' . $Qcustomers->value_int('customers_id')) . '\'">' . "\n";
        }
        ?>
                <td class="dataTableContent"><?php 
        echo $Qcustomers->value('customers_lastname');
        ?></td>
                <td class="dataTableContent"><?php 
        echo $Qcustomers->value('customers_firstname');
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        echo DateTime::to_short($Qinfo->value('date_account_created'));
        ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (isset($c_info) && is_object($c_info) && $Qcustomers->value_int('customers_id') === (int) $c_info->customers_id) {
            echo HTML::image(OSCOM::link_image('icon_arrow_right.gif'), '');
        } else {
            echo '<a href="' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID']) . 'cID=' . $Qcustomers->value_int('customers_id')) . '">' . HTML::image(OSCOM::link_image('icon_info.gif'), OSCOM::get_def('image_icon_info')) . '</a>';
        }
        ?>&nbsp;</td>
              </tr>
<?php 
    }
    ?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php 
    echo $Qcustomers->get_page_set_label(OSCOM::get_def('text_display_number_of_customers'));
    ?></td>
                    <td class="smallText" align="right"><?php 
    echo $Qcustomers->get_page_set_links();
    ?></td>
                  </tr>
<?php 
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
        ?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php 
        echo HTML::button(OSCOM::get_def('image_reset'), 'fa fa-refresh', OSCOM::link(FILENAME_CUSTOMERS));
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
        case 'confirm':
            $heading[] = ['text' => '<strong>' . OSCOM::get_def('text_info_heading_delete_customer') . '</strong>'];
            $contents = ['form' => HTML::form('customers', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $c_info->customers_id . '&action=deleteconfirm'))];
            $contents[] = ['text' => OSCOM::get_def('text_delete_intro') . '<br /><br /><strong>' . $c_info->customers_firstname . ' ' . $c_info->customers_lastname . '</strong>'];
            if (isset($c_info->number_of_reviews) && $c_info->number_of_reviews > 0) {
                $contents[] = ['text' => '<br />' . HTML::checkbox_field('delete_reviews', 'on', true) . ' ' . OSCOM::get_def('text_delete_reviews', ['number_of_reviews' => $c_info->number_of_reviews])];
            }
            $contents[] = ['align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::get_def('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $c_info->customers_id))];
            break;
        default:
            if (isset($c_info) && is_object($c_info)) {
                $heading[] = ['text' => '<strong>' . $c_info->customers_firstname . ' ' . $c_info->customers_lastname . '</strong>'];
                $contents[] = ['align' => 'center', 'text' => HTML::button(OSCOM::get_def('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $c_info->customers_id . '&action=edit')) . HTML::button(OSCOM::get_def('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(['cID', 'action']) . 'cID=' . $c_info->customers_id . '&action=confirm')) . HTML::button(OSCOM::get_def('image_orders'), 'fa fa-shopping-cart', OSCOM::link(FILENAME_ORDERS, 'cID=' . $c_info->customers_id)) . HTML::button(OSCOM::get_def('image_email'), 'fa fa-envelope', OSCOM::link(FILENAME_MAIL, 'customer=' . $c_info->customers_email_address))];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_date_account_created') . ' ' . DateTime::to_short($c_info->date_account_created)];
                if (isset($c_info->date_account_last_modified)) {
                    $contents[] = ['text' => '<br />' . OSCOM::get_def('text_date_account_last_modified') . ' ' . DateTime::to_short($c_info->date_account_last_modified)];
                }
                if (isset($c_info->date_last_logon)) {
                    $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_date_last_logon') . ' ' . DateTime::to_short($c_info->date_last_logon)];
                }
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_number_of_logons') . ' ' . $c_info->number_of_logons];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_country') . ' ' . $c_info->countries_name];
                $contents[] = ['text' => '<br />' . OSCOM::get_def('text_info_number_of_reviews') . ' ' . $c_info->number_of_reviews];
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