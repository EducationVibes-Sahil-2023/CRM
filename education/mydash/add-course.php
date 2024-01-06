<?php
//include "session.php";
include "includes/connection.php";
include "includes/function.php";


$name = "";
$description = "";
$title = "";
$meta_description = "";	
$meta_keyword = "";
$message='';

	//$arr_to_replace=array(' ','/','&');
	//$arr_replace_by=array('-','and','and');
	
	$arr_to_replace=array(',',' ','/','&');
	$arr_replace_by=array('','-','and','and');

date_default_timezone_set("Asia/Calcutta");
//$admin_id=$_SESSION["admin_id"];
$actiontype = "Add";

      if(isset($_GET['id']))
		{
		$id=unserialize(base64_decode($_GET['id']));
		$id1=unserialize(base64_decode($_GET['id']));
		$actiontype = "Edit";
		
		$sql = "SELECT * FROM course WHERE id=".$id."";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetch();
		@extract($rows);
		
		  $page_url=str_replace($arr_to_replace, $arr_replace_by,strtolower($name));
		
		//echo $image_path;
				
		}

if(isset($_POST['process']))
{
	if ($actiontype=="Add")
	{
		$actiontype = $_POST['process'];
		$name = $_POST['name'];
				
		$sql_id1 = mysqli_query($con,"SELECT MAX(id) FROM course");
		$rs_id1 = mysqli_fetch_array($sql_id1);
		
		
		$query_course_name = "SELECT * FROM course  where name = '".$name."'";
		$result_course_name = mysqli_query($con,$query_course_name);
		$rs_course_name = mysqli_fetch_array($result_course_name);
		
		if($rs_course_name == "")
		{
		 $sql="INSERT INTO course (name)value('$name')";
		$result=mysqli_query($con,$sql);	
		header("location:manage-course.php");
		}
		else
			{
			$message = "<li><font color='#ff0000'>Course Name already exists.</font></li>";	
			}
	}
	else if ($actiontype=="Edit")
	{
		$name = $_POST['name'];
		
		$query_course_name = "SELECT * FROM course  where name = '".$name."' and id!='".$id."'";
		$result_course_name = mysqli_query($con,$query_course_name);
		$rs_course_name = mysqli_fetch_array($result_course_name);
		
			if($rs_course_name == "")
			{
				$sql_update="update course set name='$name' where id='$id'" ;		
				$result_update=mysqli_query($con,$sql_update);
				header("location:manage-course.php");
			}
			else
			{
				$message = "<li><font color='#ff0000'>Course Name already exists.</font></li>";	
			}
	}	
}
   
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $tag; ?> | Add Course</title>
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

	<script src="plugins/ckeditor_wiris/plugin.js"></script>
	<script src="ckeditor.js"></script>
	
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
	
	 $(".error").hide();
     var hasError = false;

	 if (name == '') {
					$("#name").css({
						"border": "2px solid #a94442"
					});
				hasError = true;
			}
	
			if(hasError == true)
			{
			
			return false;
			}
		  
});

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
                  <h3 class="box-title"><?php  if(isset($_GET['id'])){?> Edit  Course <?php }  else {?> Add Course <?php } ?></h3>
				<p> <?php echo $message?></p>
                </div>
                <!-- form start -->
      <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1" >
            <div class="box-body" id="TextBoxesGroup">
					
                    <div class="form-group">
						<label for="exampleInputARTICLE" style="margin-top:10px;">Course Name &nbsp;<span style="color:red">*</span></label>
                    	<input type="text" name="name" id="name" class="form-control"  value="<?php echo $name; ?>">
						<input name="process" type="hidden" id="process" value="<?php echo $actiontype;?>" />
                    </div>
					
                    </div>
						
</div>

	
	
<div class="box-footer">

<input name="process" type="hidden" id="process" value="<?php echo $actiontype;?>" />
  <input name="Submit" type="submit" value="Submit" class="btn btn-primary" />
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
    </script>

   
  </body>
</html>