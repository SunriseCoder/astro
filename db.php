<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'astro';

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);
// Check connection
if (mysqli_connect_errno()) {
    die("Connection failed: " . $mysqli->connect_error);
}

/*
 * MySQL Usage Examples:

 * Execute Query
    $result = $conn->query('SELECT id, firstname, lastname FROM users');

 * Prepared Statement
    $stmt = $mysqli->prepare('INSERT INTO persons (id, name, department) VALUES (?, ?, ?)');
    $stmt->bind_param('isi', $id, $name, $department);
        i - corresponding variable has type integer
        d - corresponding variable has type double
        s - corresponding variable has type string
        b - corresponding variable is a blob and will be sent in packets
    $stmt->execute();
    $result = $stmt->get_result();

 * Parse Result
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }
    } else {
        echo "0 results";
    }

 * Close Connection
    $conn->close();
*/
?>
