<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';

// Create connection
$conn = new mysqli($DBservername, $DBusername, $DBpassword, $DBname);

// Check connection
if($conn->connect_error){
    exit('Connection failed: ' . $conn->connect_error);
}

// Create table
$sql = 'CREATE TABLE report (
    link VARCHAR(255) NOT NULL,
    password VARCHAR(32),
	name VARCHAR(40),
	info TEXT NOT NULL,
    reportDate DATETIME NOT NULL,
	checked BIT(1) DEFAULT 0 NOT NULL,
    removed BIT(1) DEFAULT 0 NOT NULL)';

if ($conn->query($sql) === TRUE) {
    echo 'Table "report" created successfully.';
} else {
    echo 'Error creating table: ' . $conn->error;
}

$conn->close();