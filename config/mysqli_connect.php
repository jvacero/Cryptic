<?php
// ==========================================
// Database Connection
// ==========================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Set your MySQL password
$db_name = 'cryptic_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error){
die ("Connection Error") . $db_connected = !$conn->connect_error;
}