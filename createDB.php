<?php
require_once 'databaseInfo.php';

// Create connection
$conn = new mysqli($DBservername, $DBusername, $DBpassword);

// Check connection
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = 'CREATE DATABASE' . $DBname;
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();