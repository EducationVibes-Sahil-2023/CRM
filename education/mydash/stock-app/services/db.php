<?php
header('access-control-allow-origin *');
header('Access-Control-Allow-Methods: POST, GET, PATCH, PUT, DELETE, OPTIONS');
$con = mysqli_connect("localhost","root","","stock_management") or die ("could not connect database");
//$con = mysql_connect("localhost","root")or die("i can not connect" .mysql_error());
//mysql_select_db("directory_registration"); 


?>