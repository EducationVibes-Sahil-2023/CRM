<aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
         <!-- <div class="user-panel">
          <!--  <div class="pull-left image">
           <!--   <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p>Super Admin</p>
              <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
          </div>-->
          <!-- search form -->
         
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
          <!--<li class="header" style="color:#33CCFF;font-size:20px">MAIN Menu</li>-->
            <?php
			//echo $_SESSION["user_type_new"];
			//if($_SESSION["user_type_new"]==1)
			//{
			 $sql_form_category = "SELECT * FROM form_category order by serial_no";
			//}
			//elseif($_SESSION["user_type_new"]==2)
			//{
			// $sql_form_category = "SELECT * FROM form_category where menu_type_so=1 order by serial_no";
			//}
			//elseif($_SESSION["user_type_new"]==3)
			//{
			// $sql_form_category = "SELECT * FROM form_category where menu_type_se=1 order by serial_no";
			//}


//echo $sql_form_category;

$result_form_category = mysqli_query($con,$sql_form_category);	
	   while($rs_form_category = mysqli_fetch_array($result_form_category))
	  {
	   // if($_SESSION["user_type_new"]==1)
		//{
	 	$sql_forms = "SELECT * FROM forms WHERE category_id=".$rs_form_category['category_id']." order by form_id";
		//}
		//elseif($_SESSION["user_type_new"]==2)
		//{
		//$sql_forms = "SELECT * FROM forms WHERE category_id=".$rs_form_category['category_id']." AND form_type_so=1 order by form_id";
		//}
		//elseif($_SESSION["user_type_new"]==3)
		//{
		//$sql_forms = "SELECT * FROM forms WHERE category_id=".$rs_form_category['category_id']." AND form_type_se=1  order by form_id";
		//}
	 	$result_forms = mysqli_query($con,$sql_forms);
	    ?>
            <li class="treeview">
              <a href="#">
                <i class="fa fa-files-o"></i>
                <span><?php echo $rs_form_category['category_name'];?></span>
                
              </a>
			  
              <ul class="treeview-menu">
			  <?php
	   while($rs_forms = mysqli_fetch_array($result_forms))
	  {
	    ?>
                <li><a href="<?php echo $rs_forms['url']?>"><i class="fa fa-circle-o"></i> <?php echo $rs_forms['form_name']; ?></a></li>
               
              
	<?php 
	  }
	?>		  
       </ul>
	   </li>
            
	  <?php } ?>		</ul>
        </section>
        <!-- /.sidebar -->
      </aside>