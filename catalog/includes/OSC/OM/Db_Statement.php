<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Db_Statement extends \PDOStatement
{
    protected $pdo;
    protected $is_error = false;
    protected $page_set_keyword = 'page';
    protected $page_set;
    protected $page_set_results_per_page;
    protected $cache;
    protected $cache_expire;
    protected $cache_data;
    protected $cache_read = false;
    protected $cache_empty_results = false;
    protected $query_call;
    public function bind_value($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        return parent::bind_value($parameter, $value, $data_type);
    }
    public function bind_int($parameter, $value)
    {
        // force type to int (see http://bugs.php.net/bug.php?id=44639)
        return $this->bind_value($parameter, (int) $value, \PDO::PARAM_INT);
    }
    public function bind_bool($parameter, $value)
    {
        // force type to bool (see http://bugs.php.net/bug.php?id=44639)
        return $this->bind_value($parameter, (bool) $value, \PDO::PARAM_BOOL);
    }
    public function bind_decimal($parameter, $value)
    {
        return $this->bind_value($parameter, (float) $value);
        // there is no \PDO::PARAM_FLOAT
    }
    public function bind_null($parameter)
    {
        return $this->bind_value($parameter, null, \PDO::PARAM_NULL);
    }
    public function set_page_set($max_results, $page_set_keyword = null, string $placeholder_offset = 'page_set_offset', string $placeholder_max_results = 'page_set_max_results'): void
    {
        if (!empty($page_set_keyword)) {
            $this->page_set_keyword = $page_set_keyword;
        }
        $this->page_set = isset($_GET[$this->page_set_keyword]) && is_numeric($_GET[$this->page_set_keyword]) && $_GET[$this->page_set_keyword] > 0 ? $_GET[$this->page_set_keyword] : 1;
        $this->page_set_results_per_page = $max_results;
        $offset = max($this->page_set * $max_results - $max_results, 0);
        $this->bind_int(':' . $placeholder_offset, $offset);
        $this->bind_int(':' . $placeholder_max_results, $max_results);
    }
    public function execute($input_parameters = null): void
    {
        if (isset($this->cache)) {
            if (isset($this->page_set)) {
                $this->cache->set_key($this->cache->get_key() . '-pageset' . $this->page_set);
            }
            if ($this->cache->exists($this->cache_expire)) {
                $this->cache_data = $this->cache->get();
                if (isset($this->cache_data['data']) && isset($this->cache_data['total'])) {
                    $this->page_set_total_rows = $this->cache_data['total'];
                    $this->cache_data = $this->cache_data['data'];
                }
                $this->cache_read = true;
            }
        }
        if ($this->cache_read === false) {
            if (empty($input_parameters)) {
                $input_parameters = null;
            }
            $this->is_error = !parent::execute($input_parameters);
            if ($this->is_error === true) {
                trigger_error($this->query_string);
            }
            if (str_contains($this->query_string, ' SQL_CALC_FOUND_ROWS ')) {
                $this->page_set_total_rows = $this->pdo->query('select found_rows()')->fetch_column();
            } elseif (isset($this->page_set)) {
                trigger_error('OSC\OM\DbStatement::execute(): Page Set query does not contain SQL_CALC_FOUND_ROWS. Please add it to the query: ' . $this->query_string);
            }
        }
    }
    public function fetch($fetch_style = \PDO::FETCH_ASSOC, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        if ($this->cache_read === true) {
            $this->result = current($this->cache_data);
            next($this->cache_data);
        } else {
            $this->result = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
            if (isset($this->cache) && $this->result !== false) {
                if (!isset($this->cache_data)) {
                    $this->cache_data = [];
                }
                $this->cache_data[] = $this->result;
            }
        }
        return $this->result;
    }
    public function fetch_all($fetch_style = \PDO::FETCH_ASSOC, $fetch_argument = null, $ctor_args = [])
    {
        if ($this->cache_read === true) {
            $this->result = $this->cache_data;
        } else {
            // fetchAll() fails if second argument is passed in a fetch style that does not
            // use the optional argument
            if (in_array($fetch_style, [\PDO::FETCH_COLUMN, \PDO::FETCH_CLASS, \PDO::FETCH_FUNC])) {
                $this->result = parent::fetch_all($fetch_style, $fetch_argument, $ctor_args);
            } else {
                $this->result = parent::fetch_all($fetch_style);
            }
            if (isset($this->cache) && $this->result !== false) {
                $this->cache_data = $this->result;
            }
        }
        return $this->result;
    }
    public function check(): bool
    {
        if (!isset($this->result)) {
            $this->fetch();
        }
        return $this->result !== false;
    }
    public function to_array()
    {
        if (!isset($this->result)) {
            $this->fetch();
        }
        return $this->result;
    }
    public function set_cache(string $key, $expire = null, $cache_empty_results = false): void
    {
        if (!is_numeric($expire)) {
            $expire = 0;
        }
        if (!is_bool($cache_empty_results)) {
            $cache_empty_results = false;
        }
        $this->cache = new Cache($key);
        $this->cache_expire = $expire;
        $this->cache_empty_results = $cache_empty_results;
        if ($this->query_call != 'prepare') {
            trigger_error('OSC\OM\DbStatement::setCache(): Cannot set cache (\'' . $key . '\') on a non-prepare query. Please change the query to a prepare() query.');
        }
    }
    protected function value_mixed($column, $type = 'string')
    {
        if (!isset($this->result)) {
            $this->fetch();
        }
        return match ($type) {
            'protected' => HTML::output_protected($this->result[$column]),
            'int' => (int) $this->result[$column],
            'decimal' => (float) $this->result[$column],
            default => $this->result[$column],
        };
    }
    public function value($column)
    {
        return $this->value_mixed($column, 'string');
    }
    public function value_protected($column)
    {
        return $this->value_mixed($column, 'protected');
    }
    public function value_int($column)
    {
        return $this->value_mixed($column, 'int');
    }
    public function value_decimal($column)
    {
        return $this->value_mixed($column, 'decimal');
    }
    public function has_value($column): bool
    {
        if (!isset($this->result)) {
            $this->fetch();
        }
        return isset($this->result[$column]);
    }
    public function is_error()
    {
        return $this->is_error;
    }
    public function get_query(): string
    {
        return $this->query_string;
    }
    public function set_query_call($type): void
    {
        $this->query_call = $type;
    }
    public function get_query_call()
    {
        return $this->query_call;
    }
    public function get_current_page_set()
    {
        return $this->page_set;
    }
    public function get_page_set_results_per_page()
    {
        return $this->page_set_results_per_page;
    }
    public function get_page_set_total_rows()
    {
        return $this->page_set_total_rows;
    }
    public function set_pdo(\PDO $instance): void
    {
        $this->pdo = $instance;
    }
    public function get_page_set_label($text): string
    {
        if ($this->page_set_total_rows < 1) {
            $from = 0;
        } else {
            $from = max($this->page_set * $this->page_set_results_per_page - $this->page_set_results_per_page, 1);
        }
        $to = min($this->page_set * $this->page_set_results_per_page, $this->page_set_total_rows);
        if ($to > $this->page_set_results_per_page) {
            $from++;
        }
        return '<span class="pagination">' . Language::parse_definition($text, ['listing_from' => $from, 'listing_to' => $to, 'listing_total' => $this->page_set_total_rows]) . '</span>';
    }
    public function get_page_set_links($parameters = null): string
    {
        global $PHP_SELF;
        $number_of_pages = ceil($this->page_set_total_rows / $this->page_set_results_per_page);
        if (empty($parameters)) {
            $parameters = '';
        }
        if (!empty($parameters)) {
            parse_str($parameters, $p);
            if (isset($p[$this->page_set_keyword])) {
                unset($p[$this->page_set_keyword]);
            }
            $parameters = !empty($p) ? http_build_query($p) . '&' : '';
        }
        $pages = [];
        for ($i = 1; $i <= $number_of_pages; $i++) {
            $pages[] = ['id' => $i, 'text' => $i];
        }
        $output = '<ul class="pagination">';
        if ($number_of_pages > 1) {
            $output .= '<li>' . HTML::select_field('pageset' . $this->page_set_keyword, $pages, $this->page_set, 'style="vertical-align: top; display: inline-block; float: left; width: 80px;" data-pageseturl="' . HTML::output(OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=PAGESETGOTO')) . '"') . '</li>';
        } else {
            $output .= '<li class="disabled"><a class="text-center" style="width: 80px;">1</a></li>';
        }
        // previous button
        if ($this->page_set > 1) {
            $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . ($this->page_set - 1)) . '" title="' . OSCOM::get_def('prevnext_title_previous_page') . '" class="text-center" style="width: 80px;"><span class="fa fa-fw fa-chevron-left"></span></a></li>';
        } else {
            $output .= '<li class="disabled"><a class="text-center" style="width: 80px;"><span class="fa fa-fw fa-chevron-left"></span></a></li>';
        }
        // next button
        if ($this->page_set < $number_of_pages && $number_of_pages != 1) {
            $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . ($this->page_set + 1)) . '" title="' . OSCOM::get_def('prevnext_title_next_page') . '" class="text-center" style="width: 80px;"><span class="fa fa-fw fa-chevron-right"></span></a></li>';
        } else {
            $output .= '<li class="disabled"><a class="text-center" style="width: 80px;"><span class="fa fa-fw fa-chevron-right"></span></a></li>';
        }
        $output .= '</ul>';
        if ($number_of_pages > 1) {
            $output .= <<<EOD
            <script>
            \$(function() {
              \$('select[name="pageset{$this->page_set_keyword}"]').on('change', function() {
                window.location = \$(this).data('pageseturl').replace('PAGESETGOTO', \$(this).children(':selected').val());
              });
            });
            </script>
            EOD;
        }
        return $output;
    }
    public function __destruct()
    {
        if ($this->cache_read === false && isset($this->cache) && is_array($this->cache_data)) {
            if ($this->cache_empty_results || isset($this->cache_data[0]) && $this->cache_data[0] !== false) {
                $cache_data = $this->cache_data;
                if (isset($this->page_set_total_rows)) {
                    $cache_data = ['data' => $cache_data, 'total' => $this->page_set_total_rows];
                }
                $this->cache->save($cache_data);
            }
        }
    }
}