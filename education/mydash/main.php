<?php
//include "session.php";
include "includes/connection.php";

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $tag; ?></title>
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

	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	
	<title>Welcome</title>

	
	<style>
	
		textarea
		{
			height:75%;
		}
	</style>
	
	</head>
  <body class="hold-transition skin-blue sidebar-mini" >
  
    <!-- Site wrapper -->
    <div class="wrapper">

     <?php include("includes/header.php") ?>

      <!-- =============================================== -->

     
      <?php include("includes/left.php") ?>
      <!-- =============================================== -->

      
      <div class="content-wrapper">
        
        <section class="content-header">
          <h1>&nbsp; </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          </ol>
        </section>

        <!-- Main content -->
					<section class="content">
			
					  <!-- Default box -->
									  <div class="box">
										
											<div class="box-body" style="height:480px;">
													<div class = "col-md-12">
															   <div class="col-md-6">
																  <!-- general form elements -->
																		   <div class="box box-primary" align="center" style="font-size:20px;">
																			  <br><br><br><br><br>
																			   Welcome To CRM Educationvibes
																			   <br><br><br><br><br><br><br>
																		  </div>
															</div>
												</div>
										</div>
								 </div>
					</section>
    
  </div>
</div>
<?php include("includes/footer.php") ?>

<div class="control-sidebar-bg"></div>

  
    <script src="dist/js/app.min.js"></script>
  </body>
</html>