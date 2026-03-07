<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\OSCOM;

$admin_menu['shop']['catalog']['categories'] = OSCOM::link('categories.php');

$cl_box_groups[] = [
  'heading' => OSCOM::getDef('box_heading_catalog'),
  'apps' => [
    [
      'code' => FILENAME_PRODUCTS_ATTRIBUTES,
      'title' => OSCOM::getDef('box_catalog_categories_products_attributes'),
      'link' => OSCOM::link(FILENAME_PRODUCTS_ATTRIBUTES),
    ],
    [
      'code' => FILENAME_MANUFACTURERS,
      'title' => OSCOM::getDef('box_catalog_manufacturers'),
      'link' => OSCOM::link(FILENAME_MANUFACTURERS),
    ],
    [
      'code' => FILENAME_REVIEWS,
      'title' => OSCOM::getDef('box_catalog_reviews'),
      'link' => OSCOM::link(FILENAME_REVIEWS),
    ],
    [
      'code' => FILENAME_SPECIALS,
      'title' => OSCOM::getDef('box_catalog_specials'),
      'link' => OSCOM::link(FILENAME_SPECIALS),
    ],
    [
      'code' => FILENAME_PRODUCTS_EXPECTED,
      'title' => OSCOM::getDef('box_catalog_products_expected'),
      'link' => OSCOM::link(FILENAME_PRODUCTS_EXPECTED),
    ],
  ],
];
