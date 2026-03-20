<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
class Alert_Block
{
    public function __construct($contents, $alert_output = false)
    {
        $alert_box_string = '';
        for ($i = 0, $n = sizeof($contents); $i < $n; $i++) {
            $alert_box_string .= '  <div';
            if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                $alert_box_string .= ' ' . $contents[$i]['params'];
            }
            $alert_box_string .= '>' . "\n";
            $alert_box_string .= '	<button type="button" class="close" data-dismiss="alert">&times;</button>' . "\n";
            $alert_box_string .= $contents[$i]['text'];
            $alert_box_string .= '  </div>' . "\n";
        }
        if ($alert_output == true) {
            echo $alert_box_string;
        }
        return $alert_box_string;
    }
}