<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

abstract class Pages_Abstract implements \OSC\OM\Pages_Interface
{
    public $data = [];
    protected string $code;
    protected $file = 'main.php';
    protected $use_site_template = true;
    protected $actions_run = [];
    protected $ignored_actions = [];
    protected $is_rpc = false;
    protected $app;
    final public function __construct(protected \OSC\OM\Sites_Interface $site)
    {
        $this->code = (new \ReflectionClass($this))->get_short_name();
        $this->init();
    }
    protected function init()
    {
    }
    public function get_code()
    {
        return $this->code;
    }
    public function get_file()
    {
        if (isset($this->file)) {
            return dirname(OSCOM::BASE_DIR) . '/' . str_replace('\\', '/', (new \ReflectionClass($this))->get_namespace_name()) . '/templates/' . $this->file;
        }
    }
    public function use_site_template()
    {
        return $this->use_site_template;
    }
    public function set_file($file): void
    {
        $this->file = $file;
    }
    public function is_action_request()
    {
        $furious_pete = [];
        if (count($_GET) > $this->site->actions_index) {
            $furious_pete = array_keys(array_slice($_GET, $this->site->actions_index, null, true));
        }
        if (!empty($furious_pete)) {
            $action = HTML::sanitize(basename((string) $furious_pete[0]));
            if (!in_array($action, $this->ignored_actions) && $this->action_exists($action)) {
                return true;
            }
        }
        return false;
    }
    public function run_action($actions): void
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }
        $run = [];
        foreach ($actions as $action) {
            $run[] = $action;
            if ($this->action_exists($run)) {
                $this->actions_run[] = $action;
                $class = $this->get_action_class_name($run);
                $ns = explode('\\', $class);
                if (count($ns) > 2 && $ns[0] == 'OSC' && $ns[1] == 'Apps') {
                    if (isset($this->app) && is_subclass_of($this->app, \OSC\OM\App_Abstract::class)) {
                        if ($this->app->definitions_exist(implode('/', array_slice($ns, 4)))) {
                            $this->app->load_definitions(implode('/', array_slice($ns, 4)));
                        }
                    }
                }
                $action = new $class($this);
                $action->execute();
                if ($action->is_rpc()) {
                    $this->is_rpc = true;
                }
            } else {
                break;
            }
        }
    }
    public function run_actions(): void
    {
        $actions = $furious_pete = [];
        if (count($_GET) > $this->site->actions_index) {
            $furious_pete = array_keys(array_slice($_GET, $this->site->actions_index, null, true));
        }
        foreach ($furious_pete as $action) {
            $action = HTML::sanitize(basename((string) $action));
            $actions[] = $action;
            if (in_array($action, $this->ignored_actions) || !$this->action_exists($actions)) {
                array_pop($actions);
                break;
            }
        }
        if (!empty($actions)) {
            $this->run_action($actions);
        }
    }
    public function action_exists($action)
    {
        if (!is_array($action)) {
            $action = [$action];
        }
        $class = $this->get_action_class_name($action);
        if (class_exists($class)) {
            if (is_subclass_of($class, \OSC\OM\Pages_Actions_Interface::class)) {
                return true;
            }
            trigger_error('OSC\OM\PagesAbstract::actionExists() - ' . implode('\\', $action) . ': Action does not implement OSC\OM\PagesActionInterface and cannot be loaded.');
        }
        return false;
    }
    public function get_actions_run()
    {
        return $this->actions_run;
    }
    public function is_rpc()
    {
        return $this->is_rpc === true;
    }
    protected function get_action_class_name($action)
    {
        if (!is_array($action)) {
            $action = [$action];
        }
        return (new \ReflectionClass($this))->get_namespace_name() . '\Actions\\' . implode('\\', $action);
    }
}