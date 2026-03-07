<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

class objectInfo
{
    // class constructor
    public function __construct($object_array)
    {
        foreach ($object_array as $key => $value) {
            $this->$key = $value;
        }
    }
}
