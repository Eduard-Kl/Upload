<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?f=' . htmlspecialchars($_GET['f']);?>" method="post" enctype="multipart/form-data">
    Enter Password:
    <input type="password" name="optionalPassword" id="optionalPassword"/>
    <br>
    <input type="submit" name="submitPassword" value="Submit password"/>
</form>	

<?php
// If button 'Submit password' was clicked
if(isset($_POST['submitPassword'])){

    $fileKey = $_GET['f'];

    if(DEBUG == true)
        echo '<p>Debug: ' . $fileKey . '</p>';

    $typedInPassword = $_POST['optionalPassword'];

    // Open database connection
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
    $db = DB::getConnection();

    // Fetch from database (find $fileName and $correctPassword based on $fileKey)
    $statement = $db -> prepare("SELECT filename, password FROM file WHERE keycode = :fileKey");
    $statement -> execute(array('fileKey' => $fileKey));

    foreach($statement->fetchAll() as $row ){
        $targetFileFullPath = DIRECTORY . $row['filename'];
        $correctPassword = $row['password'];
    }

    if(DEBUG == true)
        echo '<p>Debug: ' . $targetFileFullPath . ' ' . $typedInPassword . ' ' . $correctPassword . '</p>';

    // Download
    if(file_exists($targetFileFullPath) && $correctPassword == $typedInPassword){
        header('Content-Description: File Transfer');
        header('Content-Type: application/force-download');
        header("Content-Disposition: attachment; filename=\"" . basename($targetFileFullPath) . "\";");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($targetFileFullPath));
        ob_clean();
        flush();

        // Increase number of downloads
        $statement = $db -> prepare("UPDATE file SET downloads = downloads + 1 WHERE keycode = :fileKey");
        $statement->execute( array('fileKey' => $fileKey));

        // Update last accessed date
        $statement = $db -> prepare('UPDATE file SET lastView = ' . date('Y-m-d') . ' WHERE keycode = :fileKey');
        $statement -> execute(array('fileKey' => $fileKey));

        readfile($targetFileFullPath); 
    }else{
        echo '<p>You entered a wrong download password. Please Try Again.</p>';
    }

    // Close connection
    $db = null;
}
?>