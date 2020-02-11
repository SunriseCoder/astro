<?php

include $_SERVER["DOCUMENT_ROOT"].'/utils/config.php';

class Db {
    const DEBUG_MODE = true;

    public static $conn;

    private static function connectIfNeeded() {
        // Checking if already connected
        if (self::$conn != NULL) {
            return;
        }

        // Create connection
        self::$conn = new mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);

        // Check connection
        if (self::$conn->connect_errno) {
            $message = 'Connection failed';
            if (self::DEBUG_MODE) {
                $message += ': '.self::$conn->connect_error;
                debug_print_backtrace();
            }
            die($message);
        }
    }

    public static function query($sql) {
        self::connectIfNeeded();

        $query = self::$conn->query($sql);
        if (!$query) {
            $message = 'Query failed';
            if (self::DEBUG_MODE) {
                $message += ': '.self::$conn->error;
                debug_print_backtrace();
            }
            die($message);
        }

        $fields = $query->fetch_fields();
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
        self::connectIfNeeded();

        // Preparing Statement
        $stmt = self::$conn->prepare($sql);
        if (!$stmt) {
            $message = 'Prepared Query failed';
            if (self::DEBUG_MODE) {
                $message += ': '.self::$conn->error;
                debug_print_backtrace();
            }
            die($message);
        }

        // Binding Parameters if they are set
        self::bindParams($stmt, $types, $parameters);

        // Checking execution Status
        $status = $stmt->execute();
        if (!$status) {
            $message = 'Execute Prepared Statement failed';
            if (self::DEBUG_MODE) {
                $message += ': '.self::$conn->error;
                debug_print_backtrace();
            }
            die($message);
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
        self::connectIfNeeded();

        // Preparing Statement
        $stmt = self::$conn->prepare($sql);
        if (!$stmt) {
            $message = 'Prepared Statement failed';
            if (self::DEBUG_MODE) {
                $message += ': '.self::$conn->error;
                debug_print_backtrace();
            }
            die($message);
        }

        // Binding Parameters if they are set
        self::bindParams($stmt, $types, $parameters);

        // Checking execution Status
        $status = $stmt->execute();
        if (!$status) {
            $message = 'Execute Prepared Statement failed';
            if (self::DEBUG_MODE) {
                $message = ': '.self::$conn->error;
                debug_print_backtrace();
            }
            die($message);
        }

        $stmt->close();
    }

    private static function bindParams($stmt, $types, $parameters) {
        // Binding Parameters if they are set
        if ($types == NULL) {
            if (self::DEBUG_MODE) {
                debug_print_backtrace();
            }
            die('$types must be not NULL');
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
            if (self::DEBUG_MODE) {
                debug_print_backtrace();
            }
            die('Cannot get insert_id, connection is NULL');
        }

        $result = self::$conn->insert_id;
        if (!$result) {
            if (self::DEBUG_MODE) {
                debug_print_backtrace();
            }
            die('Connection does not have insert_id');
        }

        return $result;
    }
}
?>
