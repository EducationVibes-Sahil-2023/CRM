<?php
//session_start();
//include "session.php";

include "includes/connection.php";
//include "includes/function.php";

$form_no = $_GET['form_no'];

function select_country()
{

$id =$_GET['course_id'];

global $con;
 
$sql_add_cat = "select * from country WHERE course_id = '".$id."' order by name";    
$result_add_cat = mysqli_query($con,$sql_add_cat);
$rs_add_cat    = mysqli_fetch_array($result_add_cat);

  $display = "<select name='country_id' id='country_id' class='form-control'>
	<option value=''>--Select--</option>";
	if($rs_add_cat!="")
	{
		do
		{
			 $id = $rs_add_cat['id'];
			 $name = $rs_add_cat['name'];
			//echo $executive_name;
			$display .= "<option value=\"$id\"> $name</option>";

		}while($rs_add_cat    = mysqli_fetch_array($result_add_cat));
	}	

	$display .= "</select>";

	return $display;
	
}

			switch($form_no)
			{  
				case 20:
			    $shoppinglist = display_sub_category();
			    break;
				case 21:
			    $shoppinglist = select_ionic_category();
			    break;
				
				case 15:
			    $shoppinglist = select_country();
			    break;
				
				
			}

	     
	   echo $shoppinglist;
	 
?>