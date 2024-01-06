<?php
header('access-control-allow-origin *');
header('Access-Control-Allow-Methods: POST, GET, PATCH, PUT, DELETE, OPTIONS');
$con = mysqli_connect("localhost","webcityi_fetalus","Pwd@#Feta%0987","webcityi_fetal") or die ("could not connect database");
?>