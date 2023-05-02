<?php
//include "session.php";
include "includes/connection.php";
include "includes/function.php";


$name = "";
$description = "";
$title = "";
$message='';
$course_id='';
$country_id='';
$active=0;


date_default_timezone_set("Asia/Calcutta");
//$admin_id=$_SESSION["admin_id"];
$actiontype = "Add";

      if(isset($_GET['id']))
		{
		$id=unserialize(base64_decode($_GET['id']));
		$id1=unserialize(base64_decode($_GET['id']));
		$actiontype = "Edit";
		
		$sql = "SELECT * FROM university WHERE id=".$id."";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetch();
		@extract($rows);
		
				
		}

if(isset($_POST['process']))
{
	if ($actiontype=="Add")
	{
		$actiontype = $_POST['process'];
		$name = addslashes($_POST['name']);
		$description = addslashes($_POST['description']);	
		$title = addslashes($_POST['title']);
		$course_id	 = $_POST['course_id'];
		$country_id	 = $_POST['country_id'];
		$active	 = $_POST['active'];
		
		
		$sql_id1 = mysqli_query($con,"SELECT MAX(id) FROM university");
				$rs_id1 = mysqli_fetch_array($sql_id1);
				if($rs_id1[0]==0)
				{
						$university_id =1;
				}
				else
				{
						$university_id = $rs_id1[0]+1;
				}

				if($_FILES['file1']['name']!="")
				{
				$control_name = "file1";
				$target = "../university_image";
				$new_name = "university".$university_id;
			    //$target_thumb = "../header_image/thumb";
				$path1 = upload_all_photo($control_name, $target, $new_name);
				}
				elseif($_POST['path1']!="")
				$path1 = $_POST['path1'];
				
				if($_FILES['file2']['name']!="")
				{
				$control_name = "file2";
				$target = "../university_pdf";
				$new_name2 = $name.$university_id;
			    //$target_thumb = "../header_image/thumb";
				$path2 = upload_all_photo2($control_name, $target, $new_name2);
				}
				elseif($_POST['path2']!="")
				$path2 = $_POST['path2'];
		
		
		$query_university_name = "SELECT * FROM university  where name = '".$name."'";
		$result_university_name = mysqli_query($con,$query_university_name);
		$rs_university_name = mysqli_fetch_array($result_university_name);
		
		if($rs_university_name == "")
		{
		 $sql="INSERT INTO university (country_id,course_id,name,image,description,title,active,pdf)value('$country_id','$course_id','$name','$path1','$description','$title','$active','$path2')";
		$result=mysqli_query($con,$sql);	
		header("location:manage-university.php");
		}
		else
			{
			$message = "<li><font color='#ff0000'>Product Name already exists.</font></li>";	
			}
	}
	else if ($actiontype=="Edit")
	{
		$name = addslashes($_POST['name']);
		$course_id = $_POST['course_id'];
		$country_id = $_POST['country_id'];
		$description = addslashes($_POST['description']);	
		$title = addslashes($_POST['title']);
		$active	 = $_POST['active'];	
		
		if($_FILES['file1']['name']!="")
				{
				$control_name = "file1";
				$target = "../university_image";
				$new_name = "university".$id;
			    //$target_thumb = "../header_image/thumb";
				$path1 = upload_all_photo($control_name, $target, $new_name);
				}
				elseif($_POST['path1']!="")
				$path1 = $_POST['path1'];
				
		if($_FILES['file2']['name']!="")
				{
				$control_name = "file2";
				$target = "../university_pdf";
				$new_name2 = $name.$id;
			    //$target_thumb = "../header_image/thumb";
				$path2 = upload_all_photo2($control_name, $target, $new_name2);
				}
				elseif($_POST['path2']!="")
				$path2 = $_POST['path2'];
		
		$query_university_name = "SELECT * FROM university  where name = '".$name."' and id!='".$id."'";
		
		$result_university_name = mysqli_query($con,$query_university_name);
		$rs_university_name = mysqli_fetch_array($result_university_name);
		
			if($rs_university_name == "")
			{
				$sql_update="update university set country_id='$country_id',course_id='$course_id',name='$name',image='$path1',description='$description',title='$title',active='$active',pdf='$path2' where id='$id'" ;		
				$result_update=mysqli_query($con,$sql_update);
				header("location:manage-university.php");
			}
			else
			{
				$message = "<li><font color='#ff0000'>Product Name already exists.</font></li>";	
			}
	}	
}
   
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $tag; ?> | Add University</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
	<link rel="stylesheet" href="plugins/select2/select2.min.css">
	<link href="bootstrap-fileinput-master/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
	    <link rel="stylesheet" href="responsivetable.css">
