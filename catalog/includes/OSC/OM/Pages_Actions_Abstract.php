<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

abstract class Pages_Actions_Abstract implements \OSC\OM\Pages_Actions_Interface
{
    protected $file;
    protected $is_rpc = false;
    public function __construct(protected \OSC\OM\Pages_Interface $page)
    {
        if (isset($this->file)) {
            $this->page->set_file($this->file);
        }
    }
    public function is_rpc()
    {
        return $this->is_rpc === true;
    }
}