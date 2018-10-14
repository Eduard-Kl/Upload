<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';

function writeLog(string $message){
	$logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
	$directory = DIRECTORY . date('Y') . '/Logs/';
	
	if(!file_exists($directory)){
		// Check permissions. 0706?
		mkdir($directory, 0706, true);
	}
	file_put_contents($directory . date('Y-m-d') . '.log', $logMessage, FILE_APPEND);
}

function humanFilesize($bytes, $decimals = 2){

	$size = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	
	if($factor)
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor] . 'B';
	else
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

function toHomePage(){
	header('Location: index');
    exit();
}

function download($db, $fileName, $targetFileFullPath, $fileKey){

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
	
	// Close connection
	$db = null;
}

function directory(){
	$directory = DIRECTORY . date('Y') . '/' . date('m') . '/' . date('d') . '/';

	if(!file_exists($directory)){
		// Check permissions. 0706?
		mkdir($directory, 0706, true);
	}
	return $directory;
}