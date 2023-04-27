<?php
///include("file:///E|/sites/neasdenhardware/admin/session_start.php");
//***********Paging Start**************
$slno=1;

$first_page_arrow="";
$prev_page_arrow="";
$page_no="";
$next_page_arrow="";
$last_page_arrow="";

// If any condition required
	
	$t = mysqli_query($con,$paging_query);	
	if(!$t) die(mysqli_error());
	
	$a= mysqli_fetch_object($t);
	$total_items= mysqli_num_rows($t);


//echo "Total".$type;
	if(isset($_REQUEST['limit'])!="")
	{
		$limit = $_REQUEST['limit'];
	}
	else
	{
		$limit = 25;
	}
	if(isset($_REQUEST['page'])=="")
	{
		$page=0;
	}
	else
	{
		$page=$_REQUEST["page"];
	}
	

//set default if: $limit is empty, non numerical, less than 10, greater than 50
	
	if((!$limit)  || (is_numeric($limit) == false))
	 {
		 $limit = 25; //default
	}
//set default if: $page is empty, non numerical, less than zero, greater than total available
	if((!$page) || (is_numeric($page) == false) || ($page < 0) || ($page > $total_items)) 
	{
		  $page = 1; //default
	}
	
	if ($page!=1)
	{
		$slno = (($page - 1) * $paging_limit) + 1;
	}
	
	//calcuate total pages
	$total_pages     = ceil($total_items / $limit);
	$set_limit       = $page * $limit - ($limit);
	
	//Results per page: **EDIT LINK PATH**									

	$prev_page = $page - 1;
	
	if($prev_page >= 1) 
	{
	  $prev_page_arrow= "<font size='2' face='Arial, Helvetica, sans-serif' class='pageactive'>"."<a href='?limit=$limit&page=$prev_page".$paging_condition."' class='pageactive'><</a>"."</font>";
	  
	  $first_page_arrow= "<font size='2' face='Arial, Helvetica, sans-serif' class='pageactive'>".("<a href='?limit=$limit&page=1".$paging_condition."' class='pageactive'><<</a>"."</font>");
	}
	
	//Display middle pages: **EDIT LINK PATH**
	
	if ($total_pages <= 30)
		{	
			for($a = 1; $a <= $total_pages; $a++)
			{
			   if($a == $page) 
			   {
				  $page_no.="<font size='2' face='Arial, Helvetica, sans-serif' class='pageinactive'>&nbsp;<b>$a</b>&nbsp;</font>"; //no link
			   } 
			   else 
			   {
				  $page_no.="<font size='2' face='Arial, Helvetica, sans-serif'>&nbsp;<a href='?limit=$limit&page=$a".$paging_condition."' class='pageactive'>$a</a>&nbsp;</font>";
			   }
			}
		}
		
		else
		{	
			$tot_pages=29;
			$tot_pages=$tot_pages+$page;
			for($a = $page; $a <= $tot_pages; $a++)
			{
			   if($a == $page) 
			   {
				 @$page_no.="<font size='2' face='Arial, Helvetica, sans-serif' class='pageinactive'>&nbsp;<b>$a</b>&nbsp;</font>"; //no link
			   } 
			   else 
			   {
				  $page_no.="<font size='2' face='Arial, Helvetica, sans-serif'>&nbsp;<a href='?limit=$limit&page=$a".$paging_condition."' class='pageactive'>$a</a>&nbsp;</font>";
			   }
			}
		}
		
		if ($total_pages > 30 && $total_pages-$page <= 29)
		{	
			$page_no="";
			$start_page=$total_pages-29;
			
			for($a = $start_page; $a <= $total_pages; $a++)
			{
			   if($a == $page) 
			   {
				  $page_no.="<font size='2' face='Arial, Helvetica, sans-serif' class='pageinactive'>&nbsp;<b>$a</b>&nbsp;</font>"; //no link
			   } 
			   else 
			   {
				  $page_no.="<font size='2' face='Arial, Helvetica, sans-serif'>&nbsp;<a href='?limit=$limit&page=$a".$paging_condition."' class='pageactive'>$a</a>&nbsp;</font>";
			   }
			}
		}
		
		//next page: **EDIT THIS LINK PATH**
			
			$next_page = $page + 1;
			if($next_page <= $total_pages) 
			{
			   $next_page_arrow= "<font size='2' face='Arial, Helvetica, sans-serif' class='pageactive'>"."<a href='?limit=$limit&page=$next_page".$paging_condition."' class='pageactive'>></a>"."</font>";
			   
			   $last_page_arrow= "<font size='2' face='Arial, Helvetica, sans-serif' class='pageactive'>"."<a href='?limit=$limit&page=$total_pages".$paging_condition."' class='pageactive'>>></a>"."</font>";
			}
				
//***********Paging End**************
?>
