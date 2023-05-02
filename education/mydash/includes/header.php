<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
<?php 
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
?>
<header class="main-header">
        <!-- Logo -->
        <a href="#" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <!--<span class="logo-mini"><b>E</b>RP</span>-->
		  <span class="logo-mini"><img src="images/logo1.png" /></span>
          <!-- logo for regular state and mobile devices -->
          <!--<span class="logo-lg"><b>Sierra</b> ERP</span>-->
		  <span class="logo-lg"><img src="images/logo.png" /></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
		
          <!-- Sidebar toggle button-->
         <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
		  <div style="margin: 10px 0px -41px 80px; font-size: 20px; color:#fff;"><?php echo $tag; ?></div>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- Messages: style can be found in dropdown.less-->
              <!-- Notifications: style can be found in dropdown.less -->
                            <!-- Tasks: style can be found in dropdown.less -->
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
              
                 <!-- <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image">-->
                  <span class="hidden-xs" style="
    font-size: 16px;
    color: #fff;margin-right:10px;"><!--<?php  echo $_SESSION['admin_uid']?> |--><a href="logout.php" style="
    font-size: 16px;
    color: #fff;"> Logout </a></span>
            
                <ul class="dropdown-menu">
                  <!-- User image -->
                <!--  <li class="user-header">
                    <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                    <p>
                      Alexander Pierce - Web Developer
                      <small>Member since Nov. 2012</small>
                    </p>
                  </li>-->
                  <!-- Menu Body -->
                <!--  <li class="user-body">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </li>-->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="#" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
              <!--<li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>-->
            </ul>
          </div>
        </nav>
      </header>