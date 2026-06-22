<?php
$host = 'localhost';
$user = 'root'; // Default XAMPP username
$pass = '';     // Default XAMPP password (leave blank)
$dbname = 'inventory_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alert_query = $conn->query("SELECT COUNT(*) AS count FROM Items WHERE quantity <= critical_minimum");
$global_alert_count = $alert_query->fetch_assoc()['count'] ?? 0;
?>