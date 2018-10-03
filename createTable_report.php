<?php

require_once 'databaseInfo.php';

// Create connection
$conn = new mysqli($DBservername, $DBusername, $DBpassword, $DBname);

// Check connection
if($conn->connect_error){
    die('Connection failed: ' . $conn->connect_error);
}

// Create table
$sql = 'CREATE TABLE report (
    link VARCHAR(255) NOT NULL,
    password VARCHAR(40),
	info VARCHAR(255),
    reportDate DATETIME NOT NULL)';

if ($conn->query($sql) === TRUE) {
    echo 'Table "report" created successfully.';
} else {
    echo 'Error creating table: ' . $conn->error;
}

$conn->close();