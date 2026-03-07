<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Sites\Admin;

use OSC\OM\HTML;

abstract class ConfigParamAbstract
{
    protected string $code;
    protected $key_prefix;
    protected string $key;
    public $title;
    public $description;
    public $default;
    public $sort_order = 0;

    abstract protected function init();

    public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->key = $this->key_prefix . $this->code;

        $this->init();
    }

    protected function getInputValue()
    {
        $key = strtoupper((string) $this->key);
        $value = defined($key) ? constant($key) : null;

        if (!isset($value) && isset($this->default)) {
            return $this->default;
        }

        return $value;
    }

    public function getInputField()
    {
        return HTML::inputField($this->key, $this->getInputValue());
    }

    public function getSetField()
    {
        $input = $this->getInputField();

        return <<<EOT
<div class="row">
  <h4>{$this->title}</h4>

  <p>{$this->description}</p>

  <div>
    {$input}
  </div>
</div>
EOT;
    }
}
