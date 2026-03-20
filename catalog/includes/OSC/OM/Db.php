<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Db extends \PDO
{
    protected $connected = false;
    protected $server;
    protected $username;
    protected $password;
    protected $database;
    protected $table_prefix;
    protected $port;
    protected $driver_options = [];
    protected $options = [];
    public static function initialize($server = null, $username = null, $password = null, $database = null, $port = null, array $driver_options = null, array $options = null)
    {
        if (!isset($server)) {
            $server = OSCOM::get_config('db_server');
        }
        if (!isset($username)) {
            $username = OSCOM::get_config('db_server_username');
        }
        if (!isset($password)) {
            $password = OSCOM::get_config('db_server_password');
        }
        if (!isset($database)) {
            $database = OSCOM::get_config('db_database');
        }
        if (!is_array($driver_options)) {
            $driver_options = [];
        }
        if (!isset($driver_options[\PDO::ATTR_ERRMODE])) {
            $driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        }
        if (!isset($driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE])) {
            $driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        }
        if (!isset($driver_options[\PDO::ATTR_STATEMENT_CLASS])) {
            $driver_options[\PDO::ATTR_STATEMENT_CLASS] = [\OSC\OM\Db_Statement::class];
        }
        if (!is_array($options)) {
            $options = [];
        }
        $object = false;
        try {
            $class = \OSC\OM\Db\My_Sql::class;
            $object = new $class($server, $username, $password, $database, $port, $driver_options, $options);
        } catch (\Exception $e) {
            $message = $e->get_message();
            // $message .= "\n" . $e->getTraceAsString(); // the trace will contain the password in plain text
            if (!isset($options['log_errors']) || $options['log_errors'] === true) {
                error_log('OSC\OM\Db::initialize(): ' . $message);
            }
            throw new \Exception($message, $e->get_code());
        }
        return $object;
    }
    public function exec($statement)
    {
        $statement = $this->auto_prefix_tables($statement);
        return parent::exec($statement);
    }
    public function prepare($statement, $driver_options = null)
    {
        $statement = $this->auto_prefix_tables($statement);
        $db_statement = parent::prepare($statement, is_array($driver_options) ? $driver_options : []);
        $db_statement->set_query_call('prepare');
        $db_statement->set_pdo($this);
        return $db_statement;
    }
    public function query($statement)
    {
        $statement = $this->auto_prefix_tables($statement);
        $args = func_get_args();
        if (count($args) > 1) {
            $db_statement = call_user_func_array([$this, 'parent::query'], $args);
        } else {
            $db_statement = parent::query($statement);
        }
        if ($db_statement !== false) {
            $db_statement->set_query_call('query');
            $db_statement->set_pdo($this);
        }
        return $db_statement;
    }
    public function get($table, $fields, array $where = null, $order = null, $limit = null, $cache = null, array $options = null)
    {
        if (!is_array($table)) {
            $table = [$table];
        }
        if (!isset($options['prefix_tables']) || $options['prefix_tables'] === true) {
            array_walk($table, function (&$v, &$k): void {
                if (strlen($v) < 7 || !str_starts_with($v, ':table_')) {
                    $v = ':table_' . $v;
                }
            });
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if (isset($order) && !is_array($order)) {
            $order = [$order];
        }
        if (isset($limit)) {
            if (is_array($limit) && count($limit) === 2 && is_numeric($limit[0]) && is_numeric($limit[1])) {
                $limit = implode(', ', $limit);
            } elseif (!is_numeric($limit)) {
                $limit = null;
            }
        }
        $statement = 'select ' . implode(', ', $fields) . ' from ' . implode(', ', $table);
        if (!isset($where) && !isset($cache)) {
            if (isset($order)) {
                $statement .= ' order by ' . implode(', ', $order);
            }
            return $this->query($statement);
        }
        if (isset($where)) {
            $statement .= ' where ';
            $counter = 0;
            $it_where = new \Caching_Iterator(new \ArrayIterator($where), \Caching_Iterator::TOSTRING_USE_CURRENT);
            foreach ($it_where as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['val'])) {
                        $statement .= $key . ' ' . ($value['op'] ?? '=') . ' :cond_' . $counter;
                    }
                    if (isset($value['rel'])) {
                        if (isset($value['val'])) {
                            $statement .= ' and ';
                        }
                        if (is_array($value['rel'])) {
                            $it_rel = new \Caching_Iterator(new \ArrayIterator($value['rel']), \Caching_Iterator::TOSTRING_USE_CURRENT);
                            foreach ($it_rel as $rel) {
                                $statement .= $key . ' = ' . $rel;
                                if ($it_rel->has_next()) {
                                    $statement .= ' and ';
                                }
                            }
                        } else {
                            $statement .= $key . ' = ' . $value['rel'];
                        }
                    }
                } else {
                    $statement .= $key . ' = :cond_' . $counter;
                }
                if ($it_where->has_next()) {
                    $statement .= ' and ';
                }
                $counter++;
            }
        }
        if (isset($order)) {
            $statement .= ' order by ' . implode(', ', $order);
        }
        if (isset($limit)) {
            $statement .= ' limit ' . $limit;
        }
        $Q = $this->prepare($statement);
        if (isset($where)) {
            $counter = 0;
            foreach ($it_where as $value) {
                if (is_array($value)) {
                    if (isset($value['val'])) {
                        $Q->bind_value(':cond_' . $counter, $value['val']);
                    }
                } else {
                    $Q->bind_value(':cond_' . $counter, $value);
                }
                $counter++;
            }
        }
        if (isset($cache)) {
            if (!is_array($cache)) {
                $cache = [$cache];
            }
            call_user_func_array([$Q, 'setCache'], $cache);
        }
        $Q->execute();
        return $Q;
    }
    public function save($table, array $data, array $where_condition = null, array $options = null)
    {
        if (empty($data)) {
            return false;
        }
        if (!isset($options['prefix_tables']) || $options['prefix_tables'] === true) {
            if (strlen((string) $table) < 7 || !str_starts_with((string) $table, ':table_')) {
                $table = ':table_' . $table;
            }
        }
        if (isset($where_condition)) {
            $statement = 'update ' . $table . ' set ';
            foreach ($data as $c => $v) {
                if (is_null($v)) {
                    $v = 'null';
                }
                if ($v == 'now()' || $v == 'null') {
                    $statement .= $c . ' = ' . $v . ', ';
                } else {
                    $statement .= $c . ' = :new_' . $c . ', ';
                }
            }
            $statement = substr($statement, 0, -2) . ' where ';
            foreach (array_keys($where_condition) as $c) {
                $statement .= $c . ' = :cond_' . $c . ' and ';
            }
            $statement = substr($statement, 0, -5);
            $Q = $this->prepare($statement);
            foreach ($data as $c => $v) {
                if ($v != 'now()' && $v != 'null' && !is_null($v)) {
                    $Q->bind_value(':new_' . $c, $v);
                }
            }
            foreach ($where_condition as $c => $v) {
                $Q->bind_value(':cond_' . $c, $v);
            }
            $Q->execute();
            return $Q->row_count();
        }
        $is_prepared = false;
        $statement = 'insert into ' . $table . ' (' . implode(', ', array_keys($data)) . ') values (';
        foreach ($data as $c => $v) {
            if (is_null($v)) {
                $v = 'null';
            }
            if ($v == 'now()' || $v == 'null') {
                $statement .= $v . ', ';
            } else {
                if ($is_prepared === false) {
                    $is_prepared = true;
                }
                $statement .= ':' . $c . ', ';
            }
        }
        $statement = substr($statement, 0, -2) . ')';
        if ($is_prepared === true) {
            $Q = $this->prepare($statement);
            foreach ($data as $c => $v) {
                if ($v != 'now()' && $v != 'null' && !is_null($v)) {
                    $Q->bind_value(':' . $c, $v);
                }
            }
            $Q->execute();
            return $Q->row_count();
        }
        return $this->exec($statement);
    }
    public function delete($table, array $where_condition = [], array $options = null)
    {
        if (!isset($options['prefix_tables']) || $options['prefix_tables'] === true) {
            if (strlen((string) $table) < 7 || !str_starts_with((string) $table, ':table_')) {
                $table = ':table_' . $table;
            }
        }
        $statement = 'delete from ' . $table;
        if (empty($where_condition)) {
            return $this->exec($statement);
        }
        $statement .= ' where ';
        foreach (array_keys($where_condition) as $c) {
            $statement .= $c . ' = :cond_' . $c . ' and ';
        }
        $statement = substr($statement, 0, -5);
        $Q = $this->prepare($statement);
        foreach ($where_condition as $c => $v) {
            $Q->bind_value(':cond_' . $c, $v);
        }
        $Q->execute();
        return $Q->row_count();
    }
    public function import_sql(string $sql_file, $table_prefix = null)
    {
        if (is_file($sql_file)) {
            $import_queries = file_get_contents($sql_file);
        } else {
            trigger_error('OSC\OM\Db::importSQL(): SQL file does not exist: ' . $sql_file);
            return false;
        }
        set_time_limit(0);
        $sql_queries = [];
        $sql_length = strlen($import_queries);
        $pos = strpos($import_queries, ';');
        for ($i = $pos; $i < $sql_length; $i++) {
            // remove comments
            if (str_starts_with($import_queries, '#') || str_starts_with($import_queries, '--')) {
                $import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
                $sql_length = strlen($import_queries);
                $i = strpos($import_queries, ';') - 1;
                continue;
            }
            if (substr($import_queries, $i + 1, 1) == "\n") {
                $next = '';
                for ($j = $i + 2; $j < $sql_length; $j++) {
                    if (!empty(substr($import_queries, $j, 1))) {
                        $next = substr($import_queries, $j, 6);
                        if (str_starts_with($next, '#') || str_starts_with($next, '--')) {
                            // find out where the break position is so we can remove this line (#comment line)
                            for ($k = $j; $k < $sql_length; $k++) {
                                if (substr($import_queries, $k, 1) == "\n") {
                                    break;
                                }
                            }
                            $query = substr($import_queries, 0, $i + 1);
                            $import_queries = substr($import_queries, $k);
                            // join the query before the comment appeared, with the rest of the dump
                            $import_queries = $query . $import_queries;
                            $sql_length = strlen($import_queries);
                            $i = strpos($import_queries, ';') - 1;
                            continue 2;
                        }
                        break;
                    }
                }
                if (empty($next)) {
                    // get the last insert query
                    $next = 'insert';
                }
                if (strtoupper($next) == 'DROP T' || strtoupper($next) == 'CREATE' || strtoupper($next) == 'INSERT' || strtoupper($next) == 'ALTER ' || strtoupper($next) == 'SET FO') {
                    $next = '';
                    $sql_query = substr($import_queries, 0, $i);
                    if (isset($table_prefix) && !empty($table_prefix)) {
                        if (strtoupper(substr($sql_query, 0, 20)) == 'DROP TABLE IF EXISTS') {
                            $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 21);
                        } elseif (strtoupper(substr($sql_query, 0, 12)) == 'CREATE TABLE') {
                            $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 13);
                        } elseif (strtoupper(substr($sql_query, 0, 11)) == 'INSERT INTO') {
                            $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 12);
                        } elseif (strtoupper(substr($sql_query, 0, 12)) == 'CREATE INDEX') {
                            $sql_query = substr($sql_query, 0, stripos($sql_query, ' on ')) . ' on ' . $table_prefix . substr($sql_query, stripos($sql_query, ' on ') + 4);
                        }
                    }
                    $sql_queries[] = trim($sql_query);
                    $import_queries = ltrim(substr($import_queries, $i + 1));
                    $sql_length = strlen($import_queries);
                    $i = strpos($import_queries, ';') - 1;
                }
            }
        }
        $error = false;
        foreach ($sql_queries as $q) {
            if ($this->exec($q) === false) {
                $error = true;
                break;
            }
        }
        return !$error;
    }
    /**
     * @return mixed[]
     */
    public static function get_schema_from_file($file): array
    {
        $table = substr(basename((string) $file), 0, strrpos(basename((string) $file), '.'));
        $schema = ['name' => $table];
        $is_index = $is_foreign = $is_property = false;
        foreach (file($file) as $row) {
            $row = trim($row);
            if (!empty($row)) {
                if ($row == '--') {
                    $is_index = true;
                    $is_foreign = $is_property = false;
                    continue;
                }
                if ($row == '==') {
                    $is_foreign = true;
                    $is_index = $is_property = false;
                    continue;
                }
                if ($row == '##') {
                    $is_property = true;
                    $is_index = $is_foreign = false;
                    continue;
                }
                $details = str_getcsv($row, ' ');
                $field_name = array_shift($details);
                if ($is_index === true) {
                    $schema['index'][$field_name] = $details;
                    continue;
                }
                if ($is_foreign === true) {
                    foreach ($details as $d) {
                        if (!str_contains((string) $d, '(')) {
                            $schema['foreign'][$field_name]['col'][] = $d;
                            continue;
                        }
                        if (preg_match('/(.*)\((.*)\)/', (string) $d, $info)) {
                            switch ($info[1]) {
                                case 'ref_table':
                                case 'on_delete':
                                case 'on_update':
                                case 'prefix':
                                    $schema['foreign'][$field_name][$info[1]] = $info[2];
                                    break;
                                case 'ref_col':
                                    $schema['foreign'][$field_name]['ref_col'] = explode(' ', $info[2]);
                                    break;
                            }
                        }
                    }
                    continue;
                }
                if ($is_property === true) {
                    switch ($field_name) {
                        case 'engine':
                            $schema['property']['engine'] = implode(' ', $details);
                            break;
                        case 'character_set':
                            $schema['property']['character_set'] = implode(' ', $details);
                            break;
                        case 'collate':
                            $schema['property']['collate'] = implode(' ', $details);
                            break;
                    }
                    continue;
                }
                $field_type = array_shift($details);
                if (preg_match('/(.*)\((.*)\)/', (string) $field_type, $type_details)) {
                    $schema['col'][$field_name]['type'] = $type_details[1];
                    $schema['col'][$field_name]['length'] = $type_details[2];
                } else {
                    $schema['col'][$field_name]['type'] = $field_type;
                }
                if (preg_match('/default\((.*)\)/', implode(' ', $details), $type_default)) {
                    $schema['col'][$field_name]['default'] = $type_default[1];
                    $default_pos = array_search('default(' . $type_default[1] . ')', $details);
                    array_splice($details, $default_pos, 1);
                }
                $is_binary = array_search('binary', $details);
                if (is_integer($is_binary)) {
                    array_splice($details, $is_binary, 1);
                    $schema['col'][$field_name]['binary'] = true;
                }
                $is_unsigned = array_search('unsigned', $details);
                if (is_integer($is_unsigned)) {
                    array_splice($details, $is_unsigned, 1);
                    $schema['col'][$field_name]['unsigned'] = true;
                }
                $is_not_null = array_search('not_null', $details);
                if (is_integer($is_not_null)) {
                    array_splice($details, $is_not_null, 1);
                    $schema['col'][$field_name]['not_null'] = true;
                }
                $is_auto_increment = array_search('auto_increment', $details);
                if (is_integer($is_auto_increment)) {
                    array_splice($details, $is_auto_increment, 1);
                    $schema['col'][$field_name]['auto_increment'] = true;
                }
                if (!empty($details)) {
                    $schema['col'][$field_name]['other'] = implode(' ', $details);
                }
            }
        }
        return $schema;
    }
    public static function get_sql_from_schema(array $schema, $prefix = null): string
    {
        $sql = 'CREATE TABLE ' . ($prefix ?? '') . $schema['name'] . ' (' . "\n";
        $rows = [];
        foreach ($schema['col'] as $name => $fields) {
            $row = '  ' . $name . ' ' . $fields['type'];
            if (isset($fields['length'])) {
                $row .= '(' . $fields['length'] . ')';
            }
            if (isset($fields['binary']) && $fields['binary'] === true) {
                $row .= ' binary';
            }
            if (isset($fields['unsigned']) && $fields['unsigned'] === true) {
                $row .= ' unsigned';
            }
            if (isset($fields['default'])) {
                $row .= ' DEFAULT ' . $fields['default'];
            }
            if (isset($fields['not_null']) && $fields['not_null'] === true) {
                $row .= ' NOT NULL';
            }
            if (isset($fields['auto_increment']) && $fields['auto_increment'] === true) {
                $row .= ' auto_increment';
            }
            $rows[] = $row;
        }
        if (isset($schema['index'])) {
            foreach ($schema['index'] as $name => $fields) {
                if ($name == 'primary') {
                    $name = 'PRIMARY KEY';
                } else {
                    $name = 'KEY ' . $name;
                }
                $row = '  ' . $name . ' (' . implode(', ', $fields) . ')';
                $rows[] = $row;
            }
        }
        if (isset($schema['foreign'])) {
            foreach ($schema['foreign'] as $name => $fields) {
                $row = '  FOREIGN KEY ' . $name . ' (' . implode(', ', $fields['col']) . ') REFERENCES ' . (isset($prefix) && (!isset($fields['prefix']) || $fields['prefix'] != 'false') ? $prefix : '') . $fields['ref_table'] . '(' . implode(', ', $fields['ref_col']) . ')';
                if (isset($fields['on_update'])) {
                    $row .= ' ON UPDATE ' . strtoupper($fields['on_update']);
                }
                if (isset($fields['on_delete'])) {
                    $row .= ' ON DELETE ' . strtoupper($fields['on_delete']);
                }
                $rows[] = $row;
            }
        }
        $sql .= implode(',' . "\n", $rows) . "\n" . ')';
        if (isset($schema['property'])) {
            if (isset($schema['property']['engine'])) {
                $sql .= ' ENGINE ' . $schema['property']['engine'];
            }
            if (isset($schema['property']['character_set'])) {
                $sql .= ' CHARACTER SET ' . $schema['property']['character_set'];
            }
            if (isset($schema['property']['collate'])) {
                $sql .= ' COLLATE ' . $schema['property']['collate'];
            }
        }
        return $sql . ';';
    }
    public static function prepare_input($string)
    {
        if (is_string($string)) {
            return HTML::sanitize($string);
        }
        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = static::prepare_input($v);
            }
            return $string;
        }
        return $string;
    }
    public static function prepare_identifier($string): string
    {
        return '`' . str_replace('`', '``', $string) . '`';
    }
    public function set_table_prefix($prefix): void
    {
        $this->table_prefix = $prefix;
    }
    protected function auto_prefix_tables($statement): string|array
    {
        $prefix = '';
        if (isset($this->table_prefix)) {
            $prefix = $this->table_prefix;
        } elseif (OSCOM::config_exists('db_table_prefix')) {
            $prefix = OSCOM::get_config('db_table_prefix');
        }
        return str_replace(':table_', $prefix, $statement);
    }
}