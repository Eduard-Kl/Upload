<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
    <input type="textbox" name="filesToDelete"/>
    <input type="submit" name="submitDelete" value="Delete files"/>
</form>

<?php
if(isset($_POST['submitDelete'])){
    $filesToDelete = explode(',', $_POST['filesToDelete']);

    // Open database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
    $db = DB::getConnection();

    foreach($filesToDelete as &$fileKey){
        delete($db, $fileKey);
    }

    // Close connection
	$db = null;
}
?>