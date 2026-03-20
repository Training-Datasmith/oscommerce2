<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\File_System;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
/**
 * @return mixed[]
 */
function tep_opendir($path): array
{
    $path = rtrim((string) $path, '/') . '/';
    $exclude_array = ['.', '..', '.DS_Store', 'Thumbs.db'];
    $result = [];
    if ($handle = opendir($path)) {
        while (false !== $filename = readdir($handle)) {
            if (!in_array($filename, $exclude_array)) {
                $file = ['name' => $path . $filename, 'is_dir' => is_dir($path . $filename), 'writable' => File_System::is_writable($path . $filename)];
                $result[] = $file;
                if ($file['is_dir'] == true) {
                    $result = array_merge($result, tep_opendir($path . $filename));
                }
            }
        }
        closedir($handle);
    }
    return $result;
}
$whitelist_array = [];
$Qwhitelist = $OSCOM_Db->get('sec_directory_whitelist', 'directory');
while ($Qwhitelist->fetch()) {
    $whitelist_array[] = $Qwhitelist->value('directory');
}
$admin_dir = basename((string) OSCOM::get_config('dir_root'));
if ($admin_dir != 'admin') {
    for ($i = 0, $n = sizeof($whitelist_array); $i < $n; $i++) {
        if (str_starts_with((string) $whitelist_array[$i], 'admin/')) {
            $whitelist_array[$i] = $admin_dir . substr((string) $whitelist_array[$i], 5);
        }
    }
}
require $osc_template->get_file('template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php 
echo OSCOM::get_def('heading_title');
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php 
echo OSCOM::get_def('table_heading_directories');
?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
echo OSCOM::get_def('table_heading_writable');
?></td>
                <td class="dataTableHeadingContent" align="center"><?php 
echo OSCOM::get_def('table_heading_recommended');
?></td>
              </tr>
<?php 
foreach (tep_opendir(OSCOM::get_config('dir_root', 'Shop')) as $file) {
    if ($file['is_dir']) {
        ?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php 
        echo substr((string) $file['name'], strlen((string) OSCOM::get_config('dir_root', 'Shop')));
        ?></td>
                <td class="dataTableContent" align="center"><?php 
        echo HTML::image(OSCOM::link_image('icons/' . ($file['writable'] == true ? 'tick.gif' : 'cross.gif')));
        ?></td>
                <td class="dataTableContent" align="center"><?php 
        echo HTML::image(OSCOM::link_image('icons/' . (in_array(substr((string) $file['name'], strlen((string) OSCOM::get_config('dir_root', 'Shop'))), $whitelist_array) ? 'tick.gif' : 'cross.gif')));
        ?></td>
              </tr>
<?php 
    }
}
?>
              <tr>
                <td colspan="3" class="smallText"><?php 
echo OSCOM::get_def('text_directory') . ' ' . OSCOM::get_config('dir_root', 'Shop');
?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table>

<?php 
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';