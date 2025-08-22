<?php 
include("includes/connection.php");
include("includes/function.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $tag; ?> | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
<!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
-->    <!-- Ionicons -->
<!--    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
-->    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/square/blue.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div class="login-logo">
        <a href="index2.html"><b>Control</b>Panel</a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
		<?php 
		if(isset($_REQUEST['mesg']) && $_REQUEST['mesg']==1)
		{
		?>
		<p class="login-box-msg" style="color:red;">Wrong username and password.</p>
		<?php 
		}
		?>
        <form action="login.php" method="post" id="loginform" name="loginform">
          <div class="form-group has-feedback">
            <input type="text" name="fld_username" id="fld_username"  class="form-control" style="width:100%;  margin-top:10px" placeholder="Username">
            
          </div>
          <div class="form-group has-feedback">
            <input type="password" name="fld_password" id="fld_password" class="form-control" style="width:100%; margin-top:10px;  margin-bottom:10px;" placeholder="Password">
            
          </div>
          <div class="row">
            <div class="col-xs-8">
              <!--<div class="checkbox icheck" style="margin-left: 20px;">
                <label>
                  <input type="checkbox"> Remember Me
                </label>
              </div>-->
            </div><!-- /.col -->
            <div class="col-xs-4">
              <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
            </div><!-- /.col -->
          </div>
        </form>
       <a href="forgot-password.php">I forgot my password</a><br>
      </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

    
	  
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>	
<script type="text/javascript" src="js/jquery.validate.min.js"></script>  
	  <script type="text/javascript">
	  (function($,W,D)
{
    var JQUERY4U = {};

    JQUERY4U.UTIL =
    {
        setupFormValidation: function()
        {
            //form validation rules
            $("#loginform").validate({
                rules: {
                    
					fld_username: "required",
					fld_password: "required",
                    agree: "required"
                },
                messages: {
                    fld_username: "<font color='red'>This is required.</font>",
                    fld_password: "<font color='red'>This is required.</font>",
					agree: "Please accept our policy"
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        }
    }

    //when the dom has loaded setup form validation rules
    $(D).ready(function($) {
        JQUERY4U.UTIL.setupFormValidation();
    });

})(jQuery, window, document); 
</script>
<!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
	

    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
	  </script>
  </body>
</html>
