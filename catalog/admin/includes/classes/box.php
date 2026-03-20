<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
class box extends Table_Block
{
    public function __construct()
    {
        $this->heading = [];
        $this->contents = [];
    }
    public function info_box($heading, $contents): string
    {
        $this->table_row_parameters = 'class="infoBoxHeading"';
        $this->table_data_parameters = 'class="infoBoxHeading"';
        $this->heading = $this->table_block($heading);
        $this->table_row_parameters = '';
        $this->table_data_parameters = 'class="infoBoxContent"';
        $this->contents = $this->table_block($contents);
        return $this->heading . $this->contents;
    }
    public function menu_box($heading, $contents): string
    {
        $this->table_data_parameters = 'class="menuBoxHeading"';
        if (isset($heading[0]['link'])) {
            $this->table_data_parameters .= ' onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"';
            $heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
        } else {
            $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
        }
        $this->heading = $this->table_block($heading);
        $this->table_data_parameters = 'class="menuBoxContent"';
        $this->contents = !empty($contents) ? $this->table_block($contents) : '';
        return $this->heading . $this->contents;
    }
}