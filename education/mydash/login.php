<?php
session_start();
include "includes/connection.php";
$admin_uid=$_POST['fld_username'];
$admin_pwd=$_POST['fld_password'];

$sql = "SELECT * from admin WHERE uid=? and pwd=?";
$stmt = $db->prepare($sql);
$stmt->bindParam(1, $admin_uid, PDO::PARAM_STR);
$stmt->bindParam(2, $admin_pwd, PDO::PARAM_STR);
$stmt->execute();
$numrow = $stmt->rowCount();
//exit;
if($numrow > 0)
{	
	$col = $stmt->fetch();
	$_SESSION["admin_id"] = $col['admin_id'];
	$_SESSION["admin_uid"] = $col['uid'];
	$_SESSION["user_type_new"] = $col['user_type'];
	$_SESSION["belongsto"] = $col['belongsto'];
	
	$_SESSION['userid']=$col['admin_id'];
	$_SESSION["city"] = $col['city'];
	$admin_id = $col['admin_id'];
	
	//$admin_id = $col['admin_id'];
	$log_date = date('y-m-d');
	$log_time = date('h:i:s');
	
	$ip_add = $_SERVER['REMOTE_ADDR'];
	$browser= $_SERVER['HTTP_USER_AGENT'];
	$sql_log = "insert into admin_logs
			(
				admin_id,
				log_date,
				log_time,
				ip_address,
				browser
			) 
			values 
			(
				'$admin_id',
				'$log_date',
				'$log_time',
				'$ip_add',
				'$browser'
			)";
	$result_log = mysqli_query($con,$sql_log) or die("error" .mysqli_error());
	
	
	if($_SESSION["user_type_new"]!=4)
	{
	header("location:main.php");	
	}
	else{
	header("location:main.php");	
	}
	
}
else 
{
	header("location:index.php?mesg=1");
}
?>
