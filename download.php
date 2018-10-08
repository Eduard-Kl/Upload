<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

function download(){

    global $db, $fileName, $targetFileFullPath, $fileKey;

    header('Content-Description: File Transfer');
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="' . $fileName . '";');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($targetFileFullPath));
	
    ob_clean();
    flush();
	
    // Increase number of downloads
	$statement = $db -> prepare('UPDATE file SET downloads = downloads + 1 WHERE keycode = :fileKey');
	$statement -> execute(array('fileKey' => $fileKey));
	
	// Update last accessed date
	$statement = $db -> prepare('UPDATE file SET lastView = ' . date('Y-m-d') . ' WHERE keycode = :fileKey');
    $statement -> execute(array('fileKey' => $fileKey));
    
    writeLog("DOWN\t" . $targetFileFullPath);
	
    readfile($targetFileFullPath);
}

if(isset($_GET['f'])){
	$fileKey = $_GET['f'];
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

//File exists?
if($row['filename'] == ''){
    exit('File you are looking for doesn\'t exist.');
}

// Password protected file
if($correctPassword != null){
    ?>
    <form action="<?php echo htmlspecialchars($_GET['f']);?>" method="post" enctype="multipart/form-data">
        Enter Password:
        <input type="password" name="optionalPassword" id="optionalPassword"/>
        <br>
        <input type="submit" name="submitPassword" value="Submit password"/>
    </form>
    <?php

    // If button 'Submit password' was clicked
    if(isset($_POST['submitPassword'])){
        $typedInPassword = $_POST['optionalPassword'];
        
        if(DEBUG)
            echo '<p>DEBUG: ' . $correctPassword . ' ' . $typedInPassword . ' ' . $targetFileFullPath . '</p>';

        // Download
        if(file_exists($targetFileFullPath) && $correctPassword == $typedInPassword){
            download();
        }
        else{
            echo '<p>You entered a wrong download password. Please Try Again.</p>';
        }
    }
}
// Download (no password)
else{
    if(file_exists($targetFileFullPath)){
        download();
    }
    else{
        echo '<p>Error: File not found.</p>';
    }
}

// Close connection
$db = null;