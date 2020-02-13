<?php
    class Json {
        public static function encode($object) {
            $string = json_encode($object);
            return $string;
        }

        public static function decode($string) {
            $object = json_decode($string);
            return $object;
        }
    }
?>
