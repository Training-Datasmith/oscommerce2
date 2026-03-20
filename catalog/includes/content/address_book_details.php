<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\HTML;
use OSC\OM\OSCOM;
if (!isset($process)) {
    $process = false;
}
?>

  <p class="text-right"><?php 
echo OSCOM::get_def('form_required_information');
?></p>

  <?php 
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    ?>
    <div class="page-header">
      <h4><?php 
    echo OSCOM::get_def('edit_address_title');
    ?></h4>
    </div>
    <?php 
} else {
    ?>
    <div class="page-header">
      <h4><?php 
    echo OSCOM::get_def('new_address_title');
    ?></h4>
    </div>
    <?php 
}
?>

  <div class="contentText">

<?php 
if (ACCOUNT_GENDER == 'true') {
    $male = $female = false;
    if (isset($gender)) {
        $male = $gender == 'm' ? true : false;
        $female = !$male;
    } elseif (isset($entry['entry_gender'])) {
        $male = $entry['entry_gender'] == 'm' ? true : false;
        $female = !$male;
    }
    ?>

      <div class="form-group has-feedback">
        <label class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_gender');
    ?></label>
        <div class="col-sm-9">
          <label class="radio-inline">
            <?php 
    echo HTML::radio_field('gender', 'm', $male, 'aria-describedby="atGender"') . ' ' . OSCOM::get_def('male');
    ?>
          </label>
          <label class="radio-inline">
            <?php 
    echo HTML::radio_field('gender', 'f', $female) . ' ' . OSCOM::get_def('female');
    ?>
          </label>
          <?php 
    echo OSCOM::get_def('form_required_input');
    ?>
          <?php 
    if (tep_not_null(OSCOM::get_def('entry_gender_text'))) {
        echo '<span id="atGender" class="help-block">' . OSCOM::get_def('entry_gender_text') . '</span>';
    }
    ?>
        </div>
      </div>

<?php 
}
?>

      <div class="form-group has-feedback">
        <label for="inputFirstName" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_first_name');
?></label>
        <div class="col-sm-9">
          <?php 
echo HTML::input_field('firstname', $entry['entry_firstname'] ?? '', 'id="inputFirstName" placeholder="' . OSCOM::get_def('entry_first_name_text') . '"');
?>
          <?php 
echo OSCOM::get_def('form_required_input');
?>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label for="inputLastName" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_last_name');
?></label>
        <div class="col-sm-9">
          <?php 
echo HTML::input_field('lastname', $entry['entry_lastname'] ?? '', 'id="inputLastName" placeholder="' . OSCOM::get_def('entry_last_name_text') . '"');
?>
          <?php 
echo OSCOM::get_def('form_required_input');
?>
        </div>
      </div>

<?php 
if (ACCOUNT_COMPANY == 'true') {
    ?>

      <div class="form-group">
        <label for="inputCompany" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_company');
    ?></label>
        <div class="col-sm-9">
          <?php 
    echo HTML::input_field('company', $entry['entry_company'] ?? '', 'id="inputCompany" placeholder="' . OSCOM::get_def('entry_company_text') . '"');
    ?>
        </div>
      </div>

<?php 
}
?>

      <div class="form-group has-feedback">
        <label for="inputStreet" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_street_address');
?></label>
        <div class="col-sm-9">
          <?php 
echo HTML::input_field('street_address', $entry['entry_street_address'] ?? '', 'id="inputStreet" placeholder="' . OSCOM::get_def('entry_street_address_text') . '"');
echo OSCOM::get_def('form_required_input');
?>
        </div>
      </div>

<?php 
if (ACCOUNT_SUBURB == 'true') {
    ?>

      <div class="form-group">
        <label for="inputSuburb" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_suburb');
    ?></label>
        <div class="col-sm-9">
          <?php 
    echo HTML::input_field('suburb', $entry['entry_suburb'] ?? '', 'id="inputSuburb" placeholder="' . OSCOM::get_def('entry_suburb_text') . '"');
    ?>
        </div>
      </div>

<?php 
}
?>

      <div class="form-group has-feedback">
        <label for="inputCity" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_city');
?></label>
        <div class="col-sm-9">
          <?php 
echo HTML::input_field('city', $entry['entry_city'] ?? '', 'id="inputCity" placeholder="' . OSCOM::get_def('entry_city_text') . '"');
echo OSCOM::get_def('form_required_input');
?>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label for="inputZip" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_post_code');
?></label>
        <div class="col-sm-9">
          <?php 
echo HTML::input_field('postcode', $entry['entry_postcode'] ?? '', 'id="inputZip" placeholder="' . OSCOM::get_def('entry_post_code_text') . '"');
echo OSCOM::get_def('form_required_input');
?>
        </div>
      </div>

<?php 
if (ACCOUNT_STATE == 'true') {
    ?>

      <div class="form-group">
        <label for="inputState" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('entry_state');
    ?></label>
        <div class="col-sm-9">
          <?php 
    if ($process == true) {
        if ($entry_state_has_zones == true) {
            $zones_array = [];
            $Qzones = $OSCOM_Db->prepare('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
            $Qzones->bind_int(':zone_country_id', $country);
            $Qzones->execute();
            while ($Qzones->fetch()) {
                $zones_array[] = ['id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name')];
            }
            echo HTML::select_field('state', $zones_array, 0, 'id="inputState" aria-describedby="atState"');
        } else {
            echo HTML::input_field('state', null, 'id="inputState" placeholder="' . OSCOM::get_def('entry_state_text') . '"');
        }
    } else {
        echo HTML::input_field('state', isset($entry['entry_country_id']) ? tep_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']) : '', 'id="inputState" placeholder="' . OSCOM::get_def('entry_state_text') . '"');
    }
    if (tep_not_null(OSCOM::get_def('entry_state_text'))) {
        echo '<span id="atState" class="help-block">' . OSCOM::get_def('entry_state_text') . '</span>';
    }
    ?>
        </div>
      </div>

<?php 
}
?>

      <div class="form-group has-feedback">
        <label for="inputCountry" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_country');
?></label>
        <div class="col-sm-9">
          <?php 
echo tep_get_country_list('country', $entry['entry_country_id'] ?? STORE_COUNTRY, 'aria-describedby="atCountry" id="inputCountry"');
echo OSCOM::get_def('form_required_input');
if (tep_not_null(OSCOM::get_def('entry_country_text'))) {
    echo '<span id="atCountry" class="help-block">' . OSCOM::get_def('entry_country_text') . '</span>';
}
?>
        </div>
      </div>

<?php 
if (isset($_GET['edit']) && $_SESSION['customer_default_address_id'] != $_GET['edit'] || isset($_GET['edit']) == false) {
    ?>

      <div class="form-group">
        <label for="primary" class="control-label col-sm-3"><?php 
    echo OSCOM::get_def('set_as_primary');
    ?></label>
        <div class="col-sm-9">
          <div class="checkbox">
            <label>
              <?php 
    echo HTML::checkbox_field('primary', 'on', false, 'id="primary"');
    ?>
            </label>
          </div>
        </div>
      </div>

<?php 
}
?>
  </div>
