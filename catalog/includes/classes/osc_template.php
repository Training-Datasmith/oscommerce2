<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
use OSC\OM\Apps;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
class Osc_Template
{
    public $_title;
    public $_code = 'Sail';
    public $_blocks = [];
    public $_content = [];
    public $_grid_container_width = 12;
    public $_grid_content_width = BOOTSTRAP_CONTENT;
    public $_grid_column_width = 0;
    // deprecated
    public $_data = [];
    protected $lang;
    public function __construct()
    {
        $this->lang = Registry::get('Language');
        $this->_title = OSCOM::get_def('title', ['store_name' => STORE_NAME]);
        $this->add_block('<meta name="generator" content="osCommerce Online Merchant" />', 'header_tags');
    }
    public function set_grid_container_width($width): void
    {
        $this->_grid_container_width = $width;
    }
    public function get_grid_container_width()
    {
        return $this->_grid_container_width;
    }
    public function set_grid_content_width($width): void
    {
        $this->_grid_content_width = $width;
    }
    public function get_grid_content_width()
    {
        return $this->_grid_content_width;
    }
    public function set_grid_column_width($width): void
    {
        $this->_grid_column_width = $width;
    }
    public function get_grid_column_width(): int|float
    {
        return (12 - BOOTSTRAP_CONTENT) / 2;
    }
    public function set_title($title): void
    {
        $this->_title = $title;
    }
    public function get_title()
    {
        return $this->_title;
    }
    public function set_code($code): void
    {
        $this->_code = $code;
    }
    public function get_code()
    {
        return $this->_code;
    }
    public function add_block($block, $group): void
    {
        $this->_blocks[$group][] = $block;
    }
    public function has_blocks($group): bool
    {
        return isset($this->_blocks[$group]) && !empty($this->_blocks[$group]);
    }
    public function get_blocks($group)
    {
        if ($this->has_blocks($group)) {
            return implode("\n", $this->_blocks[$group]);
        }
    }
    public function build_blocks(): void
    {
        if (defined('TEMPLATE_BLOCK_GROUPS') && tep_not_null(TEMPLATE_BLOCK_GROUPS)) {
            $tbgroups_array = explode(';', (string) TEMPLATE_BLOCK_GROUPS);
            foreach ($tbgroups_array as $group) {
                $module_key = 'MODULE_' . strtoupper($group) . '_INSTALLED';
                if (defined($module_key) && tep_not_null(constant($module_key))) {
                    $modules_array = explode(';', (string) constant($module_key));
                    foreach ($modules_array as $module) {
                        $class = basename($module, '.php');
                        if (!class_exists($class)) {
                            if ($this->lang->definitions_exist('modules/' . $group . '/' . pathinfo($module, PATHINFO_FILENAME))) {
                                $this->lang->load_definitions('modules/' . $group . '/' . pathinfo($module, PATHINFO_FILENAME));
                            }
                            if (is_file('includes/modules/' . $group . '/' . $class . '.php')) {
                                include 'includes/modules/' . $group . '/' . $class . '.php';
                            }
                        }
                        if (class_exists($class)) {
                            $mb = new $class();
                            if ($mb->is_enabled()) {
                                $mb->execute();
                            }
                        }
                    }
                }
            }
        }
    }
    public function add_content($content, $group): void
    {
        $this->_content[$group][] = $content;
    }
    public function has_content($group): bool
    {
        return isset($this->_content[$group]) && !empty($this->_content[$group]);
    }
    public function get_content(string $group)
    {
        if (!class_exists('tp_' . $group) && is_file('includes/modules/pages/tp_' . $group . '.php')) {
            include 'includes/modules/pages/tp_' . $group . '.php';
        }
        if (class_exists('tp_' . $group)) {
            $template_page_class = 'tp_' . $group;
            $template_page = new $template_page_class();
            $template_page->prepare();
        }
        foreach ($this->get_content_modules($group) as $module) {
            if (str_contains((string) $module, '\\')) {
                $class = Apps::get_module_class($group . '/' . $module, 'Content');
                $mb = new $class();
                if ($mb->is_enabled()) {
                    $mb->execute();
                }
            } else {
                if (!class_exists($module)) {
                    if (is_file('includes/modules/content/' . $group . '/' . $module . '.php')) {
                        if ($this->lang->definitions_exist('modules/content/' . $group . '/' . $module)) {
                            $this->lang->load_definitions('modules/content/' . $group . '/' . $module);
                        }
                        include 'includes/modules/content/' . $group . '/' . $module . '.php';
                    }
                }
                if (class_exists($module)) {
                    $mb = new $module();
                    if ($mb->is_enabled()) {
                        $mb->execute();
                    }
                }
            }
        }
        if (class_exists('tp_' . $group)) {
            $template_page->build();
        }
        if ($this->has_content($group)) {
            return implode("\n", $this->_content[$group]);
        }
    }
    /**
     * @return string[]
     */
    public function get_content_modules($group): array
    {
        $result = [];
        foreach (explode(';', MODULE_CONTENT_INSTALLED) as $m) {
            $module = explode('/', $m, 2);
            if ($module[0] == $group) {
                $result[] = $module[1];
            }
        }
        return $result;
    }
    public function get_file(string $file, $template = null): string
    {
        if (!isset($template)) {
            $template = $this->get_code();
        }
        return OSCOM::BASE_DIR . 'Sites/' . OSCOM::get_site() . '/Templates/' . $template . '/' . $file;
    }
    public function get_public_file(string $file, $template = null)
    {
        if (!isset($template)) {
            $template = $this->get_code();
        }
        return OSCOM::link_public('Templates/' . $template . '/' . $file);
    }
}