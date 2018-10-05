<?php
require_once 'constants.php';

function writeLog(string &$message){
	$logMessage = date('Y-m-d H:i:s') . ' - ' . $message; 
	file_put_contents(DIRECTORY . date('Y-m-d') . '.log', $logMessage, FILE_APPEND);
}

function humanFilesize(&$bytes, $decimals = 2){

	$size = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	
	if($factor == 0)
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
	else
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor] . 'B';
}