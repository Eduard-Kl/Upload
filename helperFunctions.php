<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';

function writeLog(string $message){
	$logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
	$directory = DIRECTORY . date('Y') . '/Logs/';
	
	if(!file_exists($directory)){
		// Check permissions. 0757? 0706 doesn't work
		mkdir($directory, 0757, true);
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

// Main directory for saving files
function directory(){
	$directory = DIRECTORY . date('Y') . '/' . date('m') . '/' . date('d') . '/';

	if(!file_exists($directory)){
		// Check permissions. 0747? 0706 doesn't work
		mkdir($directory, 0747, true);
	}
	return $directory;
}

// Working directory for temp files. To be changed later
function workingDirectory(){
	$directory = DIRECTORY . date('Y') . '/';

	if(!file_exists($directory)){
		// Check permissions. 0747? 0706 doesn't work
		mkdir($directory, 0747, true);
	}
	return $directory;
}

// Determine number of bytes to be added in a zip file
function addBytes($size){

	// $size is < 10 MB
	if($size < 10485760){
	
		// Random from 6% to 10%
		$percentage = (rand(6, 10) + rand(0, 9) / 10 + rand(0, 9) / 100) / 100;	
	}
	// $size is in [10MB, 100MB]
	elseif($size < 104857600){
	
		// Random from 3% to 7%
		$percentage = (rand(3, 7) + rand(0, 9) / 10 + rand(0, 9) / 100) / 100;
		}
	// $size is > 100 MB
	else{
		
		// Random from 1% to 4%
		$percentage = (rand(1, 4) + rand(0, 9) / 10 + rand(0, 9) / 100) / 100;
	}
	return floor($percentage * $size);
}

// Create a zip file that contains $targetFileFullPath and a dummy file of random size
function createzip($targetFileFullPath){

	/*
	Examples:
		base64 /dev/urandom | head -c 4000000 > 4mb.txt
		dd if=/dev/zero of=/tmp/dummy123456 bs=1024 count=1
		zip -9 /tmp/DL.zip /tmp/file.txt /tmp/dummy123456
	*/

	$fileKey = substr(basename($targetFileFullPath), 0, KEYLENGTH);

	// Create dummy 1
	shell_exec('base64 /dev/urandom | head -c ' . addBytes(filesize($targetFileFullPath)) . ' > "' . workingDirectory() . $fileKey . '-DELETE ME.txt"');

	// Create dummy 2
	//shell_exec('dd if=/dev/urandom of="/tmp/' . $fileKey . '-DELETE ME" bs=' . addBytes(filesize($targetFileFullPath)) . ' count=1');

	$zip = workingDirectory() . $fileKey . '.zip';

	// Create zip. -9 = highest compression level, -j = don't keep folder structure
	shell_exec('zip -9 -j ' . $zip . ' "' . $targetFileFullPath . '" "' . workingDirectory() . $fileKey . '-DELETE ME.txt"');

	return $zip;
}

function e($x){
	return htmlspecialchars($x, ENT_QUOTES, 'UTF-8');
}