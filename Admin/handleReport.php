<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Button 'Check' was pressed
if(isset($_POST['submitChecked'])){

	// Set checked to true
	$statement = $db -> prepare('UPDATE report SET checked =  1 WHERE id = :id');
	$statement -> execute(array('id' => $_POST['fileID']));
	
	if(DEBUG){
		echo '<p>DEBUG: report ' . $_POST['fileID'] . ' checked.</p>';
	}
}

// Button 'Remove' was pressed
if(isset($_POST['submitRemoved'])){

	// Set removed to true. Checked is also true
	$statement = $db -> prepare('UPDATE report SET removed =  1, checked = 1 WHERE id = :id');
	$statement -> execute(array('id' => $_POST['fileID']));
	
	if(DEBUG){
		echo '<p>DEBUG: report ' . $_POST['fileID'] . ' removed.</p>';
	}
}

// Number of new reports
$statement = $db -> query('SELECT COUNT(*) FROM report WHERE checked  = 0');
echo '<p>Unhandled reports: ' . $statement->fetchColumn() . '.</p>';

// Fetch reports
$statement = $db -> query('SELECT id, link, name, info, reportDate FROM report WHERE checked  = 0  ORDER BY reportDate LIMIT 10');
//$statement = $db -> prepare('SELECT id, link, name, info, reportDate FROM report WHERE checked  = 0  ORDER BY reportDate LIMIT 10');
//$statement -> execute();

// Draw table
echo '<table><tr>';
echo '<th>id</th>'; 
echo '<th>link</th>'; 
echo '<th>name</th>'; 
echo '<th>info</th>'; 
echo '<th>reportDate</th>'; 
echo '</tr>';
foreach($statement->fetchAll() as $row){
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data"><tr>';
	
	echo 	'<td>' . e($row['id']) . '</td>';
	echo 	'<td>' . e($row['link']) . '</td>';
	echo 	'<td>' . e($row['name']) . '</td>';
	
	// Limit to 255 characters
	echo 	'<td>' . substr(e($row['info']), 0, 255) . '</td>';		
	echo 	'<td>' . e($row['reportDate']) . '</td>';
	
	echo 	'<td><input type="submit" name="submitChecked" value="Check"/></td>';	
	echo 	'<td><input type="submit" name="submitRemoved" value="Remove"/></td>';
	
	echo 	'<td><input type="hidden" name="fileID" value="' . $row['id'] . '"/></td>';
	
	echo '</tr></from>';
}
echo '</table>';

// Close connection
$db = null;