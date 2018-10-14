<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

// Check given information in textboxes
function checkInput(){
	
	if($_POST['link'] == '' || $_POST['info'] == ''){
		echo '<p>Please fill out the required forms.</p>';
		return false;
	}

	// Link must contain KEYLENGTH consecutive numbers
	if(!preg_match('*[0-9]{' . KEYLENGTH . '}*', $_POST['link'])){
		echo '<p>File to report field must contain either a full link or a file key.</p>';
		return false;
	}
	
	// Check link length
	if(strlen($_POST['link']) > LINKLENGTH){
		echo '<p>Report link cannot exceed ' . LINKLENGTH . ' characters.</p>';
		return false;
	}
	
	// Check password length
	if(strlen($_POST['optionalPassword']) > PASSWORDLENGTH){
		echo '<p>Password cannot exceed ' . PASSWORDLENGTH . ' characters.</p>';
		return false;
	}
	
	// Check name length
	if(strlen($_POST['name']) > NAMELENGTH){
		echo '<p>Name cannot exceed ' . NAMELENGTH .' characters.</p>';
		return false;
	}

	// Check info length
	if(strlen($_POST['info']) > 65535){
		echo '<p>Your message cannot exceed 65535 characters.</p>';
		return false;
	}
	
	return true;
}

?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
	<p>If reporting multiple files, please report them one by one.</p>
	
	File to report: *
	<input type="text" name="link" placeholder="File code or full link"/>
	<br>
	
	Download password (if set):
	<input type="password" name="optionalPassword"/>
	<br>
	
	Your name:
	<input type="textbox" name="name" value="<?php echo $_POST['name'];?>"/>
	<br>
	
	Your message: *
	<input type="textbox" name="info" value=""/>
	
	<input type="submit" name="submitButton" value="Report"/>
	
	<br>
	<p>Fields marked with * are mandatory.</p>
</form>

<?php

// Button 'Report' was clicked and all information is ok
if(isset($_POST['submitButton']) && checkInput() === true){		
	
	// Open database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
	$db = DB::getConnection();
	
	// Inserting into DB
	$statement = $db -> prepare('INSERT INTO report (link, password, name, info, reportDate)
	VALUES(:link, :password, :name, :info, :reportDate)');
	$statement -> execute(array
	('link' => $_POST['link'], 'password' => $_POST['optionalPassword'], 'name' => $_POST['name'], 'info' => $_POST['info'], 'reportDate' => date('Y-m-d H:i:s')));
	
	echo '<p>Your report has been filed.</p>';
	
	// Close connection
	$db = null;
}
?>