<?php

// in a template so that shopowners
// don't have to change the main file!
use OSC\OM\OSCOM;
?>

<?php 
echo OSCOM::get_def('module_navbar_special_offers_public_text', ['specials_url' => OSCOM::link('specials.php')]);