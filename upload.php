<?php
require_once 'constants.php';

function generateRandomKey(){
    global $db;

    // Repeat until unique $key is generated
    do{
        $a = rand(0,9);
        $b = rand(0,9);
        $c = rand(0,9);
        $d = rand(0,9);
        $e = rand(0,9);
        $f = rand(0,9);
        $key = "".$a.$b.$c.$d.$e.$f;

        // Fetch from database. Does a file with $key already exist?
        $statement = $db -> prepare("SELECT COUNT(*) FROM file WHERE keycode = :fileKey");
        $statement -> execute(array('fileKey' => $key));
        
        $dataExists = $statement->fetchColumn();

    } while($dataExists >= 1);
    
    return $key;
}

// With extension
$fileName = basename($_FILES["fileToUpload"]["name"]);
if(isset($_POST["optionalPassword"])){
    $optionalPassword = $_POST["optionalPassword"];
}

// Check file size
if($_FILES["fileToUpload"]["size"] > MAXFILESIZE){
    $uploadOk = false;
    exit('Error: File is too large.');
}

// Check file name length
if(strlen($fileName) > 255){
    $uploadOk = false;
    exit('Error: Maximum file name length is 255 characters.');
}

// Full path with extension
// DIRECTORY . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $fileName;
$targetFileFullPath = DIRECTORY . $fileName;
$uploadOk = true;

if(DEBUG == true)
    echo "<p>Debug: " . DIRECTORY . " " . $fileName . " " . $optionalPassword . " " . $targetFileFullPath . "</p>";

// Open database connection
require_once 'DB.php';
$db = DB::getConnection();

// Generate a unique file key
$fileKey = generateRandomKey();

// Inserting into DB
$statement = $db -> prepare("INSERT INTO file (filename, keycode, password, uploadDate)
VALUES(:filename, :keycode, :password, :uploadDate)");
$statement -> execute(array('filename' => $fileName, 'keycode' => $fileKey, 'password' => $optionalPassword, 'uploadDate' => date('Y-m-d')));

// Close connection
$db = null;

if($uploadOk === true){
    if(DEBUG == true)
        echo "<p>Debug: " . $_FILES["fileToUpload"]["tmp_name"] . " " . $targetFileFullPath . "</p>";
    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFileFullPath)){
        echo "The file ". $fileName . " has been uploaded. <br>";
        if($optionalPassword != NULL){
            echo '<p>Your Download link: <a href="passcheck.php?f=' . $fileKey . '" />click<p>';
        }
        else{
            echo '<p>Your Download link: <a href="download.php?f=' . $fileKey . '" />click<p>';
        }
    }else{
        exit('Sorry, there was an error uploading your file.');
    }
}
?>