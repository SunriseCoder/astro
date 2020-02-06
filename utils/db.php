<?php

class Db {
    private $servername = 'localhost';
    private $username = 'root';
    private $password = '';
    private $dbname = 'astro';

    private $conn;

    function connect() {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection
        if (mysqli_connect_errno()) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    function query($sql) {
        $result = $this->prepStmt($sql, NULL, NULL);
        return $result;
    }

    /**
     * Executes Prepared Statement
     *
     * @param string $sql - SQL Query
     * @param string $types - types of variables, like 'isd' (integer, string, double)
     * @param array $parameters - array of values to be bound with placehoders in SQL Query
     * @return array of arrays - fetched data
     */
    function prepStmt($sql, $types, $parameters) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare Statement failed: " . $this->conn->connect_error);
        }

        if ($types != NULL) {
            $bind_names[] = $types;
            for ($i = 0; $i < count($parameters); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $parameters[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
        }

        $status = $stmt->execute();
        if (!$status) {
            die("Execute Prepared Statement failed: " . $this->conn->connect_error);
        }

        $result = $this->fetchResult($stmt);
        $stmt->close();

        return $result;
    }

    private function fetchResult($stmt) {
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

    function insertedId() {
        return $this->conn->insert_id;
    }
}

$db = new Db();
$db->connect();

?>
