<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

function checkFile(){
	
	global $fileName, $optionalPassword;

    // Check file name length
	if(strlen($fileName) > FILENAMELENGTH){
		echo '<p>Error: Maximum file name length is ' . FILENAMELENGTH .' characters.</p>';
		return false;
    }
    
    // Check password length
	if(strlen($optionalPassword) > PASSWORDLENGTH){
		echo '<p>Error: Maximum password length is ' . PASSWORDLENGTH . ' characters.</p>';
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

$optionalPassword = '';
if(isset($_POST["optionalPassword"])){
    $optionalPassword = $_POST["optionalPassword"];
}

if(!checkFile()){
    exit();
}

// Open database connection
$db = DB::getConnection();

// Generate unique file key and unique delete code
$fileKey = generateRandomKey();
$fileDeleteCode = $fileKey . rand(0, 9);

// Full path with extension and $fileKey to allow different files with same name
$targetFileFullPath = directory() . $fileKey . '-' . $fileName;

if(DEBUG)
    echo '<p>DEBUG: ' . directory() . ' ' . $fileName . ' ' . $optionalPassword . $_FILES['fileToUpload']['tmp_name'] . ' ' . $targetFileFullPath . '</p>';

// Upload
if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFileFullPath)){
    
    if(isset($_POST['removeMetadata'])){
        shell_exec('exiftool -all= "' . $targetFileFullPath . '" -overwrite_original');
    }
		
	// Inserting into DB
	$statement = $db -> prepare('INSERT INTO file (keycode, filename, password, uploadDate, lastView, location, deleteCode, size) VALUES(:keycode, :filename, :password, :uploadDate, :lastView, :location, :deleteCode, :size)');
    $statement -> execute(array('keycode' => $fileKey, 'filename' => $fileName, 'password' => $optionalPassword, 'uploadDate' => date('Y-m-d'), 'lastView' => date('Y-m-d'), 'location' => dirname($targetFileFullPath, 4), 'deleteCode' => $fileDeleteCode, 'size' => humanFilesize(filesize($targetFileFullPath))));
    // KEYLENGTH + 1 since name is in format fileKey-fileName
    // dirname($targetFileFullPath, 4): lose 'yyyy/mm/dd/'

    if(DEBUG){
        echo '<p>DEBUG: ' . $fileKey . ' ' . $fileName . ' ' . $optionalPassword . ' ' . date('Y-m-d') . ' ' . $fileDeleteCode . ' ' . humanFilesize(filesize($targetFileFullPath));
    }

    writeLog("UP\t" . $targetFileFullPath);

	// Close connection
	$db = null;
		
    echo '<p>The file '. e($fileName) . ' has been uploaded.</p>';    
    echo '<p>Download link: <a href="file/' . $fileKey . '">click</a></p>';
    echo '<p>Direct download link: <a href="download/' . $fileKey . '">click</a></p>';
    echo '<p>Delete link: <a href="delete/' . $fileDeleteCode . '">click</a></p>';
}else{

    // File is too big. Auto check via php.ini
    exit('Sorry, there was an error uploading your file.');
}