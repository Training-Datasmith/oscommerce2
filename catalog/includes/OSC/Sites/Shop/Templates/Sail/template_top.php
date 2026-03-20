<?php

use OSC\OM\HTML;
use OSC\OM\OSCOM;
$osc_template->build_blocks();
if (!$osc_template->has_blocks('boxes_column_left')) {
    $osc_template->set_grid_content_width($osc_template->get_grid_content_width() + $osc_template->get_grid_column_width());
}
if (!$osc_template->has_blocks('boxes_column_right')) {
    $osc_template->set_grid_content_width($osc_template->get_grid_content_width() + $osc_template->get_grid_column_width());
}
?>
<!DOCTYPE html>
<html <?php 
echo OSCOM::get_def('html_params');
?>>
<head>
<meta charset="<?php 
echo OSCOM::get_def('charset');
?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php 
echo HTML::output_protected($osc_template->get_title());
?></title>
<base href="<?php 
echo OSCOM::get_config('http_server', 'Shop') . OSCOM::get_config('http_path', 'Shop');
?>">

<link href="ext/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- font awesome -->
<link href="ext/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

<link href="<?php 
echo $osc_template->get_public_file('css/custom.css');
?>" rel="stylesheet">
<link href="<?php 
echo $osc_template->get_public_file('css/user.css');
?>" rel="stylesheet">

<!--[if lt IE 9]>
   <script src="ext/js/html5shiv.js"></script>
   <script src="ext/js/respond.min.js"></script>
   <script src="ext/js/excanvas.min.js"></script>
<![endif]-->

<script src="ext/jquery/jquery-3.1.1.min.js"></script>

<?php 
echo $osc_template->get_blocks('header_tags');
?>
</head>
<body>

  <?php 
echo $osc_template->get_content('navigation');
?>

  <div id="bodyWrapper" class="<?php 
echo BOOTSTRAP_CONTAINER;
?>">
    <div class="row">

      <?php 
require $osc_template->get_file('header.php');
?>

      <div id="bodyContent" class="col-md-<?php 
echo $osc_template->get_grid_content_width();
?> <?php 
echo $osc_template->has_blocks('boxes_column_left') ? 'col-md-push-' . $osc_template->get_grid_column_width() : '';
?>">
