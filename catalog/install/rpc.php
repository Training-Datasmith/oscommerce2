<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Db;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
require 'includes/application.php';
$dir_fs_www_root = __DIR__;
$result = ['status' => '-100', 'message' => 'noActionError'];
if (isset($_GET['action']) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
        case 'httpsCheck':
            if (isset($_GET['subaction']) && $_GET['subaction'] == 'do') {
                if (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) == 'on' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
                    $result['status'] = '1';
                    $result['message'] = 'success';
                }
            } else {
                $url = 'https://' . $_SERVER['HTTP_HOST'];
                if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
                    $url .= $_SERVER['REQUEST_URI'];
                } else {
                    $url .= $_SERVER['SCRIPT_FILENAME'];
                }
                $url .= '&subaction=do';
                // errors are silenced to not log failed connection checks
                $response = @HTTP::get_response(['url' => $url, 'verify_ssl' => false]);
                if (!empty($response)) {
                    $response = json_decode((string) $response, true);
                    if (is_array($response) && isset($response['status']) && $response['status'] == '1') {
                        $result['status'] = '1';
                        $result['message'] = 'success';
                    }
                }
            }
            break;
        case 'dbCheck':
            try {
                $OSCOM_Db = Db::initialize($_POST['server'] ?? '', $_POST['username'] ?? '', $_POST['password'] ?? '', $_POST['name'] ?? '', null, null, ['log_errors' => false]);
                $result['status'] = '1';
                $result['message'] = 'success';
            } catch (\Exception $e) {
                $result['status'] = $e->get_code();
                $result['message'] = $e->get_message();
                if ($e->get_code() == '1049' && isset($_GET['createDb']) && $_GET['createDb'] == 'true') {
                    try {
                        $OSCOM_Db = Db::initialize($_POST['server'], $_POST['username'], $_POST['password'], '', null, null, ['log_errors' => false]);
                        $OSCOM_Db->exec('create database ' . Db::prepare_identifier($_POST['name']) . ' character set utf8 collate utf8_unicode_ci');
                        $result['status'] = '1';
                        $result['message'] = 'success';
                    } catch (\Exception $e2) {
                        $result['status'] = $e2->get_code();
                        $result['message'] = $e2->get_message();
                    }
                }
            }
            break;
        case 'dbImport':
            try {
                $OSCOM_Db = Db::initialize($_POST['server'] ?? '', $_POST['username'] ?? '', $_POST['password'] ?? '', $_POST['name'] ?? '');
                $OSCOM_Db->set_table_prefix('');
                $OSCOM_Db->exec('SET FOREIGN_KEY_CHECKS = 0');
                foreach (glob(OSCOM::BASE_DIR . 'Schema/*.txt') as $f) {
                    $schema = $OSCOM_Db->get_schema_from_file($f);
                    $sql = $OSCOM_Db->get_sql_from_schema($schema, $_POST['prefix']);
                    $OSCOM_Db->exec('DROP TABLE IF EXISTS ' . $_POST['prefix'] . basename($f, '.txt'));
                    $OSCOM_Db->exec($sql);
                }
                $OSCOM_Db->import_sql($dir_fs_www_root . '/oscommerce.sql', $_POST['prefix']);
                $OSCOM_Db->exec('SET FOREIGN_KEY_CHECKS = 1');
                $result['status'] = '1';
                $result['message'] = 'success';
            } catch (\Exception $e) {
                $result['status'] = $e->get_code();
                $result['message'] = $e->get_message();
            }
            break;
    }
}
echo json_encode($result);