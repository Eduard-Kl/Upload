<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';

if(isset($_GET['f'])){
	$fileKey = $_GET['f'];
}

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Fetch from database (find $fileName based on $fileKey)
$statement = $db -> prepare("SELECT filename FROM file WHERE keycode = :fileKey");
$statement -> execute(array('fileKey' => $fileKey));

foreach($statement->fetchAll() as $row){
    $targetFileFullPath = DIRECTORY . $row['filename'];
    if(DEBUG == true)
        echo '<p>DEBUG: ' . $targetFileFullPath . '</p>';
}

//File exists?
if($row['filename'] == ''){
    echo '<p>Error: File you are looking for doesn\'t exist.</p>';
    exit();
}

// Download
if(file_exists($targetFileFullPath)){
    header('Content-Description: File Transfer');
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="' . basename($targetFileFullPath) . '";');
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
	
    readfile($targetFileFullPath);
}else{
    echo '<p>Error: File not found.</p>';
}

// Close connection
$db = null;