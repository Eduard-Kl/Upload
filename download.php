<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

if(isset($_GET['f'])){
	$fileKey = $_GET['f'];
}
else{
    toHomePage();
}

// Open database connection
$db = DB::getConnection();

// Fetch from database (get file info based on $fileKey)
$statement = $db -> prepare('SELECT filename, password, uploadDate, location FROM file WHERE keycode = :fileKey');
$statement -> execute(array('fileKey' => $fileKey));

$targetFileFullPath = '';

foreach($statement->fetchAll() as $row){
    $fileName = $row['filename'];
    $targetFileFullPath = $row['location'] . superExplode('-', $row['uploadDate']) . $fileKey . '-' . $fileName;
    $correctPassword = $row['password'];
    if(DEBUG)
        echo '<p>DEBUG: ' . $targetFileFullPath . ' ' . $correctPassword . '</p>';
}

// Download
if(file_exists($targetFileFullPath)){

    // Password protected file
    if($correctPassword != null){

        if(isset($_POST['optionalPassword']) && $correctPassword == $_POST['optionalPassword']){
            
            if(DEBUG)
                echo '<p>DEBUG: ' . $correctPassword . ' ' . $_POST['optionalPassword'] . ' ' . $targetFileFullPath . '</p>';

            // Download (correct password)
            if(isset($_POST['optionalSecureDownload'])){
                download($db, pathinfo($fileName, PATHINFO_FILENAME) . '.zip', createzip($targetFileFullPath), $fileKey, true);
            }
            else{
                download($db, $fileName, $targetFileFullPath, $fileKey);
            }
        }
        else{
            header('Location: /file/' . $fileKey);
        }
    }
    // Download (no password)
    else{
        if(isset($_POST['optionalSecureDownload'])){
            download($db, pathinfo($fileName, PATHINFO_FILENAME) . '.zip', createzip($targetFileFullPath), $fileKey, true);
        }
        else{
            download($db, $fileName, $targetFileFullPath, $fileKey);
        }
    }
}
else{
    // Wrong file key or removed file
    echo '<p>Error: File not found.</p>';
}

// Close connection
$db = null;