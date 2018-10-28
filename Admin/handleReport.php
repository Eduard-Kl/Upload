<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

function writePath($fileKey){
	
	global $textOnlyLink, $statementFile;
	
	// Already prepared
	$statementFile -> execute(array('fileKey' => $fileKey));

	// Unique
	foreach($statementFile->fetchAll() as $rowFile){
		// File exists
		if($rowFile['fileName'] != ''){
			$textOnlyLink = false;
			$targetFileFullPath= $rowFile['location'] . superExplode('-', $rowFile['uploadDate']) . $fileKey . '-' . e($rowFile['fileName']);
			echo '<td><font color="white">"' . $targetFileFullPath . '"</font></td>';
		}
	}
}

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Button 'Check' was pressed
if(isset($_POST['submitChecked'])){

	// Set checked to true
	$statement = $db -> prepare('UPDATE report SET checked = 1 WHERE id = :id');
	$statement -> execute(array('id' => $_POST['fileID']));
	
	if(DEBUG){
		echo '<p>DEBUG: report ' . $_POST['fileID'] . ' checked.</p>';
	}
}

// Button 'Remove' was pressed
if(isset($_POST['submitRemoved'])){

	// Set removed to true. Checked is also true
	$statement = $db -> prepare('UPDATE report SET removed = 1, checked = 1 WHERE id = :id');
	$statement -> execute(array('id' => $_POST['fileID']));
	
	if(DEBUG){
		echo '<p>DEBUG: report ' . $_POST['fileID'] . ' removed.</p>';
	}
}

// Number of unhandled reports
$statement = $db -> query('SELECT COUNT(*) FROM report WHERE checked = 0');
echo '<p>Unhandled reports: ' . $statement->fetchColumn() . '.</p>';

// Fetch reports
$statement = $db -> query('SELECT id, link, name, info, reportDate FROM report WHERE checked = 0 ORDER BY reportDate LIMIT 10');

// Prepare $statementFile for file info lookup. Is used only if report.link is in proper format (in the following foreach() loop)
$statementFile = $db -> prepare('SELECT fileName, uploadDate, location FROM file WHERE keycode = :fileKey');

// Draw table
echo '<table><tr>';
echo '<th>id</th>';
echo '<th>link</th>';
echo '<th>name</th>';
echo '<th>info</th>';
echo '<th>reportDate</th>';
echo '</tr>';
foreach($statement->fetchAll() as $row){

	$textOnlyLink = true;
	echo '<tr><td>' . $row['id'] . '</td>';

	// Preffered format. $row['link'] is in format 123456
	if( preg_match('/^[0-9]{' . KEYLENGTH . '}/', $row['link']) ){		
		writePath($row['link']);
	}
	
	// Also acceptable. $row['link'] is in format https://.../file/123456
	// /i means case-insensitive
	elseif( preg_match('/(https:\/\/)[a-z]*\/(file)\/[0-9]{' . KEYLENGTH . '}/i', $row['link']) ){
		writePath(substr($row['link'], -KEYLENGTH));
	}
	
	// General format. Could be any text
	if($textOnlyLink){
		echo '<td>' . e($row['link']) . '</td>';
	}
	
	echo '<td>' . e($row['name']) . '</td>';
	
	// Limit to 255 characters
	echo '<td>' . substr(e($row['info']), 0, 255) . '</td>';
	echo '<td>' . $row['reportDate'] . '</td>';
	
	echo '<td><form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">';
	echo 	'<input type="submit" name="submitChecked" value="Check"/>';	
	echo 	'<input type="submit" name="submitRemoved" value="Remove"/>';	
	echo 	'<input type="hidden" name="fileID" value="' . $row['id'] . '"/>';
	echo '</form></td></tr>';
}
echo '</table>';

// Close connection
$db = null;