<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

// Get the fine name based on delete code
function getFileName(string $deleteCode){

	global $db;
	
	// Fetch from database (find file name based on htmlentities(htmlentities($_GET['code'])))
	$statement = $db -> prepare('SELECT filename FROM file WHERE deleteCode = :deleteCode');
	$statement -> execute(array('deleteCode' => $deleteCode));
	
	foreach($statement->fetchAll() as $row){
		if(DEBUG == true){
			$targetFileFullPath = DIRECTORY . $row['filename'];		
			echo '<p>DEBUG: file ' . $targetFileFullPath . ' found.</p>';
		}
	}	
	
	return $row['filename'];
}

if(!isset($_GET['code'])){
	toHomePage();
}

// Delete link was called. Ask for confirmation
if( isset($_GET['code']) && !isset($_POST['submitDelete']) ){
	
	// Open database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
	$db = DB::getConnection();

	$fileName = getFileName(htmlentities($_GET['code']));

	// Close connection
	$db = null;

	// Wrong delete code
	if($fileName == ''){
		echo '<p>Error: Wrong delete code. File not found.</p>';
		exit();
	}

	if(DEBUG)
		echo '<p>DEBUG: file ' . $fileName . ' to be deleted.</p>';
	?>
	<form action="<?php echo htmlspecialchars(htmlentities($_GET['code']));?>" method="post" enctype="multipart/form-data">
		<p>Are you sure you want to delete the file	<?php echo $fileName;?>?</p>
		<input type="submit" name="submitDelete" value="Delete file"/>
		<input type="hidden" name="fileName" value="<?php echo $fileName;?>"/>
	</form>
<?php
}

// Button 'Delete file' was clicked
if( isset($_GET['code']) && isset($_POST['submitDelete']) ){

	// Open database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
	$db = DB::getConnection();

	// Delete from database
    $statement = $db -> prepare('DELETE FROM file WHERE deleteCode = :deleteCode');
	$statement -> execute(array('deleteCode' => htmlentities($_GET['code'])));	
	
	// Close connection
	$db = null;
	
	// Get file key based on delete code. Remove last character. To be changed later
    $targetFileFullPath = DIRECTORY . substr(htmlentities($_GET['code']), 0, -1) . '-' . $_POST['fileName'];
    
    if(DEBUG)
        echo '<p>DEBUG: ' . $targetFileFullPath . ' is being deleted.</p>';
	
	// Delete from drive
	if(file_exists($targetFileFullPath) && unlink($targetFileFullPath) == true){
		echo '<p>File ' . $_POST['fileName'] . ' has been deleted.</p>';
		writeLog("DEL\t" . $targetFileFullPath);
	}
}
?>