<!--<script src="ckeditor.js"></script>-->

	<script type="text/javascript" src="js/jquery.min.js"></script>

	<!--<script src="plugins/ckeditor_wiris/plugin.js"></script>
	<script src="ckeditor.js"></script>-->
	
	<style type="text/css">
	.cke_textarea_inline{
	border: 1px solid black;
	}
	</style>
	
	<!-- CKEditor -->	
	<script src="ckeditor/ckeditor.js" ></script>


	
	<title>Welcome</title>
<script>


function getXMLObject()
{
//Initialize a Xml Object
	var xmlHttp = null;
	try
	{    // Firefox, Opera 8.0+, Safari  
		xmlHttp=new XMLHttpRequest();    
	}
	catch (e)
	{    // Internet Explorer    
		try
		{      
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");      
		}
		catch (e)
		{      
			try
			{        
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");    
			}
			catch (e)
			{  
				alert("Your browser does not support AJAX!");    
				return false;        
			}
		}
	}
	return xmlHttp;
}


$(document).on('submit', '#form1', function()
{
		var name  = $('#name').val();
		var course_id  = $('#course_id').val();
		var country_id  = $('#country_id').val();
	
	 $(".error").hide();
     var hasError = false;

		if (name == '') {
		$("#name").css({
		"border": "2px solid #a94442"
		});
		hasError = true;
		}
		if (course_id == '') {
		$("#course_id").css({
		"border": "2px solid #a94442"
		});
		hasError = true;
		}
		if (country_id == '') {
		$("#country_id").css({
		"border": "2px solid #a94442"
		});
		hasError = true;
		}

	
			if(hasError == true)
			{
			
			return false;
			}
		  
});

function getfilename(filename)
	{
	//alert(filename);
	intpos = filename.lastIndexOf(".");
	browsefile_ext = filename.substring(intpos);
	//alert(browsefile_ext);
	if(browsefile_ext=='.jpg' || browsefile_ext=='.png' || browsefile_ext=='.JPG' || browsefile_ext=='.PNG' || browsefile_ext=='.gif' || browsefile_ext=='.JPEG' || browsefile_ext=='.jpeg' || browsefile_ext=='.webp' || browsefile_ext=='.WEBP')
	document.getElementById("allowed_file").value = '1';
	else
	{
	alert('Only jpg, png, gif, jpeg, webp files are allowed');
	document.getElementById("file1").value = '';
	}	
}	

function getfilename2(filename)
	{
	//alert(filename);
	intpos = filename.lastIndexOf(".");
	browsefile_ext = filename.substring(intpos);
	//alert(browsefile_ext);
	if(browsefile_ext=='.pdf' || browsefile_ext=='.PDF')
	document.getElementById("allowed_file").value = '1';
	else
	{
	alert('Only pdf files are allowed');
	document.getElementById("file2").value = '';
	}
	
	
}	

</script>
	
	<style>
	
		textarea
		{
			height:75%;
		}
label1 {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 8px;
    font-weight: 600;
    width: 59px;
}		
	</style>
	
	</head>
  <body class="hold-transition skin-blue sidebar-mini" >
    <!-- Site wrapper -->
    <div class="wrapper">
     <?php include("includes/header.php") ?>
      <?php include("includes/left.php") ?>

      <div class="content-wrapper">
        <section class="content-header">
          <h1>&nbsp;</h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          </ol>
        </section>
