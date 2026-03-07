<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\FileSystem;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class upload
{
    public $file;
    public $filename;
    public $destination;
    public $permissions;
    public $extensions;
    public $tmp_filename;
    public $message_location;

    public function __construct($file = '', $destination = '', $permissions = '777', $extensions = '')
    {
        $this->set_file($file);
        $this->set_destination($destination);
        $this->set_permissions($permissions);
        $this->set_extensions($extensions);

        $this->set_output_messages('direct');

        if (tep_not_null($this->file) && tep_not_null($this->destination)) {
            $this->set_output_messages('session');

            return;
        }
    }

    public function parse()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');

        $file = [];

        if (isset($_FILES[$this->file])) {
            $file = ['name' => $_FILES[$this->file]['name'],
                          'type' => $_FILES[$this->file]['type'],
                          'size' => $_FILES[$this->file]['size'],
                          'tmp_name' => $_FILES[$this->file]['tmp_name']];
        } elseif (isset($_FILES[$this->file])) {
            $file = ['name' => $_FILES[$this->file]['name'],
                          'type' => $_FILES[$this->file]['type'],
                          'size' => $_FILES[$this->file]['size'],
                          'tmp_name' => $_FILES[$this->file]['tmp_name']];
        }

        if (isset($file['tmp_name']) && tep_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name'])) {
            if (sizeof($this->extensions) > 0) {
                if (!in_array(strtolower(substr((string) $file['name'], strrpos((string) $file['name'], '.') + 1)), $this->extensions)) {
                    $OSCOM_MessageStack->add(OSCOM::getDef('error_filetype_not_allowed'), 'error');

                    return false;
                }
            }

            $this->set_file($file);
            $this->set_filename($file['name']);
            $this->set_tmp_filename($file['tmp_name']);

            return $this->check_destination();
        }
        $OSCOM_MessageStack->add(OSCOM::getDef('warning_no_file_uploaded'), 'warning');
        return false;
    }

    public function save(): bool
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');

        if (!str_ends_with((string) $this->destination, '/')) {
            $this->destination .= '/';
        }

        if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
            chmod($this->destination . $this->filename, $this->permissions);

            $OSCOM_MessageStack->add(OSCOM::getDef('success_file_saved_successfully'), 'success');

            return true;
        }
        $OSCOM_MessageStack->add(OSCOM::getDef('error_file_not_saved'), 'error');
        return false;
    }

    public function set_file($file): void
    {
        $this->file = $file;
    }

    public function set_destination($destination): void
    {
        $this->destination = $destination;
    }

    public function set_permissions($permissions): void
    {
        $this->permissions = octdec($permissions);
    }

    public function set_filename($filename): void
    {
        $this->filename = $filename;
    }

    public function set_tmp_filename($filename): void
    {
        $this->tmp_filename = $filename;
    }

    public function set_extensions($extensions): void
    {
        if (tep_not_null($extensions)) {
            if (is_array($extensions)) {
                $this->extensions = $extensions;
            } else {
                $this->extensions = [$extensions];
            }
        } else {
            $this->extensions = [];
        }
    }

    public function check_destination(): bool
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');

        if (!FileSystem::isWritable($this->destination)) {
            if (is_dir($this->destination)) {
                $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_not_writeable', ['destination' => $this->destination]), 'error');
            } else {
                $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_does_not_exist', ['destination' => $this->destination]), 'error');
            }

            return false;
        }
        return true;
    }

    public function set_output_messages($location): void
    {
        $this->message_location = match ($location) {
            'session' => 'session',
            default => 'direct',
        };
    }
}
