<?php
if (!defined('ABSPATH')) exit;

class LVYID_Logger
{
    private $log_file;

    public function __construct($file_name = 'lvyid.log') {

        $this->log_file = plugin_dir_path(__FILE__) . '../logs/' . $file_name;

        if (!file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }

    public function log($message, $level = 'INFO') {
        $time = date("Y-m-d H:i:s");
        $log_message = "[$time] [$level] $message" . PHP_EOL;

        if (is_writable($this->log_file)) {
            file_put_contents($this->log_file, $log_message, FILE_APPEND);
        } else {
            error_log("Не удается записать в файл лога: " . $this->log_file);
        }
    }

    public function info($message) {
        $this->log($message, 'INFO');
    }

    public function warning($message) {
        $this->log($message, 'WARNING');
    }

    public function error($message) {
        $this->log($message, 'ERROR');
    }
}
