<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

if(isset($_GET['f'])){
	$fileKey = htmlentities($_GET['f']);
}
else{
    toHomePage();
}

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Fetch from database (find $fileName, $correctPassword based on $fileKey)
$statement = $db -> prepare("SELECT filename, password FROM file WHERE keycode = :fileKey");
$statement -> execute(array('fileKey' => $fileKey));

foreach($statement->fetchAll() as $row){
    $fileName = $row['filename'];
    $targetFileFullPath = DIRECTORY . $fileKey . '-' . $fileName;
    $correctPassword = $row['password'];
    if(DEBUG)
        echo '<p>DEBUG: ' . $targetFileFullPath . ' ' . $correctPassword . '</p>';
}

// Download
if(file_exists($targetFileFullPath)){

    // Password protected file
    if($correctPassword != null){

        // If button 'Submit password' was clicked
        if(isset($_POST['submitPassword']) && $correctPassword == $_POST['optionalPassword']){
            
            if(DEBUG)
                echo '<p>DEBUG: ' . $correctPassword . ' ' . $_POST['optionalPassword'] . ' ' . $targetFileFullPath . '</p>';

            // Download (correct password)
            download($db, $fileName, $targetFileFullPath, $fileKey);
        }
        else{
            header('Location: /file/' . $fileKey);
        }
    }
    // Download (no password)
    else{
        download($db, $fileName, $targetFileFullPath, $fileKey);
    }
}
else{
    // Wrong file key or removed file
    echo '<p>Error: File not found.</p>';
}

// Close connection
$db = null;