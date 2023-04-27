<?php 
session_start();
$db_host = 'localhost:3307';//Write your host or ip address
$db_user = 'root';// username
$db_password = '';//password
$db_database = 'edu';//name of database
$con = mysqli_connect($db_host,$db_user,$db_password,$db_database) or die ("could not connect database");
$mysqli = new mysqli($db_host, $db_user,$db_password,$db_database);
try {
$db = new PDO("mysql:host=$db_host;dbname=$db_database", $db_user, $db_password);
// set the PDO error mode to exception
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//echo "Connected successfully"; 
}
catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}

define('TBLTAG', 'tag');
//define('TBLTAG', 'tag');
$tag="CRM Educationvibes";
?>