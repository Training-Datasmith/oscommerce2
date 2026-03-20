<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\OSCOM;
class cfgm_social_bookmarks
{
    public $code = 'social_bookmarks';
    /**
     * @var string
     */
    public $directory;
    /**
     * @var string
     */
    public $language_directory;
    public $site = 'Shop';
    public $key = 'MODULE_SOCIAL_BOOKMARKS_INSTALLED';
    public $title;
    public $template_integration = false;
    public function __construct()
    {
        $this->directory = OSCOM::get_config('dir_root', $this->site) . 'includes/modules/social_bookmarks/';
        $this->language_directory = OSCOM::get_config('dir_root', $this->site) . 'includes/languages/';
        $this->title = OSCOM::get_def('module_cfg_module_social_bookmarks_title');
    }
}