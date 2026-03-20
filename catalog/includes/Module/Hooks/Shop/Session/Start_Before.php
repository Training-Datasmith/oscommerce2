<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM\Module\Hooks\Shop\Session;

use OSC\OM\OSCOM;
class Start_Before
{
    public function execute(array $parameters): void
    {
        if (SESSION_BLOCK_SPIDERS == 'True') {
            $user_agent = '';
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = strtolower((string) $_SERVER['HTTP_USER_AGENT']);
            }
            if (!empty($user_agent)) {
                foreach (file(OSCOM::get_config('dir_root') . 'includes/spiders.txt') as $spider) {
                    if (empty($spider)) {
                        continue;
                    }
                    if (!str_contains($user_agent, $spider)) {
                        continue;
                    }
                    $parameters['can_start'] = false;
                    break;
                }
            }
        }
    }
}