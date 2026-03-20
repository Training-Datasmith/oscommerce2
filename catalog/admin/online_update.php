<?php

/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Cache;
use OSC\OM\DateTime;
use OSC\OM\File_System;
use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\Online_Update;
use OSC\OM\OSCOM;
require 'includes/application_top.php';
$current_version = OSCOM::get_version();
preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', (string) $current_version, $version);
$major_version = (int) $version[1];
$minor_version = (int) $version[2];
$inc_version = (int) $version[3];
$version_cache = new Cache('core_version_check');
if ($version_cache->exists(360)) {
    $releases = $version_cache->get();
} else {
    $releases = HTTP::get_response(['url' => 'https://www.oscommerce.com/version/online_merchant/' . $major_version . $minor_version]);
    if (!empty($releases)) {
        $releases = explode("\n", trim($releases));
        if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)\|[0-9]{8}$/', $releases[0]) === 1) {
            usort($releases, function ($a, $b): bool {
                $aa = explode('|', (string) $a);
                $ba = explode('|', (string) $b);
                return version_compare($aa[0], $ba[0], '>');
            });
            $version_cache->save($releases);
        } else {
            $releases = -1;
        }
    }
}
$versions = [];
if (is_array($releases) && !empty($releases)) {
    foreach ($releases as $version) {
        $version_array = explode('|', (string) $version);
        if (version_compare($current_version, $version_array[0], '<')) {
            $versions[] = ['version' => $version_array[0], 'date' => DateTime::to_long(substr($version_array[1], 0, 4) . '-' . substr($version_array[1], 4, 2) . '-' . substr($version_array[1], 6, 2))];
        }
    }
}
$action = $_GET['action'] ?? '';
if (tep_not_null($action)) {
    switch ($action) {
        case 'getUpdateLog':
            $check = false;
            if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', (string) $_POST['version'])) {
                foreach ($versions as $v) {
                    if ($v['version'] == $_POST['version']) {
                        $check = true;
                        break;
                    }
                }
            }
            if ($check !== true) {
                trigger_error('Online Update: Retrievel of update log for requested v' . $_POST['version'] . ' is not valid.');
                http_response_code(404);
                exit;
            }
            $result = ['result' => -1];
            if (Online_Update::log_exists($_POST['version'])) {
                $result['result'] = 1;
                $result['log'] = Online_Update::get_log($_POST['version']);
                $result['path'] = Online_Update::get_log_path($_POST['version']);
            }
            echo json_encode($result);
            exit;
        case 'getReleaseNotes':
            $check = false;
            if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', (string) $_POST['version'])) {
                foreach ($versions as $v) {
                    if ($v['version'] == $_POST['version']) {
                        $check = true;
                        break;
                    }
                }
            }
            if ($check !== true) {
                trigger_error('Online Update: Retrievel of Release Notes for requested v' . $_POST['version'] . ' is not valid.');
                http_response_code(404);
                exit;
            }
            $version = str_replace('.', '_', $_POST['version']);
            $release_notes_cache = new Cache('online_update-rel_notes-' . $version);
            if ($release_notes_cache->exists()) {
                $notes = $release_notes_cache->get();
            } else {
                $notes = HTTP::get_response(['url' => 'https://www.oscommerce.com/version/online_merchant/notes/' . $_POST['version'] . '.txt']);
                $notes = trim($notes);
                if (!empty($notes)) {
                    $release_notes_cache->save($notes);
                }
            }
            echo $notes;
            exit;
        case 'downloadRelease':
            $check = false;
            if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', (string) $_POST['version'])) {
                foreach ($versions as $v) {
                    if ($v['version'] == $_POST['version']) {
                        $check = true;
                        break;
                    }
                }
            }
            if ($check !== true) {
                trigger_error('Online Update: Download for requested v' . $_POST['version'] . ' update package is not valid.');
                http_response_code(404);
                exit;
            }
            $result = ['result' => -1];
            if (File_System::is_writable(OSCOM::BASE_DIR . 'Work/OnlineUpdates', true)) {
                if (!is_dir(OSCOM::BASE_DIR . 'Work/OnlineUpdates')) {
                    mkdir(OSCOM::BASE_DIR . 'Work/OnlineUpdates', 0777, true);
                }
                $filepath = OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $_POST['version'] . '-update.zip';
                if (File_System::is_writable($filepath)) {
                    unlink($filepath);
                }
                $download_file = HTTP::get_response(['url' => 'https://www.oscommerce.com/?Products&Download=oscom-' . $_POST['version'] . '-ou', 'method' => 'post']);
                $save_result = file_put_contents($filepath, $download_file);
                if ($save_result !== false && $save_result > 0) {
                    $result['result'] = 1;
                } else {
                    $result['result'] = -3;
                    $result['path'] = File_System::display_path($filepath);
                }
            } else {
                $result['result'] = -2;
                $result['path'] = File_System::display_path(OSCOM::BASE_DIR . 'Work/OnlineUpdates');
            }
            echo json_encode($result);
            exit;
        case 'applyRelease':
            $check = false;
            if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', (string) $_POST['version'])) {
                foreach ($versions as $v) {
                    if ($v['version'] == $_POST['version']) {
                        $check = true;
                        break;
                    }
                }
            }
            if ($check !== true) {
                trigger_error('Online Update: Processing for requested v' . $_POST['version'] . ' update package is not valid.');
                http_response_code(404);
                exit;
            }
            $result = ['result' => -1];
            // reset the log
            Online_Update::reset_log($_POST['version']);
            Online_Update::log('Starting update', $_POST['version']);
            try {
                if (!is_file(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey')) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The following required public key cannot be found:' . "\n\n" . File_System::display_path(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey'));
                }
                if (!File_System::is_writable(OSCOM::BASE_DIR . 'version.txt')) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The following file cannot be written to - please check the file permissions: ' . "\n\n" . File_System::display_path(OSCOM::BASE_DIR . 'version.txt'));
                }
                $update_zip = OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $_POST['version'] . '-update.zip';
                if (!is_file($update_zip)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The following downloaded update package could not be found:' . "\n\n" . File_System::display_path($update_zip));
                }
                $work_dir = OSCOM::BASE_DIR . 'Work/OnlineUpdates/update_contents';
                if (is_dir($work_dir)) {
                    Online_Update::log('Cleaning work directory', $_POST['version']);
                    $errors = [];
                    foreach (File_System::rmdir($work_dir) as $wd) {
                        if ($wd['result'] !== true) {
                            $errors[] = File_System::display_path($wd['source']);
                        }
                    }
                    if (!empty($errors)) {
                        throw new \Exception('### ERROR ###' . "\n" . 'Could not clean the following files and directories from the work directory:' . "\n\n" . implode("\n", $errors));
                    }
                }
                if (!mkdir($work_dir, 0777, true)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not create the following work directory:' . "\n\n" . File_System::display_path($work_dir));
                }
                if (!File_System::is_writable($work_dir)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following work directory:' . "\n\n" . File_System::display_path($work_dir));
                }
                Online_Update::log('Extracting downloaded update package', $_POST['version']);
                try {
                    $zip = new \Zip_Archive();
                    if ($zip->open($update_zip) === true) {
                        $zip->extract_to($work_dir);
                        $zip->close();
                    }
                } catch (\Exception $e) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not extract the following downloaded update package:' . "\n\n" . File_System::display_path($update_zip) . "\n\n" . 'to the following work directory:' . "\n\n" . File_System::display_path($work_dir));
                }
                unset($zip);
                Online_Update::log('Verifying downloaded update package', $_POST['version']);
                $update_pkg = $work_dir . '/' . $_POST['version'] . '.zip';
                if (!is_file($update_pkg) || !is_file($update_pkg . '.sig')) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The following downloaded update package does not seem to be a valid update package:' . "\n\n" . File_System::display_path($update_zip));
                }
                $public = openssl_get_publickey(file_get_contents(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey'));
                if (openssl_verify(sha1_file($update_pkg), file_get_contents($update_pkg . '.sig'), $public) !== 1) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not verify the following downloaded update package:' . "\n\n" . File_System::display_path($update_zip));
                }
                if (!unlink($update_zip)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not delete the following downloaded update package:' . "\n\n" . File_System::display_path($update_zip));
                }
                mkdir($work_dir . '/' . $_POST['version'], 0777, true);
                Online_Update::log('Extracting downloaded update package files', $_POST['version']);
                try {
                    $zip = new \Zip_Archive();
                    if ($zip->open($update_pkg) === true) {
                        $zip->extract_to($work_dir . '/' . $_POST['version']);
                        $zip->close();
                    }
                } catch (\Exception $e) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not extract the files of the following update package:' . "\n\n" . File_System::display_path($update_pkg) . "\n\n" . 'to the following work directory:' . "\n\n" . File_System::display_path($work_dir . '/' . $_POST['version']));
                }
                unset($zip);
                unlink($update_pkg);
                Online_Update::log('Verifying update package meta file', $_POST['version']);
                $meta = [];
                if (!is_file($work_dir . '/' . $_POST['version'] . '/oscommerce.json')) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The oscommerce.json meta file could not be found in the following update package:' . "\n\n" . File_System::display_path($update_pkg));
                }
                $meta = json_decode(file_get_contents($work_dir . '/' . $_POST['version'] . '/oscommerce.json'), true);
                if (!is_array($meta) || empty($meta)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The oscommerce.json meta file in the following update package seems to be corrupt:' . "\n\n" . File_System::display_path($update_pkg));
                }
                if (!isset($meta['version']) || $meta['version'] != $_POST['version']) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The update package version does not match the requested update version. Update Package version: ' . $meta['version'] . '; Requested version: ' . $_POST['version']);
                }
                if (!isset($meta['version_req']) || $meta['version_req'] != $current_version) {
                    throw new \Exception('### ERROR ###' . "\n" . 'The update package version does not match the required current version of osCommerce Online Merchant. Current version: ' . $current_version . '; Update Package required version: ' . $meta['version_req']);
                }
                Online_Update::log('Verifying file and directory permissions', $_POST['version']);
                $errors = [];
                $update_pkg_contents = File_System::get_directory_contents($work_dir . '/' . $_POST['version']);
                foreach ($update_pkg_contents as $file) {
                    $pathname = substr((string) $file, strlen($work_dir . '/' . $_POST['version'] . '/'));
                    $file_source = null;
                    if (str_starts_with($pathname, 'catalog/')) {
                        $file_source = OSCOM::get_config('dir_root', 'Shop') . substr($pathname, 8);
                    } elseif (str_starts_with($pathname, 'admin/')) {
                        $file_source = OSCOM::get_config('dir_root') . substr($pathname, 6);
                    }
                    if (isset($file_source)) {
                        // check if target and target directory are writable
                        if (!File_System::is_writable($file_source, true) || !File_System::is_writable(dirname($file_source), true)) {
                            $errors[] = File_System::display_path($file_source);
                        }
                    }
                }
                $to_del = [];
                if (is_file($work_dir . '/' . $_POST['version'] . '/delete.txt')) {
                    $to_del = explode("\n", trim(file_get_contents($work_dir . '/' . $_POST['version'] . '/delete.txt')));
                    foreach ($to_del as $d) {
                        $file_source = null;
                        if (str_starts_with($d, 'catalog/')) {
                            $file_source = OSCOM::get_config('dir_root', 'Shop') . substr($d, 8);
                        } elseif (str_starts_with($d, 'admin/')) {
                            $file_source = OSCOM::get_config('dir_root') . substr($d, 6);
                        }
                        if (isset($file_source)) {
                            if (file_exists($file_source)) {
                                if (is_dir($file_source)) {
                                    foreach (File_System::get_directory_contents($file_source) as $dr) {
                                        if (!File_System::is_writable($dr, true) || !File_System::is_writable(dirname((string) $dr), true)) {
                                            $errors[] = File_System::display_path($dr);
                                        }
                                    }
                                }
                                if (!File_System::is_writable($file_source, true) || !File_System::is_writable(dirname($file_source), true)) {
                                    $errors[] = File_System::display_path($file_source);
                                }
                            }
                        }
                    }
                }
                if (!empty($errors)) {
                    throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following files and directories - please check their file permissions:' . "\n\n" . implode("\n", $errors));
                }
                Online_Update::log('Starting the update process', $_POST['version']);
                $OU = null;
                if (is_file($work_dir . '/' . $_POST['version'] . '/Update.php')) {
                    include $work_dir . '/' . $_POST['version'] . '/Update.php';
                    $OU = new OSC\OM\Online_Update\Update();
                    if ($OU->version != $meta['version']) {
                        throw new \Exception('### ERROR ###' . "\n" . 'Update class version does not match update package version. Update Package version: ' . $meta['version'] . '; Update Class version: ' . $OU->version);
                    }
                }
                if (isset($OU) && $OU instanceof \OSC\OM\Online_Update\Update && method_exists($OU, 'runBefore')) {
                    Online_Update::log('Executing update package runBefore()', $_POST['version']);
                    $OU->run_before();
                }
                foreach ($update_pkg_contents as $file) {
                    $pathname = substr((string) $file, strlen($work_dir . '/' . $_POST['version'] . '/'));
                    $file_source = null;
                    if (str_starts_with($pathname, 'catalog/')) {
                        $file_source = OSCOM::get_config('dir_root', 'Shop') . substr($pathname, 8);
                    } elseif (str_starts_with($pathname, 'admin/')) {
                        $file_source = OSCOM::get_config('dir_root') . substr($pathname, 6);
                    }
                    if (isset($file_source)) {
                        $target = dirname($file_source);
                        if (!is_dir($target)) {
                            mkdir($target, 0777, true);
                            Online_Update::log('+ CREATED: ' . File_System::display_path($target), $_POST['version']);
                        }
                        $action = is_file($file_source) ? 'UPDATED' : 'ADDED';
                        if (copy($file, $file_source)) {
                            Online_Update::log('+ ' . $action . ': ' . File_System::display_path($file_source), $_POST['version']);
                        } else {
                            throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following file: ' . File_System::display_path($file_source));
                        }
                    }
                }
                foreach ($to_del as $d) {
                    $file_source = null;
                    if (str_starts_with($d, 'catalog/')) {
                        $file_source = OSCOM::get_config('dir_root', 'Shop') . substr($d, 8);
                    } elseif (str_starts_with($d, 'admin/')) {
                        $file_source = OSCOM::get_config('dir_root') . substr($d, 6);
                    }
                    if (isset($file_source)) {
                        if (file_exists($file_source)) {
                            if (is_dir($file_source)) {
                                foreach (File_System::rmdir($file_source) as $delresult) {
                                    if ($delresult['result'] === true) {
                                        Online_Update::log('- DELETED: ' . File_System::display_path($delresult['source']), $_POST['version']);
                                    } else {
                                        Online_Update::log('--- DELETE ERROR: Could not delete the following file or directory: ' . File_System::display_path($delresult['source']), $_POST['version']);
                                    }
                                }
                            } else if (unlink($file_source)) {
                                Online_Update::log('- DELETED: ' . File_System::display_path($file_source), $_POST['version']);
                            } else {
                                Online_Update::log('--- DELETE ERROR: Could not delete the following file: ' . File_System::display_path($file_source), $_POST['version']);
                            }
                        }
                    }
                }
                if (isset($OU) && $OU instanceof \OSC\OM\Online_Update\Update && method_exists($OU, 'runAfter')) {
                    Online_Update::log('Executing update package runAfter()', $_POST['version']);
                    $OU->run_after();
                }
                if (file_put_contents(OSCOM::BASE_DIR . 'version.txt', $_POST['version'])) {
                    Online_Update::log('+ UPDATED: ' . File_System::display_path(OSCOM::BASE_DIR . 'version.txt'), $_POST['version']);
                } else {
                    Online_Update::log('+++ UPDATE ERROR: Could not update the following file: ' . File_System::display_path(OSCOM::BASE_DIR . 'version.txt'), $_POST['version']);
                }
                Online_Update::log('Finished update', $_POST['version']);
                $result['result'] = 1;
                File_System::rmdir($work_dir);
            } catch (\Exception $e) {
                Online_Update::log($e->get_message(), $_POST['version']);
            }
            echo json_encode($result);
            exit;
    }
}
$new_version = [];
if (is_array($releases) && !empty($releases)) {
    if (!empty($versions)) {
        $new_version = array_slice($versions, -1)[0];
    }
    if (!empty($new_version)) {
        $oscom_message_stack->add(OSCOM::get_def('version_upgrades_available', ['version' => $new_version['version']]), 'warning', 'versionCheck');
    } else {
        $oscom_message_stack->add(OSCOM::get_def('version_running_latest'), 'success', 'versionCheck');
    }
} else {
    $oscom_message_stack->add(OSCOM::get_def('error_could_not_connect'), 'error', 'versionCheck');
}
require $osc_template->get_file('template_top.php');
?>

