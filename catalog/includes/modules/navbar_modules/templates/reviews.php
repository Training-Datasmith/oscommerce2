<?php

// in a template so that shopowners
// don't have to change the main file!
use OSC\OM\OSCOM;
?>

<?php 
echo OSCOM::get_def('module_navbar_reviews_public_text', ['reviews_url' => OSCOM::link('reviews.php')]);