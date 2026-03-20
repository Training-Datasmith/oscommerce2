<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class Security_Check_Extended_ext_directory_listing
{
    public $type = 'warning';
    public $has_doc = true;
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->lang->load_definitions('modules/security_check/extended/ext_directory_listing');
        $this->title = OSCOM::get_def('module_security_check_extended_ext_directory_listing_title');
    }
    public function pass(): bool
    {
        $request = $this->get_http_request(OSCOM::link('Shop/ext/'));
        return $request['http_code'] != 200;
    }
    public function get_message()
    {
        return OSCOM::get_def('module_security_check_extended_ext_directory_listing_http_200', ['ext_url' => OSCOM::link('Shop/ext/'), 'ext_path' => OSCOM::get_config('http_path', 'Shop') . 'ext/']);
    }
    public function get_http_request($url)
    {
        $server = parse_url((string) $url);
        if (isset($server['port']) === false) {
            $server['port'] = $server['scheme'] == 'https' ? 443 : 80;
        }
        if (isset($server['path']) === false) {
            $server['path'] = '/';
        }
        $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
        curl_setopt($curl, CURLOPT_PORT, $server['port']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return $info;
    }
}