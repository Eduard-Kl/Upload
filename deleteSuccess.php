<?php

//require_once 'helperFunctions.php';

// Open database connection
require_once 'DB.php';
$db = DB::getConnection();

// If button 'Delete file' was clicked on script delete.php
if(isset($_POST['submitDelete']) && isset($_GET['code']) ){

	// Delete from database
    $statement = $db -> prepare('DELETE FROM file WHERE deleteCode = :deleteCode');
    $statement -> execute(array('deleteCode' => $_GET['code']));	
	
    $targetFileFullPath = DIRECTORY . $_POST['fileName'];
    
    if(DEBUG == true)
        echo '<p>Debug: ' . $targetFileFullPath . ' is being deleted.</p>';
	
	// Delete from drive
	if(file_exists($targetFileFullPath) && unlink($targetFileFullPath) == true){
		echo '<p>File ' . $_POST['fileName'] . ' has been deleted.</p>';
		log('DEL: ' . $targetFileFullPath);
	}
}

// Close connection
$db = null;