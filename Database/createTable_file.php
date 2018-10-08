<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';

// Create connection
$conn = new mysqli($DBservername, $DBusername, $DBpassword, $DBname);

// Check connection
if($conn->connect_error){
    die('Connection failed: ' . $conn->connect_error);
}

// Create table
$sql = 'CREATE TABLE file (
    keycode VARCHAR(6) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    password VARCHAR(32),
    downloads INT DEFAULT 0 NOT NULL,
    uploadDate DATE NOT NULL,
    lastView DATE NOT NULL,
    deleteCode VARCHAR(10) NOT NULL,
    size VARCHAR(10) NOT NULL,
    PRIMARY KEY(keycode))';

if ($conn->query($sql) === TRUE) {
    echo 'Table "file" created successfully.';
} else {
    echo 'Error creating table: ' . $conn->error;
}

$conn->close();