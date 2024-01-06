<?php
 include "db.php";
 if(isset($_POST['insert']))
		{
		
			$iemi_no=$_POST['iemi_no'];
			$sales_date= date('Y-m-d',strtotime($_POST['sales_date']));
			$counter_id1=$_POST['counter_id1'];
			$created_by = $_SESSION['admin_id'];
			$created_date = date('Y-m-d');
			$created_ip = $_SERVER['REMOTE_ADDR'];
		
		$q=mysqli_query($con,"INSERT INTO sale_entry (iemi_no,counter_id,sales_date,created_by,created_date,created_ip) VALUES ('$iemi_no','$counter_id1','$sales_date','$created_by','$created_date','$created_ip')");
		if($q)
		echo "success";
		else
		echo "error";
		}
 ?>