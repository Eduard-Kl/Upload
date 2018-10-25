<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

if(!isset($_GET['code'])){
	toHomePage();
}

// Delete link was called. Ask for confirmation
if( isset($_GET['code']) && !isset($_POST['submitDelete']) ){
	
	// Open database connection	
	$db = DB::getConnection();

	// Fetch from database (find keycode, file name based on $deleteCode)
	$statement = $db -> prepare('SELECT keycode, filename FROM file WHERE deleteCode = :deleteCode');
	$statement -> execute(array('deleteCode' => $_GET['code']));

	$fileName = '';
	
	// Delete code is unique
	foreach($statement->fetchAll() as $row){		
		$fileKey = $row['keycode'];
		$fileName = $row['filename'];
	}

	// Close connection
	$db = null;

	// Wrong delete code
	if($fileName === ''){
		echo '<p>Error: Wrong delete code. File not found.</p>';
		exit();
	}

	?>
	<form action="<?php echo e($_GET['code']);?>" method="post" enctype="multipart/form-data">
		<p>Are you sure you want to delete the file	<?php echo e($fileName);?>?</p>
		<input type="submit" name="submitDelete" value="Delete file"/>
		<input type="hidden" name="keycode" value="<?php echo $fileKey;?>"/>
	</form>
<?php
}

// Button 'Delete file' was clicked
if( isset($_GET['code']) && isset($_POST['submitDelete']) ){

	// Open database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
	$db = DB::getConnection();

	delete($db, $_POST['keycode']);

	// Close connection
	$db = null;
}
?>