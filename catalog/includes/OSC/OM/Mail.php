<?php

declare (strict_types=1);
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */
namespace OSC\OM;

class Mail
{
    protected $to = [];
    protected $from = [];
    protected $cc = [];
    protected $bcc = [];
    protected $subject;
    protected $body_plain;
    protected $body_html;
    protected $attachments = [];
    protected $images = [];
    protected $headers = ['X-Mailer' => 'osCommerce'];
    protected $body;
    protected $content_transfer_encoding = '7bit';
    protected $charset = 'utf-8';
    public function __construct($to_email_address = null, $to = null, $from_email_address = null, $from = null, $subject = null)
    {
        if (!empty($to_email_address)) {
            $this->add_to($to_email_address, $to);
        }
        if (!empty($from_email_address)) {
            $this->set_from($from_email_address, $from);
        }
        if (!empty($subject)) {
            $this->set_subject($subject);
        }
    }
    public function add_to($email_address, $name = null): void
    {
        $this->to[] = ['name' => $name, 'email_address' => $email_address];
    }
    public function set_from($email_address, $name = null): void
    {
        $this->from = ['name' => $name, 'email_address' => $email_address];
    }
    public function add_cc($email_address, $name = null): void
    {
        $this->cc[] = ['name' => $name, 'email_address' => $email_address];
    }
    public function add_bcc($email_address, $name = null): void
    {
        $this->bcc[] = ['name' => $name, 'email_address' => $email_address];
    }
    public function clear_to(): void
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        if (isset($this->headers['Cc'])) {
            unset($this->headers['Cc']);
        }
        if (isset($this->headers['Bcc'])) {
            unset($this->headers['Bcc']);
        }
    }
    public function set_subject($subject): void
    {
        $this->subject = $subject;
    }
    public function set_body($text, $html = null): void
    {
        $this->set_body_plain($text);
        if (!isset($html) || empty($html)) {
            $html = nl2br((string) $text);
        }
        $this->set_body_html($html);
    }
    public function set_body_plain($body): void
    {
        $this->body_plain = $body;
        $this->body = null;
    }
    public function set_body_html($body): void
    {
        $this->body_html = $body;
        $this->body = null;
    }
    public function set_content_transfer_encoding($encoding): void
    {
        $this->content_transfer_encoding = $encoding;
    }
    public function set_charset($charset): void
    {
        $this->charset = $charset;
    }
    public function add_header($key, $value)
    {
        if (str_contains((string) $key, "\n") || str_contains((string) $key, "\r")) {
            return false;
        }
        if (str_contains((string) $value, "\n") || str_contains((string) $value, "\r")) {
            return false;
        }
        $this->headers[$key] = $value;
    }
    public function add_attachment($file, $is_uploaded = false): void
    {
        if ($is_uploaded !== true && (file_exists($file) && is_readable($file))) {
            $data = file_get_contents($file);
            $filename = basename($file);
            $mimetype = $this->get_mime_type($filename);
        }
        $this->attachments[] = ['filename' => $filename, 'mimetype' => $mimetype, 'data' => chunk_split(base64_encode($data))];
    }
    public function add_image($file, $is_uploaded = false): void
    {
        if ($is_uploaded !== true && (file_exists($file) && is_readable($file))) {
            $data = file_get_contents($file);
            $filename = basename($file);
            $mimetype = $this->get_mime_type($filename);
        }
        $this->images[] = ['id' => md5(uniqid(time())), 'filename' => $filename, 'mimetype' => $mimetype, 'data' => chunk_split(base64_encode($data))];
    }
    public function send()
    {
        if (empty($this->body)) {
            if (!empty($this->body_plain) && !empty($this->body_html)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';
                $alternative_boundary = '=_____MULTIPART_ALTERNATIVE_BOUNDARY____';
                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
                $this->headers['Content-Transfer-Encoding'] = $this->content_transfer_encoding;
                if (!empty($this->images)) {
                    foreach ($this->images as $image) {
                        $this->body_html = str_replace('src="' . $image['filename'] . '"', 'src="cid:' . $image['id'] . '"', $this->body_html);
                    }
                    unset($image);
                }
                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" . '--' . $boundary . "\n" . 'Content-Type: multipart/alternative; boundary="' . $alternative_boundary . '";' . "\n\n" . '--' . $alternative_boundary . "\n" . 'Content-Type: text/plain; charset="' . $this->charset . '"' . "\n" . 'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" . $this->body_plain . "\n\n" . '--' . $alternative_boundary . "\n" . 'Content-Type: multipart/related; boundary="' . $related_boundary . '"' . "\n\n" . '--' . $related_boundary . "\n" . 'Content-Type: text/html; charset="' . $this->charset . '"' . "\n" . 'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" . $this->body_html . "\n\n";
                if (!empty($this->images)) {
                    foreach ($this->images as $image) {
                        $this->body .= $this->build_image($image, $related_boundary);
                    }
                    unset($image);
                }
                $this->body .= '--' . $related_boundary . '--' . "\n\n" . '--' . $alternative_boundary . '--' . "\n\n";
                if (!empty($this->attachments)) {
                    foreach ($this->attachments as $attachment) {
                        $this->body .= $this->build_attachment($attachment, $boundary);
                    }
                    unset($attachment);
                }
                $this->body .= '--' . $boundary . '--' . "\n\n";
            } elseif (!empty($this->body_html) && !empty($this->images)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';
                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
                foreach ($this->images as $image) {
                    $this->body_html = str_replace('src="' . $image['filename'] . '"', 'src="cid:' . $image['id'] . '"', $this->body_html);
                }
                unset($image);
                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" . '--' . $boundary . "\n" . 'Content-Type: multipart/related; boundary="' . $related_boundary . '";' . "\n\n" . '--' . $related_boundary . "\n" . 'Content-Type: text/html; charset="' . $this->charset . '"' . "\n" . 'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" . $this->body_html . "\n\n";
                foreach ($this->images as $image) {
                    $this->body .= $this->build_image($image, $related_boundary);
                }
                unset($image);
                $this->body .= '--' . $related_boundary . '--' . "\n\n";
                foreach ($this->attachments as $attachment) {
                    $this->body .= $this->build_attachment($attachment, $boundary);
                }
                unset($attachment);
                $this->body .= '--' . $boundary . '--' . "\n";
            } elseif (!empty($this->attachments)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';
                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" . '--' . $boundary . "\n" . 'Content-Type: multipart/related; boundary="' . $related_boundary . '";' . "\n\n" . '--' . $related_boundary . "\n" . 'Content-Type: text/' . (empty($this->body_plain) ? 'html' : 'plain') . '; charset="' . $this->charset . '"' . "\n" . 'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" . (empty($this->body_plain) ? $this->body_html : $this->body_plain) . "\n\n" . '--' . $related_boundary . '--' . "\n\n";
                foreach ($this->attachments as $attachment) {
                    $this->body .= $this->build_attachment($attachment, $boundary);
                }
                unset($attachment);
                $this->body .= '--' . $boundary . '--' . "\n";
            } elseif (!empty($this->body_html)) {
                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'text/html; charset="' . $this->charset . '"';
                $this->headers['Content-Transfer-Encoding'] = $this->content_transfer_encoding;
                $this->body = $this->body_html . "\n";
            } else {
                $this->body = $this->body_plain . "\n";
            }
        }
        $to_email_addresses = [];
        foreach ($this->to as $to) {
            if (str_contains((string) $to['email_address'], "\n") || str_contains((string) $to['email_address'], "\r")) {
                return false;
            }
            if (str_contains((string) $to['name'], "\n") || str_contains((string) $to['name'], "\r")) {
                return false;
            }
            if (empty($to['name'])) {
                $to_email_addresses[] = $to['email_address'];
            } else {
                $to_email_addresses[] = '"' . $to['name'] . '" <' . $to['email_address'] . '>';
            }
        }
        unset($to);
        $cc_email_addresses = [];
        foreach ($this->cc as $cc) {
            if (empty($cc['name'])) {
                $cc_email_addresses[] = $cc['email_address'];
            } else {
                $cc_email_addresses[] = '"' . $cc['name'] . '" <' . $cc['email_address'] . '>';
            }
        }
        unset($cc);
        $bcc_email_addresses = [];
        foreach ($this->bcc as $bcc) {
            if (empty($bcc['name'])) {
                $bcc_email_addresses[] = $bcc['email_address'];
            } else {
                $bcc_email_addresses[] = '"' . $bcc['name'] . '" <' . $bcc['email_address'] . '>';
            }
        }
        unset($bcc);
        if (empty($this->from['name'])) {
            $this->add_header('From', $this->from['email_address']);
        } else {
            $this->add_header('From', '"' . $this->from['name'] . '" <' . $this->from['email_address'] . '>');
        }
        if (!empty($cc_email_addresses)) {
            $this->add_header('Cc', implode(', ', $cc_email_addresses));
        }
        if (!empty($bcc_email_addresses)) {
            $this->add_header('Bcc', implode(', ', $bcc_email_addresses));
        }
        $headers = '';
        foreach ($this->headers as $key => $value) {
            $headers .= $key . ': ' . $value . "\n";
        }
        if (empty($this->from['email_address']) || empty($to_email_addresses)) {
            return false;
        }
        if (empty($this->from['name'])) {
            ini_set('sendmail_from', $this->from['email_address']);
        } else {
            ini_set('sendmail_from', '"' . $this->from['name'] . '" <' . $this->from['email_address'] . '>');
        }
        mail(implode(', ', $to_email_addresses), (string) $this->subject, (string) $this->body, $headers, '-f' . $this->from['email_address']);
        ini_restore('sendmail_from');
    }
    protected function get_mime_type($file): string
    {
        $ext = substr((string) $file, strrpos((string) $file, '.') + 1);
        $mime_types = ['gif' => 'image/gif', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'bmp' => 'image/bmp', 'png' => 'image/png', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'swf' => 'application/x-shockwave-flash'];
        return $mime_types[$ext] ?? 'application/octet-stream';
    }
    protected function build_attachment(array $attachment, string $boundary): string
    {
        return '--' . $boundary . "\n" . 'Content-Type: ' . $attachment['mimetype'] . '; name="' . $attachment['filename'] . '"' . "\n" . 'Content-Disposition: attachment' . "\n" . 'Content-Transfer-Encoding: base64' . "\n\n" . $attachment['data'] . "\n\n";
    }
    protected function build_image(array $image, string $boundary): string
    {
        return '--' . $boundary . "\n" . 'Content-Type: ' . $image['mimetype'] . '; name="' . $image['filename'] . '"' . "\n" . 'Content-ID: ' . $image['id'] . "\n" . 'Content-Disposition: inline' . "\n" . 'Content-Transfer-Encoding: base64' . "\n\n" . $image['data'] . "\n\n";
    }
}