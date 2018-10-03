<?php

//require_once 'helperFunctions.php';

// Get the fine name based on delete code
function getFileName(string $deleteCode){

	// Open database connection
	require_once 'DB.php';
	$db = DB::getConnection();
	
	// Fetch from database (find file name based on $_GET['code'])
	$statement = $db -> prepare('SELECT filename FROM file WHERE deleteCode = :deleteCode');
	$statement -> execute(array('deleteCode' => $deleteCode));
	
	foreach($statement->fetchAll() as $row){
		if(DEBUG == true){
			$targetFileFullPath = DIRECTORY . $row['filename'];		
			echo '<p>Debug: ' . $targetFileFullPath . '</p>';
		}
	}

	// Close connection
	$db = null;

	return $row['filename'];
}

// Open database connection
require_once 'DB.php';
$db = DB::getConnection();

// Delete link was called. Ask for confirmation
	if(isset($_GET['code'])){
		$fileName = getFileName($_GET['code']);
		
		if(DEBUG == true)
			echo '<p>Debug: file ' . $fileName . ' to be deleted.</p>';
	
		?>
		<form action="deleteSuccess.php?code=<?php echo htmlspecialchars($_GET['code']);?>" method="post" enctype="multipart/form-data">
			<p>Are you sure you want to delete the file
				<?php
				echo $fileName;
				//$_SESSION['fileName'] = $fileName;
				?>?
			</p>
			<input type="submit" name="submitDelete" value="Delete file"/>
			<input type="hidden" name="fileName" value="<?php echo $fileName;?>"/>
		</form>
	<?php
	}

// Close connection
$db = null;
?>