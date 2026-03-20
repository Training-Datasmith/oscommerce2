<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
class Table_Block
{
    public $table_border = '0';
    public $table_width = '100%';
    public $table_cellspacing = '0';
    public $table_cellpadding = '2';
    public $table_parameters = '';
    public $table_row_parameters = '';
    public $table_data_parameters = '';
    public function __construct(array $contents)
    {
        $table_box_string = '';
        $form_set = false;
        if (isset($contents['form'])) {
            $table_box_string .= $contents['form'] . "\n";
            $form_set = true;
            array_shift($contents);
        }
        $table_box_string .= '<table border="' . $this->table_border . '" width="' . $this->table_width . '" cellspacing="' . $this->table_cellspacing . '" cellpadding="' . $this->table_cellpadding . '"';
        if (tep_not_null($this->table_parameters)) {
            $table_box_string .= ' ' . $this->table_parameters;
        }
        $table_box_string .= '>' . "\n";
        for ($i = 0, $n = sizeof($contents); $i < $n; $i++) {
            $table_box_string .= '  <tr';
            if (tep_not_null($this->table_row_parameters)) {
                $table_box_string .= ' ' . $this->table_row_parameters;
            }
            if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                $table_box_string .= ' ' . $contents[$i]['params'];
            }
            $table_box_string .= '>' . "\n";
            if (isset($contents[$i][0]) && is_array($contents[$i][0])) {
                for ($x = 0, $y = sizeof($contents[$i]); $x < $y; $x++) {
                    if (isset($contents[$i][$x]['text']) && tep_not_null($contents[$i][$x]['text'])) {
                        $table_box_string .= '    <td';
                        if (isset($contents[$i][$x]['align']) && tep_not_null($contents[$i][$x]['align'])) {
                            $table_box_string .= ' align="' . $contents[$i][$x]['align'] . '"';
                        }
                        if (isset($contents[$i][$x]['params']) && tep_not_null($contents[$i][$x]['params'])) {
                            $table_box_string .= ' ' . $contents[$i][$x]['params'];
                        } elseif (tep_not_null($this->table_data_parameters)) {
                            $table_box_string .= ' ' . $this->table_data_parameters;
                        }
                        $table_box_string .= '>';
                        if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) {
                            $table_box_string .= $contents[$i][$x]['form'];
                        }
                        $table_box_string .= $contents[$i][$x]['text'];
                        if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) {
                            $table_box_string .= '</form>';
                        }
                        $table_box_string .= '</td>' . "\n";
                    }
                }
            } else {
                $table_box_string .= '    <td';
                if (isset($contents[$i]['align']) && tep_not_null($contents[$i]['align'])) {
                    $table_box_string .= ' align="' . $contents[$i]['align'] . '"';
                }
                if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                    $table_box_string .= ' ' . $contents[$i]['params'];
                } elseif (tep_not_null($this->table_data_parameters)) {
                    $table_box_string .= ' ' . $this->table_data_parameters;
                }
                $table_box_string .= '>' . $contents[$i]['text'] . '</td>' . "\n";
            }
            $table_box_string .= '  </tr>' . "\n";
        }
        $table_box_string .= '</table>' . "\n";
        if ($form_set == true) {
            $table_box_string .= '</form>' . "\n";
        }
        return $table_box_string;
    }
}