<style>
textarea.form-control {
    width:100%;
	height:150px;
}
</style>     
        <section class="content">
          <div class="box">
            <div class="box-body">
			<div class = "col-md-12">
		   <div class="col-md-10">
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><?php  if(isset($_GET['id'])){?> Edit  University <?php }  else {?> Add University <?php } ?></h3>
				<p> <?php echo $message?></p>
                </div>
                <!-- form start -->
      <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1" >
            <div class="box-body" id="TextBoxesGroup">
			
					<div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">Course Name &nbsp;<span style="color:red">*</span></label>
						<?php
						$sql_course = "SELECT * FROM course order by name asc";	
						$result_course = mysqli_query($con,$sql_course);	
						$rs_course = mysqli_fetch_array($result_course);
						?>
                      <select name="course_id" id="course_id" class="form-control" onChange="change_country()">
					  <option value="">--Select--</option>
					  <?php 
					  do{
					  ?>
					   <option value="<?php echo $rs_course['id'];?>" <?php  if($course_id == $rs_course['id']){?> selected<?php }?>><?php echo $rs_course['name'];?></option>
					   <?php 
					   }
					   while($rs_course = mysqli_fetch_array($result_course));
					   ?>
					  </select>
						<input name="process" type="hidden" id="process" value="<?php echo $actiontype;?>" />
                    </div>
					
					<div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">Country Name &nbsp;<span style="color:red">*</span></label>
						<?php /*?><?php
						if($actiontype == "Edit")
						{
						?>
<?php
						$sql_country = "select * from country WHERE course_id = '".$course_id."' order by name";	
						$result_country = mysqli_query($con,$sql_country);	
						$rs_country = mysqli_fetch_array($result_country);
						?>
                      <select name="country_id" id="country_id" class="form-control" onChange="change_country()">
					  <option value="">--Select--</option>
					  <?php 
					  do{
					  ?>
					   <option value="<?php echo $rs_country['id'];?>" <?php  if($country_id == $rs_country['id']){?> selected<?php }?>><?php echo $rs_country['name'];?></option>
					   <?php 
					   }
					   while($rs_country = mysqli_fetch_array($result_country));
					   ?>
					   <?php
						}
						else
						{
						?><?php */?>
						<span id="div_country_id">
					  <select name="country_id" id="country_id" class="form-control">
					  <option value="">--Select--</option>
					  <?php
					  if($actiontype == "Edit")
						{
						?>
<?php
						$sql_country = "select * from country WHERE course_id = '".$course_id."' order by name";	
						$result_country = mysqli_query($con,$sql_country);	
						$rs_country = mysqli_fetch_array($result_country);
						?>
					  
					  <?php 
					  do{
					  ?>
					   <option value="<?php echo $rs_country['id'];?>" <?php  if($country_id == $rs_country['id']){?> selected<?php }?>><?php echo $rs_country['name'];?></option>
					   <?php 
					   }
					   while($rs_country = mysqli_fetch_array($result_country));
					   ?>
					   <?php
						}
						
						?>
					  </select></span>
                     
                    </div>
					
                    <div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">University Name &nbsp;<span style="color:red">*</span></label>
                    	<input type="text" name="name" id="name" class="form-control"  value="<?php echo $name; ?>">
						<input name="process" type="hidden" id="process" value="<?php echo $actiontype;?>" />
                    </div>
					
					<div class="form-group" style="margin-top:5px">
                      <label for="exampleInputDIPPING" style="margin-top:10px;">Is popular &nbsp; </label>
					  <span style="float:right; width:60%;">
                      <input type="radio" name="active" id="active" <?php if($active == '0'){?>checked = "	checked"<?php }?> value="0"> Yes &nbsp;&nbsp;
					  <input type="radio" name="active" id="active" <?php if($active == '1'){?> checked = "checked" <?php }?>value="1"> No
					  </span>
              </div>
					 
                 
			  	<?php if($actiontype == "Edit")
					{
					
						$sql_image = "SELECT * FROM university WHERE id = '$id' ";	
						$result_image = mysqli_query($con,$sql_image);	
						$rs_image = mysqli_fetch_array($result_image);
		
						if($rs_image['image']!="")
						{
					?>
					    <div class="form-group" style="margin: 5px 0 0px 0;">
						<label for="exampleInputARTICLE" style="margin-top:10px; height:70px">Current Image &nbsp;</label>
						<span style="float:right; width:60%;">
					       <img src="../<?php echo $rs_image['image'];?>" border="0" style="width: 100px;height: 80px;">
						   </span>
						  </div>
						
					      <?php 
						  }
					}
					 ?>
					
					<div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">Image</label>
                    	<input name="file1" type="file" id="file1" class="form-control" onChange="return getfilename(this.value);">
					</div>
					
					
					<div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">Fee structure</label>
                    	<input name="file2" type="file" id="file2" class="form-control" onChange="return getfilename2(this.value);">
						
					</div>
					
					
				<div class="form-group"style="height: 120px;" >
                      <label for="exampleInputDIPPING" style="margin-top:10px;">Content &nbsp; </label>
                      <textarea name="title" id="title" cols="40" rows="5" class="form-control" style="width: 60%;height: 100px;"><?php echo $title;?></textarea>  
             	 </div>	
					
				<div class="form-group">
				<label for="exampleInputDIPPING" style="margin-top:10px; width:900px">Description 
				<textarea id='description' name='description' rows="10" cols="80"><?php echo $description;?></textarea>
				<!--<textarea name="description" id="description"  class="form-control">
				
				</textarea>
				<script>
				CKEDITOR.replace( 'description');
				</script>-->
				
				</label>
				</div>

			  
			  
                    </div>
						
