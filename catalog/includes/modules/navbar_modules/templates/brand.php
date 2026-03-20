<?php

// in a template so that shopowners
// don't have to change the main file!
use OSC\OM\OSCOM;
?>

<?php 
echo OSCOM::get_def('module_navbar_brand_public_text', ['store_url' => OSCOM::link('index.php'), 'store_name' => STORE_NAME]);