<?php
    // TODO Disable before release
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    class Config {
        const DB_HOST = 'localhost';
        const DB_USER = 'root';
        const DB_PASS = '';
        const DB_NAME = 'astro';
    }

    class Logger {
        const OFF = 0;
        const SEVERE = 1;
        const ERROR = 2;
        const WARNING = 3;
        const INFO = 4;
        const DEBUG = 5;
        const TRACE = 6;

        const SHOW_STACKTRACE = TRUE;

        const LOG_LEVEL = self::WARNING;

        public static function isLevel($level) {
            $result = self::LOG_LEVEL >= $level;
            return $result;
        }

        public static function severe($message) {
            if (self::LOG_LEVEL >= self::SEVERE) {
                self::printMessage('Severe', $message);
            }
        }

        public static function error($message) {
            if (self::LOG_LEVEL >= self::ERROR) {
                self::printMessage('Error', $message);
            }
        }

        public static function warning($message) {
            if (self::LOG_LEVEL >= self::WARNING) {
                self::printMessage('Warning', $message);
            }
        }

        public static function info($message) {
            if (self::LOG_LEVEL >= self::INFO) {
                self::printMessage('Info', $message);
            }
        }

        public static function debug($message) {
            if (self::LOG_LEVEL >= self::DEBUG) {
                self::printMessage('Debug', $message);
            }
        }

        public static function trace($message) {
            if (self::LOG_LEVEL >= self::TRACE) {
                self::printMessage('Trace', $message);
            }
        }

        public static function showTrace($trace) {
            if (self::SHOW_STACKTRACE) {
                self::printMessage('Stacktrace', $trace);
            }
        }

        public static function printMessage($level, $message) {
            echo '<b>'.$level.':</b> '.$message.'<br /><br />';
        }
    }
?>
