<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

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

function checkFile(){
	
	global $fileName, $optionalPassword;

    // Check file name length
    // Limit on linux is 142 characters. 142 - 7 = 135. 7 for file key and '-'
	if(strlen($fileName) > 135){
		echo '<p>Error: Maximum file name length is 135 characters.</p>';
		return false;
    }
    
    // Check password length
	if(strlen($optionalPassword) > 32){
		echo '<p>Error: Maximum password length is 32 characters.</p>';
		return false;
    }
	
	return true;
}

if(!isset($_POST['submitButton'])){
    toHomePage();
}

// Button 'Upload' was pressed with no file selected
if( $_FILES['fileToUpload']['name'] == '' && $_FILES['fileToUpload']['type'] == '' ){
    //exit('You need to select a file to upload.');
    toHomePage();
}

// With extension
$fileName = basename($_FILES["fileToUpload"]["name"]);

if(isset($_POST["optionalPassword"])){
    $optionalPassword = $_POST["optionalPassword"];
}

if(checkFile() === false){
    exit();
}

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Generate unique file key and unique delete code
$fileKey = generateRandomKey();
$fileDeleteCode = $fileKey . rand(0, 9);

// Full path with extension and $fileKey to allow different files with same name
$targetFileFullPath = DIRECTORY . $fileKey . '-' . $fileName;

if(DEBUG)
    echo '<p>DEBUG: ' . DIRECTORY . ' ' . $fileName . ' ' . $optionalPassword . $_FILES['fileToUpload']['tmp_name'] . ' ' . $targetFileFullPath . '</p>';

// Upload
if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFileFullPath)){
    
    if(isset($_POST['removeMetadata'])){
        $utput = shell_exec('exiftool -all= "' . $targetFileFullPath . '" -overwrite_original');
        if(DEBUG){
            echo '<p>DEBUG: metadata removed. Status: ' . $output . '</p>';
        }
    }
		
	// Inserting into DB
	$statement = $db -> prepare('INSERT INTO file (keycode, filename, password, uploadDate, lastView, deleteCode, size)	VALUES(:keycode, :filename, :password, :uploadDate, :lastView, :deleteCode, :size)');
    $statement -> execute(array('keycode' => $fileKey, 'filename' => $fileName, 'password' => $optionalPassword, 'uploadDate' => date('Y-m-d'), 'lastView' => date('Y-m-d'), 'deleteCode' => $fileDeleteCode, 'size' => humanFilesize(filesize($targetFileFullPath))));
    // KEYLENGTH + 1 since name is in format fileKey-fileName

    if(DEBUG){
        echo '<p>DEBUG: ' . $fileKey . ' ' . $fileName . ' ' . $optionalPassword . ' ' . date('Y-m-d') . ' ' . $fileDeleteCode . ' ' . humanFilesize(filesize($targetFileFullPath));
    }

    writeLog("UP\t" . $targetFileFullPath);

	// Close connection
	$db = null;
		
    echo '<p>The file '. $fileName . ' has been uploaded.</p>';		
    echo '<p>Your download link: <a href="download/' . $fileKey . '">click</a></p>';
    echo '<p>Delete link: <a href="delete/' . $fileDeleteCode . '">click</a></p>';
}else{

    // File is too big. Auto check via php.ini
    exit('Sorry, there was an error uploading your file.');
}
?>