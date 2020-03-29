<?php
    if (!class_exists('Db')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php'; }
    if (!class_exists('Utils')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/utils.php'; }
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }

    class Parameter {
        public $code;
        public $value;
    }

    class Settings {
        // TODO Find all places where the string values of the following constants are used
        // and replace with usage of the appropriate constants
        const DEFAULT_QUESTIONNAIRE = 'DEFAULT_QUESTIONNAIRE';
        const DEFAULT_LANGUAGE_CODE = 'DEFAULT_LANGUAGE_CODE';
        const DB_BACKUP_LAST_TIME = 'DB_BACKUP_LAST_TIME';
        const DEFAULT_TRANSLATION_USER_ID = 'DEFAULT_TRANSLATION_USER_ID';
    }

    class SettingsDao {
        public static function getAll() {
            $sql = 'SELECT s.* FROM settings s ORDER BY s.code';
            $queryResults = Db::query($sql);
            $result = self::fetchAll($queryResults);
            return $result;
        }

        public static function getValueByCode($code) {
            $sql = 'SELECT s.* FROM settings s WHERE code = ? ORDER BY s.code';
            $queryResults = Db::prepQuery($sql, 's', [$code]);
            $fetchedParameters = self::fetchAll($queryResults);
            $result = empty($fetchedParameters[$code]) ? NULL : $fetchedParameters[$code];
            return $result;
        }

        public static function fetchAll($queryResult) {
            $parameters = [];
            foreach ($queryResult as $queryRow) {
                $parameter = new Parameter();
                $parameter->code = $queryRow['code'];
                $parameter->value = $queryRow['value'];
                $parameters[$parameter->code] = $parameter;
            }
            return $parameters;
        }
    }
?>
