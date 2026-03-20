<?php

use OSC\OM\OSCOM;
?>
<!DOCTYPE html>
<html <?php 
echo OSCOM::get_def('html_params');
?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php 
echo OSCOM::get_def('charset');
?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php 
echo OSCOM::get_def('title', ['store_name' => STORE_NAME]);
?></title>
<base href="<?php 
echo OSCOM::get_config('http_server', 'Admin') . OSCOM::get_config('http_path', 'Admin');
?>" />
<meta name="generator" content="osCommerce Online Merchant" />
<link rel="stylesheet" type="text/css" href="<?php 
echo OSCOM::link('Shop/ext/jquery/ui/redmond/jquery-ui-1.11.4.min.css', '', false);
?>">
<script type="text/javascript" src="<?php 
echo OSCOM::link('Shop/ext/jquery/jquery-3.1.1.min.js', '', false);
?>"></script>
<script type="text/javascript" src="<?php 
echo OSCOM::link('Shop/ext/jquery/ui/jquery-ui-1.11.4.min.js', '', false);
?>"></script>

<link href="<?php 
echo OSCOM::link('Shop/ext/bootstrap/css/bootstrap.min.css', '', false);
?>" rel="stylesheet">
<link href="<?php 
echo OSCOM::link('Shop/ext/font-awesome/4.7.0/css/font-awesome.min.css', '', false);
?>" rel="stylesheet">
<link href="<?php 
echo OSCOM::link('Shop/ext/smartmenus/jquery.smartmenus.bootstrap.css', '', false);
?>" rel="stylesheet">
<link href="<?php 
echo OSCOM::link('Shop/ext/chartist/chartist.min.css', '', false);
?>" rel="stylesheet">

<?php 
if (tep_not_null(OSCOM::get_def('jquery_datepicker_i18n_code'))) {
    ?>
<script type="text/javascript" src="<?php 
    echo OSCOM::link('Shop/ext/jquery/ui/i18n/datepicker-' . OSCOM::get_def('jquery_datepicker_i18n_code') . '.js', '', false);
    ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php 
    echo OSCOM::get_def('jquery_datepicker_i18n_code');
    ?>']);
</script>
<?php 
}
?>

<link rel="stylesheet" type="text/css" href="<?php 
echo $osc_template->get_public_file('css/stylesheet.css');
?>">
<script src="<?php 
echo OSCOM::link_public('js/general.js');
?>"></script>
</head>
<body>

<?php 
require $osc_template->get_file('header.php');
?>

<div id="contentText" class="container-fluid">
