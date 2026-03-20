<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Apps;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$hooks = [];
$directory = OSCOM::get_config('dir_root', 'Shop') . 'includes/Module/Hooks/';
if (is_dir($directory)) {
    if ($dir = new \Directory_Iterator($directory)) {
        foreach ($dir as $file) {
            if (!$file->is_dot() && $file->is_dir()) {
                $site = $file->get_basename();
                if ($sitedir = new \Directory_Iterator($directory . $site)) {
                    foreach ($sitedir as $groupfile) {
                        if (!$groupfile->is_dot() && $groupfile->is_dir()) {
                            $group = $groupfile->get_basename();
                            if ($groupdir = new \Directory_Iterator($directory . $site . '/' . $group)) {
                                foreach ($groupdir as $hookfile) {
                                    if (!$hookfile->is_dot() && !$hookfile->is_dir() && $hookfile->get_extension() == 'php') {
                                        $hook = $hookfile->get_basename('.php');
                                        $class = 'OSC\OM\Module\Hooks\\' . $site . '\\' . $group . '\\' . $hook;
                                        $h = new \ReflectionClass($class);
                                        foreach ($h->get_methods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
                                            if ($method->name != '__construct') {
                                                $hooks[$site . '/' . $group . '\\' . $hook][] = ['method' => $method->name];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
foreach (Apps::get_modules('Hooks') as $k => $v) {
    [$vendor, $app, $code] = explode('\\', (string) $k, 3);
    $h = new \ReflectionClass($v);
    foreach ($h->get_methods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->name != '__construct') {
            $hooks[$code][] = ['app' => $vendor . '\\' . $app, 'method' => $method->name];
        }
    }
}
require $osc_template->get_file('template_top.php');
?>

<style>
.sitePill {
  color: #fff;
  background-color: #009933;
  border-radius: 20px;
  padding: 5px 10px;
}

.appPill {
  color: #fff;
  background-color: #0066CC;
  border-radius: 20px;
  padding: 5px 10px;
}
</style>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="pageHeading"><?php 
echo OSCOM::get_def('heading_title');
?></td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php 
foreach ($hooks as $code => $data) {
    $counter = 0;
    foreach ($data as $v) {
        $counter++;
        [$site, $group] = explode('/', $code, 2);
        ?>

  <tr class="dataTableRow">

<?php 
        if ($counter === 1) {
            ?>

    <td class="dataTableContent" style="padding: 10px;" <?php 
            if (count($data) > 1) {
                echo 'rowspan="' . count($data) . '"';
            }
            ?>><?php 
            echo '<span class="sitePill">' . $site . '</span> ' . $group;
            ?></td>

<?php 
        }
        ?>

    <td class="dataTableContent" style="padding: 10px;"><?php 
        echo (isset($v['app']) ? '<span class="appPill">' . $v['app'] . '</span> ' : '') . $v['method'];
        ?></td>
  </tr>

<?php 
    }
}
?>

</table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';