<?php

// in a template so that shopowners
// don't have to change the main file!
use OSC\OM\OSCOM;
?>

<?php 
echo OSCOM::get_def('module_navbar_home_public_text', ['store_url' => OSCOM::link('index.php')]);