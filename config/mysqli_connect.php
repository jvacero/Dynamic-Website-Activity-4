<?php
// mysqli_connect.php - opens the shared $conn database connection.
// Edit DB_HOST / DB_USER / DB_PASS for your local environment.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'DynamicWebDemoAct4');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Database connection failed. Please make sure MySQL is running and DynamicWebDemoAct4 has been imported.');
}
