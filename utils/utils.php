<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    class DateTimeUtils {
        public static function fromDatabase($dbValue) {
            $result = DateTime::createFromFormat("Y-m-d H:i:s", $dbValue);
            return $result;
        }

        public static function now() {
            $now = new DateTime();
            return $now;
        }

        public static function toDatabase(DateTime $dateTime) {
            $result = $dateTime->format("Y-m-d H:i:s");
            return $result;
        }

        public static function diffInSeconds(DateTime $dateTime1, DateTime $dateTime2) {
            $result = $dateTime2->format("U") - $dateTime1->format("U");
            return $result;
        }
    }

    class Utils {
        /**
         * Checks that all elements $parameters are set in $array
         *
         * @param array $array
         * @param array $parameters
         * @return TRUE only if ALL $parameters are set
         */
        public static function areSet($array, $parameters) {
            if (!isset($array)) {
                return FALSE;
            }
            foreach ($parameters as $parameter) {
                if (!isset($array[$parameter])) {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function redirect($url) {
            header("Location: ".$url, true);
            exit;
        }

        public static function generateRandomString($length = 10) {
            $symbols = '0123456789ABCDEFGHKMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
            $symbolsAmount = strlen($symbols);
            $result = '';
            for ($i = 0; $i < $length; $i++) {
                $result .= $symbols[rand(0, $symbolsAmount - 1)];
            }

            return $result;
        }

        /**
         * Checks that the current PHP version meets the requirements
         *
         * @param string $required like: '5.4.1';
         *
         * @return TRUE if the current version is equals or greater than $required, otherwise FALSE
         */
        public static function checkPhpVersion($required) {
            $result = version_compare(phpversion(), $required, '>=');
            return $result;
        }
    }

    class Email {
        public static function sendPassword($email, $pass) {
            $subject = Tr::trs('registration.password.email.subject', 'Your password for the Astrology Survey');
            $message = Tr::format('registration.password.email.text', [$email, $pass],
                "This E-Mail is sent because you have registered on the website http://astro.chaitanya.academy/\n\n".
                "Your E-Mail: {0}\nYour password: {1}\n".
                "Please use this link to Sign In: http://astro.chaitanya.academy/login.php\n\nChaitanya Academy");
            $headers = 'From: noreply@chaitanya.academy';
            $result = mail($email, $subject, $message, $headers);
            return $result;
        }
    }

    class NumberUtils {
        public static function humanReadableSize($size) {
            $power = 0;
            $unit = 1024;
            $prefixes = 'kMGT';
            while ($size > $unit && $power <= strlen($prefixes)) {
                $size = $size / $unit;
                $power++;
            }

            $size = $size >= 10 ? round($size, 0) : round($size, 1);
            if ($power > 0) {
                $size .= substr($prefixes, $power - 1, 1);
            }
            return $size;
        }
    }
?>
