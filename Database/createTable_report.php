<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';

// Create connection
$conn = new mysqli($DBservername, $DBusername, $DBpassword, $DBname);

// Check connection
if($conn->connect_error){
    exit('Connection failed: ' . $conn->connect_error);
}

// Create table
$sql = 'CREATE TABLE report (
    id INT NOT NULL AUTO_INCREMENT,
    link VARCHAR(' . LINKLENGTH . ') NOT NULL,
    password VARCHAR(' . PASSWORDLENGTH . '),
    name VARCHAR(' . NAMELENGTH . '),
    info TEXT NOT NULL,
    reportDate DATETIME NOT NULL,
    handleDate DATETIME,
    checked BOOL DEFAULT 0 NOT NULL,
    removed BOOL DEFAULT 0 NOT NULL,
    PRIMARY KEY(id))';

if ($conn->query($sql) === TRUE) {
    echo 'Table "report" created successfully.';
} else {
    echo 'Error creating table: ' . $conn->error;
}

$conn->close();