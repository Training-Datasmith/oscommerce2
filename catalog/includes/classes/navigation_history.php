<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  class navigationHistory {
    public $path, $snapshot;

    function __construct() {
      $this->reset();
    }

    function reset(): void {
      $this->path = [];
      $this->snapshot = [];
    }

    function add_current_page(): void {
      global $PHP_SELF, $cPath;

      $set = 'true';
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        if ($this->path[$i]['page'] == $PHP_SELF) {
          if (isset($cPath)) {
            if (!isset($this->path[$i]['get']['cPath'])) {
              continue;
            }
            if ($this->path[$i]['get']['cPath'] == $cPath) {
              array_splice($this->path, ($i+1));
              $set = 'false';
              break;
            } else {
              $old_cPath = explode('_', (string) $this->path[$i]['get']['cPath']);
              $new_cPath = explode('_', $cPath);

              for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                if ($old_cPath[$j] != $new_cPath[$j]) {
                  array_splice($this->path, ($i));
                  $set = 'true';
                  break 2;
                }
              }
            }
          } else {
            array_splice($this->path, ($i));
            $set = 'true';
            break;
          }
        }
      }

      if ($set == 'true') {
        $this->path[] = ['page' => $PHP_SELF,
                              'get' => $this->filter_parameters($_GET),
                              'post' => $this->filter_parameters($_POST)];
      }
    }

    function remove_current_page(): void {
      global $PHP_SELF;

      $last_entry_position = sizeof($this->path) - 1;
      if ($this->path[$last_entry_position]['page'] == $PHP_SELF) {
        unset($this->path[$last_entry_position]);
      }
    }

    function set_snapshot($page = ''): void {
      global $PHP_SELF;

      if (is_array($page)) {
        $this->snapshot = ['page' => $page['page'] ?? $PHP_SELF,
                                'get' => isset($page['get']) ? $this->filter_parameters($page['get']) : [],
                                'post' => isset($page['post']) ? $this->filter_parameters($page['post']) : []];
      } else {
        $this->snapshot = ['page' => $PHP_SELF,
                                'get' => $this->filter_parameters($_GET),
                                'post' => $this->filter_parameters($_POST)];
      }
    }

    function clear_snapshot(): void {
      $this->snapshot = [];
    }

    function set_path_as_snapshot($history = 0): void {
      $pos = (sizeof($this->path)-1-$history);
      $this->snapshot = ['page' => $this->path[$pos]['page'],
                              'get' => $this->path[$pos]['get'],
                              'post' => $this->path[$pos]['post']];
    }

    function debug(): void {
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        echo $this->path[$i]['page'] . '?';
        foreach($this->path[$i]['get'] as $key => $value) {
          echo $key . '=' . $value . '&';
        }
        if (sizeof($this->path[$i]['post']) > 0) {
          echo '<br />';
          foreach($this->path[$i]['post'] as $key => $value) {
            echo '&nbsp;&nbsp;<strong>' . $key . '=' . $value . '</strong><br />';
          }
        }
        echo '<br />';
      }

      if (sizeof($this->snapshot) > 0) {
        echo '<br /><br />';

        echo $this->snapshot['page'] . '?' . tep_array_to_string($this->snapshot['get'], [session_name()]) . '<br />';
      }
    }

    /**
     * @return mixed[]
     */
    function filter_parameters($parameters): array {
      $clean = [];

      if (is_array($parameters)) {
        foreach($parameters as $key => $value) {
          if (strpos((string) $key, '_nh-dns') < 1) {
            $clean[$key] = $value;
          }
        }
      }

      return $clean;
    }

    function unserialize($broken): void {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }
  }
?>
