<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/helperFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';

if(isset($_GET['f'])){
	$fileKey = $_GET['f'];
}
else{
    toHomePage();
}

// Open database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/DB.php';
$db = DB::getConnection();

// Fetch from database (find $fileName, $correctPassword, $size based on $fileKey)
$statement = $db -> prepare("SELECT filename, password, size FROM file WHERE keycode = :fileKey");
$statement -> execute(array('fileKey' => $fileKey));

foreach($statement->fetchAll() as $row){
    $fileName = $row['filename'];
    $targetFileFullPath = directory() . $fileKey . '-' . $fileName;
    $correctPassword = $row['password'];
    $size = $row['size'];
    if(DEBUG)
        echo '<p>DEBUG: ' . $targetFileFullPath . ' ' . $correctPassword . ' ' . $size . '</p>';
}

//File exists?
if($row['filename'] == null ){
    exit('File you are looking for doesn\'t exist.');
}

// File info
echo '<p>' . e($fileName) . ' ' . $size . '</p>';

// Password protected file
if($correctPassword != null){
    ?>
    <form action="/download/<?php echo $fileKey; ?>" method="post" enctype="multipart/form-data">
        Enter Password:
        <input type="password" name="optionalPassword" id="optionalPassword"/>
        <br>
        <input type="submit" name="submitPassword" value="Submit password"/>
    </form>
    <?php
}
else{
    echo '<a href="/download/' . $fileKey . '">Download</a>';
}

// Close connection
$db = null;