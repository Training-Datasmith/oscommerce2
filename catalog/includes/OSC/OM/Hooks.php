<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Hooks
{
    protected string $site;
    protected $hooks = [];
    protected $watches = [];
    public function __construct($site = null)
    {
        if (!isset($site)) {
            $site = OSCOM::get_site();
        }
        $this->site = basename((string) $site);
    }
    /**
     * @return mixed[]
     */
    public function call($group, $hook, $parameters = null, $action = null): array
    {
        if (!isset($action)) {
            $action = 'execute';
        }
        if (!isset($this->hooks[$this->site][$group][$hook][$action])) {
            $this->register($group, $hook, $action);
        }
        $calls = [];
        if (isset($this->hooks[$this->site][$group][$hook][$action])) {
            $calls = $this->hooks[$this->site][$group][$hook][$action];
        }
        if (isset($this->watches[$this->site][$group][$hook][$action])) {
            $calls = array_merge($calls, $this->watches[$this->site][$group][$hook][$action]);
        }
        $result = [];
        foreach ($calls as $code) {
            $bait = null;
            if (is_string($code)) {
                $class = Apps::get_module_class($code, 'Hooks');
                $obj = new $class();
                $bait = $obj->{$action}($parameters);
            } else {
                $ref = new \ReflectionFunction($code);
                if ($ref->is_closure()) {
                    $bait = $code($parameters);
                }
            }
            if (!empty($bait)) {
                $result[] = $bait;
            }
        }
        return $result;
    }
    public function output(): string
    {
        return implode('', call_user_func_array($this->call(...), func_get_args()));
    }
    public function watch($group, $hook, $action, $code): void
    {
        $this->watches[$this->site][$group][$hook][$action][] = $code;
    }
    protected function register($group, string $hook, $action)
    {
        $group = basename((string) $group);
        $this->hooks[$this->site][$group][$hook][$action] = [];
        $directory = OSCOM::get_config('dir_root', 'Shop') . 'includes/Module/Hooks/' . $this->site . '/' . $group;
        if (is_dir($directory)) {
            if ($dir = new \Directory_Iterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->is_dot() && !$file->is_dir() && $file->get_extension() == 'php' && $file->get_basename('.php') == $hook) {
                        $class = 'OSC\OM\Module\Hooks\\' . $this->site . '\\' . $group . '\\' . $hook;
                        if (method_exists($class, $action)) {
                            $this->hooks[$this->site][$group][$hook][$action][] = $class;
                        }
                    }
                }
            }
        }
        $filter = ['site' => $this->site, 'group' => $group, 'hook' => $hook];
        foreach (Apps::get_modules('Hooks', null, $filter) as $k => $class) {
            if (method_exists($class, $action)) {
                $this->hooks[$this->site][$group][$hook][$action][] = $k;
            }
        }
    }
}