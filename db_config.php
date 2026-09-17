<?php
// Central DB connection settings.
// In Docker, these come from environment variables set in docker-compose.yml.
// Locally (e.g. XAMPP), it falls back to your old localhost/root/no-password/tom setup.
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'tom';

function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    $con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (!$con) {
        die("couldn't connect to the server: " . mysqli_connect_error());
    }
    return $con;
}
