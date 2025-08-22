<?php
session_start();
session_unset();
session_destroy();

$_SESSION["admin_uid"] = "";
$_SESSION["admin_pwd"] = "";
$_SESSION["admin_id"] = "";
$_SESSION["user_type_new"] = "";
	
header("location:index.php");
?>