<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  class messageStack extends alertBlock {

// class constructor
    function __construct() {

      $this->messages = [];

      if (isset($_SESSION['messageToStack'])) {
        for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

// class methods
    function add($class, $message, $type = 'error'): void {
      if ($type == 'error') {
        $this->messages[] = ['params' => 'class="alert alert-danger alert-dismissible"', 'class' => $class, 'text' => $message];
      } elseif ($type == 'warning') {
        $this->messages[] = ['params' => 'class="alert alert-warning alert-dismissible"', 'class' => $class, 'text' => $message];
      } elseif ($type == 'success') {
        $this->messages[] = ['params' => 'class="alert alert-success alert-dismissible"', 'class' => $class, 'text' => $message];
      } else {
        $this->messages[] = ['params' => 'class="alert alert-info alert-dismissible"', 'class' => $class, 'text' => $message];
      }
    }

    function add_session($class, $message, $type = 'error'): void {
      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = [];
      }

      $_SESSION['messageToStack'][] = ['class' => $class, 'text' => $message, 'type' => $type];
    }

    function reset(): void {
      $this->messages = [];
    }

    function output($class) {
      $output = [];
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }

      return parent::__construct($output);
    }

    function size($class): int {
      $count = 0;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $count++;
        }
      }

      return $count;
    }
  }
