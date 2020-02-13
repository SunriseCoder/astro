<?php
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
    }

    class Utils {
        public static function redirect($url) {
            header("Location: /".$url, true);
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
    }

    class Email {
        public static function sendPassword($email, $pass) {
            $subject = 'Your password for Astrology Survey';
            $message = "This E-Mail is sent because you have registered on the website astro.chaitanya.academy\n\n".
                "Your E-Mail: $email\nYour password: $pass\n".
                "Please use this link to Sign In: http://astro.chaitanya.academy/login.php\n\nChaitanya Academy";
            $headers = 'From: noreply@chaitanya.academy';
            $result = mail($email, $subject, $message, $headers);
            return $result;
        }
    }
?>
