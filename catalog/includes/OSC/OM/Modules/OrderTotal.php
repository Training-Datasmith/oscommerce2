<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Modules;

use OSC\OM\Apps;
class Order_Total extends \OSC\OM\Modules_Abstract
{
    /**
     * @return class-string[]
     */
    public function get_info($app, $key, $data): array
    {
        $result = [];
        $class = $this->ns . $app . '\\' . $data;
        if (is_subclass_of($class, 'OSC\OM\Modules\\' . $this->code . 'Interface')) {
            $result[$app . '\\' . $key] = $class;
        }
        return $result;
    }
    public function get_class($module)
    {
        [$vendor, $app, $code] = explode('\\', (string) $module, 3);
        $info = Apps::get_info($vendor . '\\' . $app);
        if (isset($info['modules'][$this->code][$code])) {
            return $this->ns . $vendor . '\\' . $app . '\\' . $info['modules'][$this->code][$code];
        }
    }
}