<?php 
//include "session.php";
include "includes/connection.php";

		$backpage = explode('?',$_SERVER['HTTP_REFERER']);
		$backpage = $backpage[0];                          // remove query string

		$form_no = $_GET['form_no'];
		$id = unserialize(base64_decode($_REQUEST['id']));
		//$type = unserialize(base64_decode($_REQUEST['type']));
		$sid = unserialize(base64_decode($_REQUEST['sid']));

		
		
		function ChangeStatus($id, $tableName)
		{
		global $con;
		     $sql = "select * from ". $tableName . "   where id  = '".$id."'";
			 
			 
		     $result=mysqli_query($con,$sql);
		     $rs=mysqli_fetch_array($result);
	
			if($rs['active']=="0") 
			 {
				  $sqlup = "update ". $tableName . " set active = '1' where id = '".$id."'";
				  $resultup=mysqli_query($con,$sqlup);
			 }
			else if($rs['active']=="1")
			{  
				$sqlup = "update ". $tableName . " set active = '0' where id = '".$id."'";
				$resultup=mysqli_query($con,$sqlup);
			}
		}
		
		
		switch($form_no)
		{
		 case 1:
		 ChangeStatus($id,"university");  // arguments: auto id, field name and then table name 
		 break;
		 
		}

		
//$type = "?type=".base64_encode(serialize($type))."&sid=".base64_encode(serialize($sid))."&id=".base64_encode(serialize($id))."&customer_id=".$customer_id;
$type = "?id=".base64_encode(serialize($id));
header("location:$backpage".$type);
?>