<h2><i class="fa fa-cloud-download"></i> <a href="<?php 
echo OSCOM::link('online_update.php');
?>"><?php 
echo OSCOM::get_def('heading_title');
?></a></h2>

<div id="onlineUpdateContentBlock">
  <p><?php 
echo OSCOM::get_def('title_installed_version') . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>';
?></p>

  <?php 
echo $oscom_message_stack->get('versionCheck');
?>

<?php 
if (!empty($new_version)) {
    ?>

  <?php 
    echo HTML::button('Start Update Procedure', 'fa fa-cloud-download', null, ['params' => 'id="updateStartButton"'], 'btn-success');
    ?>

  <div id="updateProgressBar" class="progress hide">
    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;"></div>
  </div>
</div>

<div id="onlineUpdateSuccessBlock" class="hidden">

<?php 
    $heading = $contents = [];
    $heading[] = ['text' => 'Success!'];
    $contents[] = ['text' => 'osCommerce Online Merchant has been successfully updated to the latest version!'];
    echo HTML::panel($heading, $contents, ['type' => 'success']);
    ?>

</div>

<h3>Releases</h3>

<div id="releasePanels" class="panel-group"></div>

<script id="templateReleasePanel" type="x-tmpl-mustache">

<?php 
    $heading = $contents = [];
    $heading[] = ['text' => 'v{{version}} ({{date}})'];
    $contents[] = ['text' => ''];
    echo HTML::panel($heading, $contents, ['type' => 'info']);
    ?>

</script>

<script>
$(function() {
  var versions = <?php 
    echo json_encode($versions);
    ?>;

  var showVersionBlock = function(version) {
    var upDivId = 'up' + version.replace(/\./g, '\\\.');

    $('#' + upDivId + ' .panel-body .row').html('<i class="fa fa-refresh fa-spin fa-fw"></i>');

    $.post('<?php 
    echo OSCOM::link('online_update.php', 'action=getReleaseNotes');
    ?>', {version: version}, function(data) {
      data = $('<div>').text(data).html().replace(/\n/g, '<br />');

      data = anchorme.js(data, {
        'attributes': {
          'target': '_blank'
        }
      });

      if ($('#' + upDivId).hasClass('panel-danger')) {
        $('#' + upDivId).removeClass('panel-danger').addClass('panel-info');
      }

      $('#' + upDivId + ' .panel-body .row').html(data);
      $('#' + upDivId + ' .panel-body .row').prepend('<a href="https://www.oscommerce.com/?RPC&GotoReleaseAnnouncement&v=oscom-' + version + '-ou" class="pull-right btn btn-info btn-sm" target="_blank"><i class="fa fa-external-link fa-fw"></i> View Online</a>');
    }).fail(function() {
      $('#' + upDivId + ' .panel-body .row').html('Error: Could not retrieve release notes for this version. <a data-action="showVersionBlock" class="btn btn-danger btn-sm"><i class="fa fa-refresh"></i> Retry</a>');

      $('#' + upDivId + ' .panel-body .row a[data-action="showVersionBlock"]').on('click', function() {
        showVersionBlock(version);
      });

      if ($('#' + upDivId).hasClass('panel-info')) {
        $('#' + upDivId).removeClass('panel-info').addClass('panel-danger');
      }
    });
  };

  var templateReleasePanel = $('#templateReleasePanel').html();
  Mustache.parse(templateReleasePanel);

  $(versions).each(function (k, v) {
    var panel = $.parseHTML(Mustache.render(templateReleasePanel, v));

    $(panel).attr('id', 'up' + v.version);

    $(panel).appendTo('#releasePanels');

    showVersionBlock(v.version);
  });

  $('#updateStartButton').on('click', function() {
    var updateError = false;
    var total = $('#releasePanels .panel').length;
    var each_percent = Math.round(100 / total);
    var counter = 0;

    $('#updateStartButton').hide();

    $('#updateProgressBar').removeClass('hide');

    $('#releasePanels .panel .panel-body').hide();

    (function() {
      var runQueueInOrder = function(i) {
        if (updateError == true) {
          return false;
        }

        if (i >= versions.length) {
          $('#onlineUpdateContentBlock').hide();

          $('#onlineUpdateSuccessBlock').removeClass('hidden');

          return true;
        }

        var upDivId = 'up' + versions[i].version.replace(/\./g, '\\\.');

        $('#' + upDivId).removeClass('panel-info').addClass('panel-primary');
        $('#' + upDivId + ' .panel-heading').prepend('<i data-icon="status" class="fa fa-refresh fa-spin fa-fw pull-right"></i>');
        $('#' + upDivId + ' .panel-body .row').html('Downloading..');
        $('#' + upDivId + ' .panel-body').show();

        $.post('<?php 
    echo addslashes((string) OSCOM::link('online_update.php', 'action=downloadRelease'));
    ?>', {version: versions[i].version}, function(data) {
          if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
            $('#' + upDivId + ' .panel-body .row').html('Applying..');

            $.post('<?php 
    echo addslashes((string) OSCOM::link('online_update.php', 'action=applyRelease'));
    ?>', {version: versions[i].version}, function(data) {
              if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
                $('#' + upDivId + ' .panel-body').hide();
                $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-check');
                $('#' + upDivId).removeClass('panel-primary').addClass('panel-success');

                var counter = i + 1;

                var progress_value = each_percent * counter;
                $('#updateProgressBar .progress-bar').css('width', progress_value + '%').attr('aria-valuenow', progress_value);
              } else {
                updateError = true;

                $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
                $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
                $('#' + upDivId + ' .panel-body .row').html('Error!');

                $.post('<?php 
    echo OSCOM::link('online_update.php', 'action=getUpdateLog');
    ?>', {version: versions[i].version}, function(data) {
                  if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
                    var log = $('<div>').text(data.log).html().replace(/\n/g, '<br />');

                    $('#' + upDivId + ' .panel-body .row').append('<br /><br />The following log can be found at:<br /><br />' + data.path + '<br /><br />' + log);
                  }
                }, 'json');
              }
            }, 'json').fail(function() {
              updateError = true;

              $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
              $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
              $('#' + upDivId + ' .panel-body .row').html('Error!<br /><br />Could not start the procedure to apply the update package. Please try again.');
            }).then(function() {
              i++;

              runQueueInOrder(i);
            });
          } else {
            updateError = true;

            var error_msg = 'Error!';

            if ((typeof data == 'object') && ('result' in data)) {
              if (data.result === -2) {
                error_msg = error_msg + '<br /><br />Cannot download the online update package. Please check the file permissions of the following directory:<br /><br />' + data.path;
              } else if (data.result === -3) {
                error_msg = error_msg + '<br /><br />Cannot save the online update package. Please check the file permissions of the following file:<br /><br />' + data.path;
              }
            }

            $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
            $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
            $('#' + upDivId + ' .panel-body .row').html(error_msg);
          }
        }, 'json').fail(function() {
          updateError = true;

          $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
          $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
          $('#' + upDivId + ' .panel-body .row').html('Error!<br /><br />Could not connect to the osCommerce Website to download the update package. Please try again.');
        });
      }

      runQueueInOrder(0);
    })();
  });
});
</script>

<script src="<?php 
    echo OSCOM::link('Shop/ext/anchorme/anchorme.min.js');
    ?>"></script>

<?php 
}
require $osc_template->get_file('template_bottom.php');
require 'includes/application_bottom.php';