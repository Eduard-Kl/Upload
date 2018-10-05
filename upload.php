<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';

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
	
	global $fileName;
	
	// Check file size
	if($_FILES["fileToUpload"]["size"] > MAXFILESIZE){
		echo '<p>Error: Maximum allowed file size is ' .  MAXFILESIZE . ' bytes (' . humanFilesize(MAXFILESIZE) . ')</p>';
		return false;
	}

	// Check file name length
	if(strlen($fileName) > 255){
		echo '<p>Error: Maximum file name length is 255 characters.</p>';
		return false;
	}
	
	return true;
}

// With extension
$fileName = basename($_FILES["fileToUpload"]["name"]);

if(isset($_POST["optionalPassword"])){
    $optionalPassword = $_POST["optionalPassword"];
}

// Full path with extension
$targetFileFullPath = DIRECTORY . $fileName;

if(DEBUG == true)
    echo "<p>DEBUG: " . DIRECTORY . " " . $fileName . " " . $optionalPassword . " " . $targetFileFullPath . "</p>";
	
// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Generate unique file key and unique delete code
$fileKey = generateRandomKey();
$fileDeleteCode = $fileKey . rand(0, 9);

// Upload
if(checkFile() === true){
    if(DEBUG == true)
        echo "<p>DEBUG: " . $_FILES["fileToUpload"]["tmp_name"] . " " . $targetFileFullPath . "</p>";
		
    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFileFullPath)){
        if(isset($_POST['removeMetadata'])){
            $output = shell_exec('exiftool -all= "' . $targetFileFullPath . '" -overwrite_original');
            if(DEBUG == true){
                echo $output;
                echo '<p>DEBUG: metadata removed.</p>';
            }
        }
		
		// Inserting into DB
		$statement = $db -> prepare('INSERT INTO file (keycode, filename, password, uploadDate, lastView, deleteCode, size)	VALUES(:keycode, :filename, :password, :uploadDate, :lastView, :deleteCode, :size)');
        $statement -> execute(array('keycode' => $fileKey, 'filename' => $fileName, 'password' => $optionalPassword, 'uploadDate' => date('Y-m-d'), 'lastView' => date('Y-m-d'), 'deleteCode' => $fileDeleteCode, 'size' => humanFilesize(filesize($targetFileFullPath))));
        
        if(DEBUG == true){
            echo '<p>DEBUG: ' . $fileKey . ' ' . $fileName . ' ' . $optionalPassword . ' ' . date('Y-m-d') . ' ' . $fileDeleteCode . ' ' . humanFilesize(filesize($targetFileFullPath));
        }

		// Close connection
		$db = null;
		
        echo '<p>The file '. $fileName . ' has been uploaded.</p>';
		
        if($optionalPassword != NULL){
            echo '<p>Your Download link: <a href="passcheck.php?f=' . $fileKey . '">click</a></p>';
        }
        else{
            echo '<p>Your Download link: <a href="download.php?f=' . $fileKey . '">click</a></p>';
        }
        echo '<p>Delete link: <a href="delete.php?code=' . $fileDeleteCode . '">click</a></p>';
    }else{
        exit('Sorry, there was an error uploading your file.');
    }
}
?>