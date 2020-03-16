<?php
if (!class_exists('Config')) { include $_SERVER["DOCUMENT_ROOT"].'/config/config.php'; }
if (!class_exists('Json')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/json.php'; }
if (!class_exists('LoginDao')) { include $_SERVER["DOCUMENT_ROOT"].'/dao/permissions.php'; }
if (!class_exists('Utils')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/utils.php'; }

class Db {
    public static $conn;

    private static function connectIfNeeded() {
        // Checking if already connected
        if (self::$conn != NULL) {
            return;
        }

        // Create connection
        self::$conn = new mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
        self::$conn->set_charset('utf8');

        // Check connection
        if (self::$conn->connect_errno) {
            Logger::error('Connection failed, '.self::$conn->connect_error);
            die;
        }

        self::doBackupIfNeeded();
    }

    public static function autocommit($mode) {
        $result = self::$conn->autocommit($mode);
        if (!$result) {
            Logger::warning('Failed to set autocommit mode: '.$mode);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
        }
        return $result;
    }

    public static function beginTransaction($flags = null, $name = null) {
        if (!Utils::checkPhpVersion('5.5')) {
            $result = self::autocommit(FALSE);
            return $result;
        }

        $result = self::$conn->begin_transaction($flags, $name);
        if (!$result) {
            Logger::warning('Failed to set begin Transaction: '.$flags.', '.$name);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
        }
        return $result;
    }

    public static function commit(int $flags = null, $name = null) {
        if (!Utils::checkPhpVersion('5.5')) {
            $result = self::$conn->commit();
            $result &= self::$conn->autocommit(TRUE);
            return $result;
        }

        $result = self::$conn->commit($flags, $name);
        if (!$result) {
            Logger::warning('Failed to Commit Transaction: '.$flags.', '.$name);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
        }
        return $result;
    }

    public static function rollback(int $flags = null, $name = null) {
        if (!Utils::checkPhpVersion('5.5')) {
            $result = self::$conn->rollback();
            $result &= self::$conn->autocommit(TRUE);
            return $result;
        }

        $result = self::$conn->rollback($flags, $name);
        if (!$result) {
            Logger::warning('Failed to Rollback Transaction: '.$flags.', '.$name);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
        }
        return $result;
    }

    /**
     * Just a Simple Database Query with Result
     *
     * @param string $sql - SQL Query
     * @return array of arrays - fetched data
     */
    public static function query($sql) {
        self::logQuery($sql, NULL);
        self::connectIfNeeded();

        $query = self::$conn->query($sql);
        if (!$query) {
            Logger::error('Query failed: '.self::$conn->error);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        $fields = $query->fetch_fields();
        $result = [];
        while ($row = $query->fetch_array(MYSQLI_ASSOC)) {
            $converted_row = [];
            foreach ($fields as $field) {
                $field_name = $field->name;
                $value = $row[$field_name];
                $converted_row[$field_name] = $value;
            }
            $result[] = $converted_row;
        }

        return $result;
    }

    /**
     * Executes Prepared Statement to get data from the database
     *
     * @param string $sql - SQL Query
     * @param string $types - types of variables, like 'isd' (integer, string, double)
     * @param array $parameters - array of values to be bound with placehoders in SQL Query
     * @return array of arrays - fetched data
     */
    public static function prepQuery($sql, $types, $parameters) {
        self::logQuery($sql, $parameters);
        self::connectIfNeeded();

        // Preparing Statement
        $stmt = self::$conn->prepare($sql);
        if (!$stmt) {
            Logger::error('Prepared Query failed: '.self::$conn->error);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        // Binding Parameters if they are set
        self::bindParams($stmt, $types, $parameters);

        // Checking execution Status
        $status = $stmt->execute();
        if (!$status) {
            Logger::error('Execute Prepared Statement failed: '.self::$conn->error);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        // Fetching Result
        $result = self::fetchResult($stmt);
        $stmt->close();

        return $result;
    }

    /**
     * Executes Prepared Statement to save data without Query Result
     *
     * @param string $sql - SQL Query
     * @param string $types - types of variables, like 'isd' (integer, string, double)
     * @param array $parameters - array of values to be bound with placehoders in SQL Query
     * @return array of arrays - fetched data
     */
    public static function prepStmt($sql, $types, $parameters) {
        self::logQuery($sql, $parameters);
        self::connectIfNeeded();

        // Preparing Statement
        $stmt = self::$conn->prepare($sql);
        if (!$stmt) {
            Logger::error('Prepared Statement failed: '.self::$conn->error);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        // Binding Parameters if they are set
        self::bindParams($stmt, $types, $parameters);

        // Checking execution Status
        $status = $stmt->execute();
        if (!$status) {
            Logger::error('Execute Prepared Statement failed: '.self::$conn->error);
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        $stmt->close();

        return true;
    }

    private static function bindParams($stmt, $types, $parameters) {
        // Binding Parameters if they are set
        if ($types == NULL) {
            Logger::error('$types must be not NULL');
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        $bind_names[] = $types;
        for ($i = 0; $i < count($parameters); $i++) {
            $bind_name = 'bind'.$i;
            $$bind_name = $parameters[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array(array($stmt,'bind_param'), $bind_names);
    }

    private static function fetchResult($stmt) {
        if (!$stmt->result_metadata()) {
            return NULL;
        }

        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $params);

        $result = [];
        while ($stmt->fetch()) {
            foreach($row as $key => $val) {
                $c[$key] = $val;
            }
            $result[] = $c;
        }

        return $result;
    }

    public static function insertedId() {
        if (self::$conn == NULL) {
            Logger::error('Cannot get insert_id, connection is NULL');
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        $result = self::$conn->insert_id;
        if (!$result) {
            Logger::error('Connection does not have insert_id');
            if (Logger::SHOW_STACKTRACE) {
                debug_print_backtrace();
            }
            die;
        }

        return $result;
    }

    public static function logQuery($sql, $parameters) {
        if (substr($sql, 0, 6) == 'SELECT') {
            return; // No need to log SELECT Queries
        }

        // Generating log message
        $message = DateTimeUtils::toDatabase(DateTimeUtils::now());
        $currentUser = LoginDao::getCurrentUser();
        $message .= ' '.(empty($currentUser) ? 'anonymous' : $currentUser->email).':'.LoginDao::clientIP();
        $message .= ' '.$sql.(empty($parameters) ? '' : ' -> '.Json::encode($parameters))."\n";

        // Writing message to the file
        $file = fopen($_SERVER["DOCUMENT_ROOT"].'/logs/db_queries.log', 'a');
        fwrite($file, $message);
        fclose($file);
    }

    private static function doBackupIfNeeded() {
        $sql = 'SELECT value FROM settings WHERE code = \'DB_BACKUP_LAST_TIME\'';
        $queryResult = self::query($sql);
        // If last backup time is not set, doing backup
        if (count($queryResult) == 0 || empty($queryResult[0]['value'])) {
            self::backup();
        } else {
            $lastBackup = DateTimeUtils::fromDatabase($queryResult[0]['value']);
            $secondsSinceLastBackup = DateTimeUtils::diffInSeconds($lastBackup, DateTimeUtils::now());
            // If passed more than backup time interval, doing backup
            if ($secondsSinceLastBackup > Config::DB_BACKUP_INTERVAL) {
                self::backup();
            }
        }
    }

    public static function backup(&$output = NULL, &$status = NULL) {
        $file = $_SERVER["DOCUMENT_ROOT"].Config::DB_BACKUP_SAVE.'/'.Config::DB_NAME.'-'.DateTimeUtils::now()->format("Y-m-d__H-i-s").'.sql.gz';
        $command = 'mysqldump -u '.Config::DB_USER.' --password='.Config::DB_PASS.' '.Config::DB_NAME.' | gzip > '.$file;
        exec($command, $output, $status);
        if ($status == 0) {
            $sql = 'UPDATE settings SET value = ? WHERE code = \'DB_BACKUP_LAST_TIME\'';
            self::prepStmt($sql, 's', [DateTimeUtils::toDatabase(DateTimeUtils::now())]);
        }
        return $status == 0;
    }

    public static function restore($file, &$output = NULL, &$status = NULL) {
        $command = 'gzip -cd '.$file.' | mysql -u '.Config::DB_USER.' --password='.Config::DB_PASS.' '.Config::DB_NAME;
        exec($command, $output, $status);
    }
}
?>
