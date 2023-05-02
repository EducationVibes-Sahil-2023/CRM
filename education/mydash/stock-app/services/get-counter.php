<?php
session_start();
include "db.php";
	
	$data=array();
	$sql=mysqli_query($con,"SELECT id,counter from counter");
	for ($set = array (); 
	$row = $sql->fetch_assoc(); 
	$set[array_shift($row)] = $row);
	
	$data['result'] = $set;
	echo json_encode($data);
	
	

 
		


 ?>