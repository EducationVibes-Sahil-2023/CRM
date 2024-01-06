<?php
//include "session.php";
include "includes/connection.php";

		$paging_query = "SELECT * FROM course order by name";
		$paging_limit = 25;
		
		include_once "paging.php";
		
		$sql = $paging_query." LIMIT $set_limit, $limit";
		$result = mysqli_query($con,$sql);
		$rs=mysqli_fetch_array($result);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $tag; ?> | Manage Course</title>
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
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
	<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<SCRIPT LANGUAGE="JavaScript">
	
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

	
function showpopup(query)
{
	var i
		i=confirm('Course will be deleted. Please confirm.');
          
		  //alert(query);
		if(i)
		{
			//submitForm(query);
          document.form1.action="delete_records.php?"+query+"&form_no=2";
          document.form1.method="post";
	        
			document.form1.submit();
			return false
			//alert(query);
		}
		else
		{
			return false;
		}

}

</script>
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">

      <?php include("includes/header.php") ?>

      <!-- =============================================== -->

      <!-- Left side column. contains the sidebar -->
      <?php include("includes/left.php") ?>

      <!-- =============================================== -->

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>&nbsp;
           
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Location</a></li>
            <li class="active">Manage Course</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Default box -->
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">List of Course</h3>
			  <div style="text-align:right"><a href="add-course.php" class="btn btn-default btn-flat" title="Add New Course">Add New Course</a></div>
            </div>
			<form action="" method="post" name="form1" id="form1">
            <div class="box-body">
			
			<?php 
			if(isset($_REQUEST['mesg']) && $_REQUEST['mesg'] ==4)
			{
			?>
			<p>Course has been deleted successfully.</p>
			<?php
			}
			else{
				if(isset($_REQUEST['mesg'])&& $_REQUEST['mesg']==1)
				{
				?>
				<p>Course has been added successfully.</p>
				<?php
				}
				elseif(isset($_REQUEST['mesg'])&& $_REQUEST['mesg']==2)
				{
				?>
				<p>Course  has been updated  successfully.</p>
				<?php	
				}
			
			}
			
			?>
              <table  class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th width="8%">SNo.</th>
                        <th width="21%">Course Name</th>
                        <th width="15%">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                     <?php 
					 $i=(($page * 25 )-25)+1;
					if($rs!='')
					{
						do
						{
					$sql2 = "SELECT * FROM country where course_id = '".$rs['id']."'";
					$result2 = mysqli_query($con,$sql2);
					$rs2=mysqli_fetch_array($result2);
						
						?>
						<tr>
						<td><?php echo $i ?></td>
						<td><?php echo $rs['name']; ?></td>
						<td><a href="add-course.php?id=<?php echo base64_encode(serialize ($rs['id']));?>" title="edit"><img src="dist/img/edit.png" alt="edit"></a>
						
						<?php if($rs2 == ""){?>
						| <a href="javascript:void(0)" onClick="return showpopup('id=<?php echo $rs['id']?>')" class="delete"><img src="dist/img/delete.png" alt="delete"></a>
						<?php }?>
						
						</td>
						</tr>
						<?php 
						$i++;
						}
						while($rs = mysqli_fetch_array($result));
					}
					 
					 ?> 
        			</tbody>
               
                  </table>
            </div><!-- /.box-body -->
			  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                      <td width="50" height="19" align="center" bgcolor="#E4E4E4"><table width="86%" border="0" cellpadding="0" cellspacing="0">
                          <tr align="center">
                            <td width="50%"><?php echo @$first_page_arrow; ?></td>
                            <td width="50%"><?php echo @$prev_page_arrow; ?></td>
                          </tr>
                      </table></td>
                      <td width="588" align="center" bgcolor="#E4E4E4">&nbsp; <?php echo @$page_no; ?></td>
                      <td width="62" align="center" bgcolor="#E4E4E4"><table width="78%" border="0" cellpadding="0" cellspacing="0" bordercolor="#E4E4E4">
                          <tr align="center">
                            <td width="50%" bgcolor="#E4E4E4"><?php echo @$next_page_arrow; ?></td>
                            <td width="50%" bgcolor="#E4E4E4"><?php echo @$last_page_arrow; ?></td>
                          </tr>
                      </table></td>
                    </tr>
                  </table>
            </form>
          </div><!-- /.box -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <?php include("includes/footer.php") ?>

      <!-- Control Sidebar -->
      <!-- /.control-sidebar -->
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
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