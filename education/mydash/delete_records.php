<?php
include "session.php";
include "includes/connection.php";
include "includes/function.php";

$backpage=$_SERVER['HTTP_REFERER'];

$form_no = $_GET['form_no'];

$type="delete";

// echo $form_no;
 
switch ($form_no)
 {
			case 1 :
		    $id = 	$_GET['id'];
			$sql_main_image = "select image from university where id = '".$id."'";
			$result_main_image = mysqli_query($con,$sql_main_image);
			
			while($rs_main_image = mysqli_fetch_array($result_main_image))
			{
			$path ="../".$rs_main_image['image'];
			if(file_exists($path))
			{
				unlink($path);
			}
			}
			$sql_delete1="delete from university where id='".$id."'";
			$result_delete1=mysqli_query($con,$sql_delete1);
			break;

			case 2 :
		    $id = 	$_GET['id'];
			$sql_delete1="delete from course where id='".$id."'";
			$result_delete1=mysqli_query($con,$sql_delete1);
			
			break;
			
			case 3 :
		    $id = 	$_GET['id'];
			$sql_main_image = "select image from country where id = '".$id."'";
			$result_main_image = mysqli_query($con,$sql_main_image);
			
			while($rs_main_image = mysqli_fetch_array($result_main_image))
			{
			$path ="../".$rs_main_image['image'];
			if(file_exists($path))
			{
				unlink($path);
			}
			}
			$sql_delete1="delete from country where id='".$id."'";
			$result_delete1=mysqli_query($con,$sql_delete1);
			break;
										
			

	}
	
	//$backpage = str_replace('?not_deleted=0','',$backpage);
	//$backpage = str_replace('?not_deleted=1','',$backpage);
	
	header("location:$backpage");
?>