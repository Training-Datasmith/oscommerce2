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

  <div class="contentText">

<?php 
if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
        $male = $gender == 'm' ? true : false;
        $female = $gender == 'f' ? true : false;
    } else {
        $male = false;
        $female = false;
    }
    ?>

    <div class="form-group">
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
    if (tep_not_null(OSCOM::get_def('entry_gender_text'))) {
        echo '<span id="atGender" class="help-block">' . OSCOM::get_def('entry_gender_text') . '</span>';
    }
    ?>
      </div>
    </div>

<?php 
}
?>

    <div class="form-group">
      <label for="inputFirstName" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_first_name');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::input_field('firstname', null, 'id="inputFirstName" placeholder="' . OSCOM::get_def('entry_first_name_text') . '"');
?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputLastName" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_last_name');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::input_field('lastname', null, 'id="inputLastName" placeholder="' . OSCOM::get_def('entry_last_name_text') . '"');
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
    echo HTML::input_field('company', null, 'id="inputCompany" placeholder="' . OSCOM::get_def('entry_company_text') . '"');
    ?>
      </div>
    </div>

<?php 
}
?>

    <div class="form-group">
      <label for="inputStreet" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_street_address');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::input_field('street_address', null, 'id="inputStreet" placeholder="' . OSCOM::get_def('entry_street_address_text') . '"');
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
    echo HTML::input_field('suburb', null, 'id="inputSuburb" placeholder="' . OSCOM::get_def('entry_suburb_text') . '"');
    ?>
      </div>
    </div>

<?php 
}
?>

    <div class="form-group">
      <label for="inputCity" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_city');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::input_field('city', null, 'id="inputCity" placeholder="' . OSCOM::get_def('entry_city_text') . '"');
?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputZip" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_post_code');
?></label>
      <div class="col-sm-9">
        <?php 
echo HTML::input_field('postcode', null, 'id="inputZip" placeholder="' . OSCOM::get_def('entry_post_code_text') . '"');
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
            $Qzones = $OSCOM_Db->get('zones', 'zone_name', ['zone_country_id' => $country], 'zone_name');
            while ($Qzones->fetch()) {
                $zones_array[] = ['id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name')];
            }
            echo HTML::select_field('state', $zones_array, 0, 'id="inputState" aria-describedby="atState"');
            if (tep_not_null(OSCOM::get_def('entry_state_text'))) {
                echo '<span id="atState" class="help-block">' . OSCOM::get_def('entry_state_text') . '</span>';
            }
        } else {
            echo HTML::input_field('state', null, 'id="inputState" placeholder="' . OSCOM::get_def('entry_state_text') . '"');
        }
    } else {
        echo HTML::input_field('state', null, 'id="inputState" placeholder="' . OSCOM::get_def('entry_state_text') . '"');
    }
    ?>
      </div>
    </div>

<?php 
}
?>

    <div class="form-group">
      <label for="inputCountry" class="control-label col-sm-3"><?php 
echo OSCOM::get_def('entry_country');
?></label>
      <div class="col-sm-9">
        <?php 
echo tep_get_country_list('country', STORE_COUNTRY, 'aria-describedby="atCountry" id="inputCountry"');
if (tep_not_null(OSCOM::get_def('entry_country_text'))) {
    echo '<span id="atCountry" class="help-block">' . OSCOM::get_def('entry_country_text') . '</span>';
}
?>
      </div>
    </div>
</div>