</div>

	
	
<div class="box-footer">

<input name="process" type="hidden" id="process" value="<?php echo $actiontype;?>" />
  <input name="Submit" type="submit" value="Submit" class="btn btn-primary" />
	<?php
	if($actiontype == "Edit")
	{
	?>
  <input type="hidden" name="path1" id="path1" value="<?php echo $image;?>">
  <input type="hidden" name="path2" id="path2" value="<?php echo $pdf;?>">
  <?php
	}
	?>
&nbsp;&nbsp;
<input name="reset" type="reset" value="Reset" class="btn btn-primary" /> 

</div>
</form>
</div>
</div>
</div>
</div>
</div>
</section>

</div>
<?php include("includes/footer.php") ?>


</div>


	<script type="text/javascript">
				$(function () {
        // Replace the <textarea id="editor1"> with a CKEditor
        // instance, using default configuration.
        /*CKEDITOR.replace('editor1');
		//config.allowedContent = true;
		editor1.pasteFilter.disabled = true;
        //bootstrap WYSIHTML5 - text editor
        $(".textarea").wysihtml5();*/
		
	   CKEDITOR.replace("description"), {
       //allowedContent : true
		}
		
      });
				
				
				</script>

    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
	 <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
	  <script>
      $(function () {
        $("#example1").DataTable({
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ]},
			{ "bSortable": true, "aTargets": [ 1 ] },
			{ "bSortable": true, "aTargets": [ 2 ] },
			{ "bSortable": false, "aTargets": [ 3 ]}
			],
       });
      });
	  
function change_country()
	{
	var course_id = document.getElementById("course_id").value;
	var xmlHttp = getXMLObject();
	xmlHttp.onreadystatechange=function()
		{
		if(xmlHttp.readyState==4)
			{
			document.getElementById("div_country_id").innerHTML = xmlHttp.responseText;
			}
		}
	xmlHttp.open("POST","add_remove_product_own.php?course_id=" + course_id + "&form_no=15",true);
	xmlHttp.send(null); 
}	  
    </script>

   
  </body>
</html>