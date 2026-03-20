<?php

use OSC\OM\Error_Handler;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
?>

<div class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php 
echo OSCOM::link(FILENAME_DEFAULT);
?>"><i class="fa fa-home"></i></a>
    </div>

<?php 
if (isset($_SESSION['admin'])) {
    ?>

    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li><a><?php 
    echo OSCOM::get_def('admin_menu_shop');
    ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">

<?php 
    foreach ($admin_menu['shop'] as $group => $links) {
        echo '<li><a>' . HTML::output_protected(OSCOM::get_def('admin_menu_shop_' . $group)) . ' <span class="caret"></span></a>
              <ul class="dropdown-menu">';
        foreach ($links as $code => $page) {
            echo '<li><a href="' . (is_string($page) ? $page : $page['link']) . '">' . HTML::output_protected(OSCOM::get_def('admin_menu_shop_' . $group . '_' . $code)) . '</a></li>';
        }
        echo '  </ul>
            </li>';
    }
    ?>

          </ul>
        </li>

        <li><a><?php 
    echo OSCOM::get_def('admin_menu_apps');
    ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php 
    echo OSCOM::link('apps.php');
    ?>">Manage</a></li>

<?php 
    if (!empty($cl_apps_groups)) {
        echo '<li class="divider"></li>';
        foreach ($cl_apps_groups as $groups) {
            echo '<li><a>' . HTML::output_protected($groups['heading']) . ' <span class="caret"></span></a>
                <ul class="dropdown-menu">';
            foreach ($groups['apps'] as $app) {
                echo '<li><a href="' . $app['link'] . '">' . HTML::output_protected($app['title']) . '</a></li>';
            }
            echo '  </ul>
              </li>';
        }
    }
    ?>

          </ul>
        </li>
        <li><a><?php 
    echo OSCOM::get_def('admin_menu_legacy');
    ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">

<?php 
    foreach ($cl_box_groups as $groups) {
        echo '<li><a>' . HTML::output_protected($groups['heading']) . ' <span class="caret"></span></a>
              <ul class="dropdown-menu">';
        foreach ($groups['apps'] as $app) {
            echo '<li><a href="' . $app['link'] . '">' . HTML::output_protected($app['title']) . '</a></li>';
        }
        echo '  </ul>
            </li>';
    }
    ?>

          </ul>
        </li>

<?php 
    if (count(glob(Error_Handler::get_directory() . 'errors-*.txt')) > 0) {
        ?>

        <li><a href="<?php 
        echo OSCOM::link('error_log.php');
        ?>"><i class="fa fa-exclamation-circle text-danger"></i></a>
          <ul class="dropdown-menu">
            <li><a href="<?php 
        echo OSCOM::link('error_log.php');
        ?>">View Error Log</a></li>
          </ul>
        </li>

<?php 
    }
    ?>

      </ul>

<?php 
}
?>

      <ul class="nav navbar-nav navbar-right">

<?php 
if (isset($_SESSION['admin'])) {
    ?>

        <li><a><?php 
    echo HTML::output_protected($_SESSION['admin']['username']);
    ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php 
    echo OSCOM::link(FILENAME_LOGIN, 'action=logoff');
    ?>">Logoff</a></li>
          </ul>
        </li>

<?php 
}
$all_get = tep_get_all_get_params('language');
$lang = [];
foreach (tep_get_languages() as $l) {
    $lang[] = ['name' => $l['name'], 'link' => OSCOM::link($PHP_SELF, $all_get . (!empty($all_get) ? '&' : '') . 'language=' . $l['code'])];
}
if (count($lang) > 1) {
    ?>

        <li><a><i class="fa fa-language"></i></a>
          <ul class="dropdown-menu">

<?php 
    foreach ($lang as $l) {
        echo '<li><a href="' . $l['link'] . '">' . HTML::output_protected($l['name']) . '</a></li>';
    }
    ?>

          </ul>
        </li>

<?php 
}
?>

        <li><a><i class="fa fa-question-circle"></i></a>
          <ul class="dropdown-menu">
            <li><a href="<?php 
echo OSCOM::link('Shop/');
?>">View Shop</a></li>
            <li class="divider"></li>
            <li><a href="https://www.oscommerce.com">osCommerce Website</a></li>
            <li><a href="https://www.oscommerce.com/Support">Help and Support</a></li>
            <li><a href="https://library.oscommerce.com">Documentation</a></li>
            <li><a href="http://forums.oscommerce.com">Community Forum</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>

<?php 
if (Registry::get('MessageStack')->exists('main')) {
    echo Registry::get('MessageStack')->get('main');
}