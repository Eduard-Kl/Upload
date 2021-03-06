<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Database/databaseInfo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/constants.php';

class DB{
	// Private PDO object
	private static $db = null;
	
	// Forbid new DB() and cloning
	final private function __construct(){}
	final private function __clone(){}
	
	// Static function for database access
	public static function getConnection(){
		// Connect only once
		if(DB::$db === null){
			// Global connection parameters from databaseInfo.php
			global $DBservername, $DBname, $DBusername, $DBpassword;
			try{				
				if(DEBUG)
					echo '<p>DEBUG: PDO created.</p>';
				// First parameter = 'mysql:host=localhost;dbname=myDB;charset=utf8';
				DB::$db = new PDO('mysql:host=' . $DBservername . ';dbname=' . $DBname . ';charset=utf8mb4', $DBusername, $DBpassword);
			} catch(PDOException $e){
				echo '<p>Connection failed: ' . $e->getMessage() . '</p>';
				exit();
			}
		}
		return DB::$db;
	}

	final function __destruct(){
        DB::$db = null;
    }
}