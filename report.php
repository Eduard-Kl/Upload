<?php
require_once 'constants.php';
//require_once 'helperFunctions.php';

// Check given information in textboxes
function checkInput(){
	
	if(!isset($_POST['link']) || !isset($_POST['info'])){
		echo '<p>Please fill out the required forms.</p>';
		return false;
	}
	
	// Check link length
	if(strlen($_POST['link'] > 255)){
		echo '<p>Report link cannot exceed 255 characters.</p>';
		return false;
	}
	
	// Check password length
	if(strlen($_POST['password'] > 32)){
		echo '<p>Password cannot exceed 32 characters.</p>';
		return false;
	}
	
	// Check name length
	if(strlen($_POST['name'] > 40)){
		echo '<p>Name cannot exceed 40 characters.</p>';
		return false;
	}
	
	// Check info length
	if(strlen($_POST['info'] > 65535)){
		echo '<p>Your message cannot exceed 65535 characters.</p>';
		return false;
	}
	
	return true;
}

// Button 'Report' was clicked and all information is ok
if(isset($_POST['submitButton']) && checkInput() === true){		
	
	// Open database connection
	require_once 'DB.php';
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

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" enctype="multipart/form-data">
	<p>If reporting multiple files, please report them one by one.</p>
	
	File to report: *
	<input type="text" name="link" placeholder="File code or full link" value="<?php echo $_POST['link'];?>"/>
	<br>
	
	Download password (if set):
	<input type="password" name="optionalPassword" value="<?php echo $_POST['optionalPassword'];?>"/>
	<br>
	
	Your name:
	<input type="textbox" name="name" value="<?php echo $_POST['name'];?>"/>
	<br>
	
	Your message: *
	<input type="textbox" name="info" value="<?php echo $_POST['info'];?>"/>
	
	<input type="submit" name="submitButton" value="Report"/>
	
	<br>
	<p>Fields marked with * are mandatory.</p>
</form>