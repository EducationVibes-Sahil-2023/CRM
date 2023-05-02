<?php
date_default_timezone_set("Asia/Kolkata");
function InsertData($field)
{
global $db;
$date = date('Y-m-d H:i:s');
 //print_r($field);
$query = "INSERT INTO registration SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" date = '".$date."'";

$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}

// Check existing email

class SimpleImage {
   
   var $image;
   var $image_type;
 
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename);
		 //imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }  
	  
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }

   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }      
}


function checkemailexist($fld_email)
{
global $db;
$query = "SELECT email1 FROM registration WHERE email1 = '".$fld_email."'";
$result = mysqli_query($con,$query)or die('Not execute this query');
$numrows = mysqli_num_rows($result);
return($numrows);
}


function upload_multiple_photo($file, $target, $name, $width_final, $height_final,$i)
{
  $size = $_FILES[$file]['size'][$i];
  $picture = $_FILES[$file]['name'][$i];
  $serverpath = $target;
  $path = "";

	$allowedfiles[] = "GIF";
	$allowedfiles[] = "gif";
	$allowedfiles[] = "JPG";
	$allowedfiles[] = "jpg";
	$allowedfiles[] = "pdf";
	$allowedfiles[] = "PDF";
	$allowedfiles[] = "png";
	$allowedfiles[] = "PNG";

$done="No";		  


foreach($allowedfiles as $allowedfile) 
{					
		
if (substr($picture, -3) == $allowedfile) 
{
	$done  = "yes"; 
}
//echo $done;
}		

if($size<=5000000000) // Less than or equal to 5MB
{
  if($done=="yes")
  {
	$image = new SimpleImage();
	$image->load($_FILES[$file]['tmp_name'][$i]);
	$width = $image->getWidth();
	$height = $image->getheight();
	
	
	    if($width>=$width_final && $height>=$height_final)
		{
		if($width>=$height)
		{
		$perc = round(($width_final*100)/$width);
		}
		else
		{
		$perc = round(($height_final*100)/$height);
		}
		}
		else { $perc = 100; }
	
	$w = ($image->getWidth())*$perc/100;
	$h = ($image->getheight())*$perc/100;
	
	$new_width = round($w);    // reduce 25% of Width
	$new_height = round($h);  // reduce 25% of Height
	
	$image->resize($new_width,$new_height);
	
	    $newname=$name;
		$pos = strpos($picture,".",0);
		$ext = trim(substr($picture,$pos+1,strlen($picture))," ");	
		$newfile = $newname . "." . $ext;
		
		if (strstr($serverpath,'../')!='')
		{
			$mypath = trim(substr($serverpath,3,strlen($serverpath))," ");
		}
		else
		{
			$mypath = $serverpath;
		}
		
		$pathsend = "$mypath/$newfile";
		
		$path = "$serverpath/$newfile";
		
		$original_file = "$picture";
		
		//echo $original_file;
	
	   $image->save($path);
	    return $pathsend; 
		return $original_file; 
		
	  }
  else
  {
   return "File type not allowed."; 
  }
  
}
else
{
  return "File size is too big - please reduce file size and try again."; 
}


}



// Upload images
/*function upload_all_photo($file,$target,$name)
{
	$serverpath = $target;
	//echo $serverpath;		// Path to where images should be uploaded to on the server.
	$urltoimages = $target; 	// Web address to where the images are accessible from.
	$maxsize = "50000000"; 				// Example - 20000 is the same as 20kb
	// CONFIG END
	$send_message = "";
	
	$picture = $_FILES[$file]['name'];
	
	// If you add your own file types don't forget to add an uppercase version.
	$allowedfiles[] = "GIF";
	$allowedfiles[] = "gif";
	$allowedfiles[] = "JPG";
	$allowedfiles[] = "jpg";
	$allowedfiles[] = "PNG";
	$allowedfiles[] = "png";
	$allowedfiles[] = "JPEG";
	$allowedfiles[] = "jpeg";
	$done = "No";
	global $Error_Uploading_Image;
	$Error_Uploading_Image = 1;		  
	
	if($_FILES[$file]['size'] > $maxsize)
	{
		$send_message = "<li><font color='#ff0000'>File size is too big - please reduce file size and try again.</font></li>";
		$Error_Uploading_Image = 0;	
	}
	else 
	{	
		$newname=$name;
		$pos = strpos($picture,".",0);
		$ext = trim(substr($picture,$pos+1,strlen($picture))," ");	
		$newfile = $newname . "." . $ext;
		
		if (strstr($serverpath,'../')!='')
		{
			$mypath = trim(substr($serverpath,3,strlen($serverpath))," ");
		}
		else
		{
			$mypath = $serverpath;
		}
		
		$pathsend = "$mypath/$newfile";
		
		$path = "$serverpath/$newfile";
		
		$send_message = $pathsend;
		
		foreach($allowedfiles as $allowedfile) 
		{					
						
			if (substr($picture, -3) == $allowedfile) 
			{
					move_uploaded_file($_FILES[$file]['tmp_name'], "$path");
					$done  = "yes";
					$photo = 1; 
			}
		}
		if($done != "yes") 
		{ 
		 $send_message = "<li><font color='#ff0000'>File is not allowed... Please upload jpg 0r gif file only</font></li>"; 
		 $Error_Uploading_Image = 0;	
		 }
				
	}
	
	return $send_message;
}*/

function upload_all_photo($file,$target,$name)
{
	$serverpath = $target;
	//echo $serverpath;		// Path to where images should be uploaded to on the server.
	$urltoimages = $target; 	// Web address to where the images are accessible from.
	$maxsize = "5000000000"; 				// Example - 20000 is the same as 20kb
	// CONFIG END
	$send_message = "";
	
	echo $picture = $_FILES[$file]['name'];
	
	// If you add your own file types don't forget to add an uppercase version.
	$allowedfiles[] = "GIF";
	$allowedfiles[] = "gif";
	$allowedfiles[] = "JPG";
	$allowedfiles[] = "jpg";
	$allowedfiles[] = "PNG";
	$allowedfiles[] = "png";
	$allowedfiles[] = "JPEG";
	$allowedfiles[] = "jpeg";
	$allowedfiles[] = "webp";
	$done = "No";
	global $Error_Uploading_Image;
	$Error_Uploading_Image = 1;		  
	
	if($_FILES[$file]['size'] > $maxsize)
	{
		$send_message = "File size is too big - please reduce file size and try again.";
		$Error_Uploading_Image = 0;	
	}
	else 
	{	
		$newname=$name;
		$pos = strpos($picture,".",0);
		$ext = trim(substr($picture,$pos+1,strlen($picture))," ");	
		$newfile = $newname . "." . $ext;
		
		if (strstr($serverpath,'../')!='')
		{
			$mypath = trim(substr($serverpath,3,strlen($serverpath))," ");
		}
		else
		{
			$mypath = $serverpath;
		}
		
		$pathsend = "$mypath/$newfile";
		
		$path = "$serverpath/$newfile";
		
		$send_message = $pathsend;
		//echo substr($picture, strpos($picture, ".") + 1);
		foreach($allowedfiles as $allowedfile) 
		{					
			/*if (substr($picture, -3) == $allowedfile)*/
			//if (substr($picture, -3) == $allowedfile)
			
			if (substr($picture, strpos($picture, ".") + 1) == $allowedfile) 
			{
				//echo "bbb";
					move_uploaded_file($_FILES[$file]['tmp_name'], "$path");
					$done  = "yes";
					$photo = 1; 
			}
		}
		//exit();
		if($done != "yes") 
		{ 
		 $send_message = "error"; 
		 $Error_Uploading_Image = 0;	
		 }
				
	}
	
	return $send_message;
}

function upload_all_resumes($file,$target,$name)
{
	$serverpath = $target;			// Path to where resumes should be uploaded to on the server.
	$urltoimages = $target; 	// Web address to where the resumes are accessible from.
	$maxsize = "50000000"; 				// Example - 20000 is the same as 20kb
	// CONFIG END
	$send_message = "";
	
	$picture = $_FILES[$file]['name'];
	
	// If you add your own file types don't forget to add an uppercase version.
	
	$done = "No";
	global $Error_Uploading_Image;
	$Error_Uploading_Image = 1;		  
	
	if($_FILES[$file]['size'] > $maxsize)
	{
		$send_message = "<li><font color='#ff0000'>File size is too big - please reduce file size and try again.</font></li>";
		$Error_Uploading_Image = 0;	
	}
	else 
	{	
		//$newname=$name;
		//$pos = strpos($picture,".",0);
		//$ext = trim(substr($picture,$pos+1,strlen($picture))," ");	
		//$newfile = $newname . "." . $ext;
		$newfile = $name;
		
		if (strstr($serverpath,'../')!='')
		{
			$mypath = trim(substr($serverpath,3,strlen($serverpath))," ");
		}
		else
		{
			$mypath = $serverpath;
		}
		
		$pathsend = "$mypath/$newfile";
		
		$path = "$serverpath/$newfile";
		
		$send_message = $pathsend;
		
		move_uploaded_file($_FILES[$file]['tmp_name'], "$path");
		$photo = 1; 
	}
	return $send_message;
}
// Get address from latitude and longitude
/*function getaddress($lat,$lng)
{
$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
$json = @file_get_contents($url);
$data=json_decode($json);
$status = $data->status;
if($status=="OK")
return $data->results[0]->formatted_address;
else
return false;
}*/

//Get latitude and longitude from the given address

function getlatlng12($location1)
{

$address1 = $location1;
//echo $address;

$geo12 = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address1).'&sensor=false');

$geo12 = json_decode($geo12, true);

if ($geo12['status'] = 'OK') {

  $latitude12 = $geo12['results'][0]['geometry']['location']['lat'];
  $longitude12 = $geo12['results'][0]['geometry']['location']['lng'];
}
$latlng12 = array("lat12"=>$latitude12, "lng12"=>$longitude12);

return($latlng12);
}
//Find distance between two co-ordinates 
/*function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2) {
    $theta = $longitude1 - $longitude2;
    $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;
    return $miles; 
}*/
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
      $theta = $lon1 - $lon2;
      $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist  = acos($dist);
      $dist  = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit  = strtoupper($unit);
    
      if ($unit == "K") {
          return ($miles * 1.609344);
      } else if ($unit == "N") {
          return ($miles * 0.8684);
      } else {
          return $miles;
      }
}
 
function get123Distance($addressFrom, $addressTo, $unit){
    //Change address format
    $formattedAddrFrom = str_replace(' ','+',$addressFrom);
    $formattedAddrTo = str_replace(' ','+',$addressTo);
    
    //Send request and receive json data
    $geocodeFrom = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false');
    $outputFrom = json_decode($geocodeFrom);
    $geocodeTo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false');
    $outputTo = json_decode($geocodeTo);
    
    //Get latitude and longitude from geo data
    $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
    $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
    $latitudeTo = $outputTo->results[0]->geometry->location->lat;
    $longitudeTo = $outputTo->results[0]->geometry->location->lng;
    
    //Calculate distance from latitude and longitude
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
    if ($unit == "K") {
        return ($miles * 1.609344).' km';
    } else if ($unit == "N") {
        return ($miles * 0.8684).' nm';
    } else {
        return $miles.' mi';
    }
}
function calculateDistance($lat1, $lng1, $lat2, $lng2, $unit="km")
{
    $radius = 6371; // mean radius of the earth in kilometers
    $lat1 = (float)$lat1;
    $lat2 = (float)$lat2;
    $lng1 = (float)$lng1;
    $lng2 = (float)$lng2;
    
    
    // calculation of distance in km using Great Circle Distance Formula
    $dist = $radius *
            acos( sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1)) );
    
    switch ( strtolower($unit) )
    {
        case 'm' :     // miles
            $dist = $dist / 1.609;
            break;
        case 'n' :     // nautical miles
            $dist = $dist / 1.852;
            break;
        case 'i' :     // inch
            $dist = $dist * 39370;
            break;
    }
    
    return $dist;
}

function calculateDistance1($lat1, $lng1, $lat2, $lng2, $unit="km")
{
    $radius = 6371; // mean radius of the earth in kilometers
    $lat1 = (float)$lat1;
    $lat2 = (float)$lat2;
    $lng1 = (float)$lng1;
    $lng2 = (float)$lng2;
   //echo $lat1."<br/>".$lat2."<br/>".$lng1."<br/>".$lng2."<br/>";
    
    // calculation of distance in km using Great Circle Distance Formula
    $dist = $radius *
            acos( sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1)) );
    
    switch ( strtolower($unit) )
    {
        case 'm' :     // miles
            $dist = $dist / 1.609;
            break;
        case 'n' :     // nautical miles
            $dist = $dist / 1.852;
            break;
        case 'i' :     // inch
            $dist = $dist * 39370;
            break;
    }
    
    return $dist;
}

// Show out in proper format
function tep_db_output($string) {
return htmlspecialchars($string);
}
// proper input
function tep_db_input($string) {
if (function_exists('$mysqli -> real_escape_string')) {
return $mysqli -> real_escape_string($string);
} elseif (function_exists('mysqli_escape_string')) {
return mysqli_escape_string($string);
}
return addslashes($string);
}

function createAlias($str){
$str=str_replace('-',' ',$str);
//echo $str;

$alias_array = explode(" ",$str);
for($i = 0; $i < count($alias_array); $i++){
$alias_str=preg_replace("/[^A-Za-z0-9]/", '',strtolower(trim($alias_array[$i])));
if(trim($alias_str)!= ''){
$new_array[] = $alias_str; 
}
}
if (is_array($new_array)) {
$new_str = implode("-",$new_array);
}
return $new_str;
}

function checkcategory($category)
{
global $db;
$sql = "SELECT * FROM category WHERE fld_alias='".$category."'" ;
$execute = mysqli_query($con,$sql)or die(mysqli_error());
$num = mysqli_num_rows($execute);
return($num);
	
}





/*function addsalesofficer($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO sales_officer SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editsalesofficer($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE sales_officer SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}
*/

function addsalesofficeradmin($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editsalesofficeradmin($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE admin_id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}


function addsalesexecutiveadmin($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editsalesexecutiveadmin($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE admin_id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}


function addclient($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO client SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editclient($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE client SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addsupplier($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO supplier SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editsupplier($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE supplier SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addcurrency($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO currency SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editcurrency($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE currency SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addunit($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO unit SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editunit($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE unit SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addtax($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO tax SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function edittax($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE tax SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}
function addproducttype($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO product_type SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editproducttype($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE product_type SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}




function addproduct($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO products SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editproduct($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE products SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addprofile($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO profile SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editprofile($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE profile SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}

function addbankdetail($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO bank_detail SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .="fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}



function editbankdetail($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE bank_detail SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE id=$id";
//echo $query; exit;
$result = mysqli_query($con,$query);
return($result);
}






function editdesignation($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE designation SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE fld_id=$id";
//echo $query; exit;
$stmt= $db->prepare($query);
$result = $stmt->execute();
return($result);
}




function getcategoryname($id)
{
global $db;
$sql="SELECT fld_category FROM category WHERE fld_id = $id ";
$execute = mysqli_query($con,$sql)or die('<br>'.$sql.'<br>'.mysqli_error());	
$result = mysqli_fetch_array($execute);	
return ($result['fld_category']);
}


function get_authorization_val($autho_id)
{
global $db;
$sql = "SELECT * FROM  authorisation WHERE fld_id='".$autho_id."'" ;
$execute = mysqli_query($con,$sql)or die(mysqli_error());
$result = mysqli_fetch_array($execute);
return($result['fld_authorisation']);
	
}

function checkvendorexist($category)
{
global $db;
$sql = "SELECT * FROM vendor WHERE fld_alias='".$category."'" ;
$execute = mysqli_query($con,$sql)or die(mysqli_error());
$num = mysqli_num_rows($execute);
return($num);	
	
}
function checkworktype($category)
{
global $db;
$sql = "SELECT * FROM businesstype WHERE fld_alias='".$category."'" ;
$execute = mysqli_query($con,$sql)or die(mysqli_error());
$num = mysqli_num_rows($execute);
return($num);	
	
}
function addvendor($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO vendor SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function editvendor($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE vendor SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_mdate = '".$date."' WHERE fld_id='".$id."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$c=$stmt->execute();
return($c);
}

function edit_vendor_info($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE vendor SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_mdate = '".$date."' WHERE fld_id='".$id."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$c=$stmt->execute();
return($c);	
}

function checkusername($username)
{
global $db;
$query = "SELECT fld_username FROM vendor WHERE fld_username = '".$username."'";
$stmt = $db->prepare($query);	
$stmt->execute();
return ($stmt->rowCount());
}	

function checkemailexit($email)
{
global $db;
$query = "SELECT fld_email FROM vendor WHERE fld_email = '".$email."'";
$stmt = $db->prepare($query);	
$stmt->execute();
return ($stmt->rowCount());
}	

function editvendorbusinessinfo($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE vendor SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_cdate = '".$date."' WHERE fld_id='".$id."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$c=$stmt->execute();
return($c);
}

function checkousernameexist($fld_username)
{
	global $db;
	$query = $db->prepare("SELECT * FROM admin WHERE uid = '".$fld_username."'");
	$query -> execute();
	return($query->rowCount());

}

function checkoemailexist($fld_email)
{
	global $db;
	$query = $db->prepare("SELECT * FROM admin WHERE email = '".$fld_email."'");
	$query -> execute();
	return($query->rowCount());

}



function addoperator($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO  admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function addvisiter($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO viewers SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function editoperator($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE  admin SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE admin_id=".$id."";
//echo $query; exit;
$stmt=$db->prepare($query);
$rs = $stmt->execute();
return($rs);
}


function getusertype($id)
{
global $db;
$query = $db->prepare("SELECT fld_usertype FROM  usertype WHERE fld_id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['fld_usertype']);
}

function getusername($id)
{
global $db;
$query = $db->prepare("SELECT name FROM  admin WHERE admin_id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['name']);
}


function getvusername($id)
{
global $db;
$query = $db->prepare("SELECT fld_fname FROM  user WHERE fld_id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['fld_fname']);
}

function random_password( $length = 8 ) {
$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
$password = substr( str_shuffle( $chars ), 0, $length );
return $password;
}

function getvehicletype($id)
{
global $db;
$query = $db->prepare("SELECT vehicle FROM vehicle WHERE id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['vehicle']);
}

function getlabourcharges($id)
{
global $db;
$query = $db->prepare("SELECT vehicle_type FROM labour_charges  WHERE id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['vehicle_type']);
}


function getbrandname($id)
{
global $db;
$query = $db->prepare("SELECT brand FROM brand WHERE id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['brand']);
}

function getmodelname($id)
{
global $db;
$query = $db->prepare("SELECT model FROM model WHERE id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['model']);	
}

function getbusinesstype($id)
{
global $db;
$query = $db->prepare("SELECT fld_name FROM businesstype WHERE fld_id=".$id."");
$query->execute();
$rs=$query->fetch();
return($rs['fld_name']);
}

function check_vendor_exist($cn)
{
global $db;
$query = $db->prepare("SELECT fld_vendor, fld_address, fld_mobile1 FROM vendor WHERE fld_mobile1 = '".$cn."'");
$query->execute();
$total = $query->rowCount();
$rs = $query->fetch();
$data = array("fld_vendor"=>$rs['fld_vendor'], "fld_address"=>$rs['fld_vendor'], "fld_mobile1"=>$rs['fld_mobile1'], "flag"=>$total);
return($data);	
}

/* Get active caller*/
function getcaller()
{
$carray=array();	
$usertype=4;
$status = 1;
global $db;
$query = $db->prepare("SELECT admin_id FROM admin WHERE user_type='".$usertype."' AND fld_status = '".$status."'");
$query->execute();
while($rs = $query->fetch())
{
$carray[] = $rs['admin_id'];	
}
return($carray);	
}

function getcallerid()
{
$usertype=4;
$status = 1;
global $db;
$query = $db->prepare("SELECT admin_id, min(fld_count)FROM admin WHERE user_type='".$usertype."' AND fld_status = '".$status."' GROUP BY fld_count");
$query->execute();	
$rs=$query->fetch();
$minid=$rs['admin_id'];
return($minid);
}




function addvendorpayment($field)
{
global $db;
//print_r($field); exit;
$query = "INSERT INTO  vendor_payment_method SET ";
$i=0;
foreach($field as $key => $values)
{
$query .= "$key= '".$values;
if($i < count($field))
{
$query .=", ";	
}
$i++;
}
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);	
}

function getcityid($str)
{
global $db;
$query = $db->prepare("SELECT location_id FROM location WHERE name='".$str."'");
$query->execute();
$rs = $query->fetch();
return($rs['location_id']);
}

function getcityname($str)
{
global $db;
$query = $db->prepare("SELECT name FROM location WHERE location_id='".$str."'");
$query->execute();
$rs = $query->fetch();
return($rs['name']);
}

function addlocality($field)
{
global $db;
$date = date('Y-m-d H:i:s');
$query = "INSERT INTO area SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date='".$date."'";
//echo $query;
//exit;
$stmt=$db->prepare($query);	
$stmt->execute();
$last_id=$db->lastInsertId();
return($last_id);
}

function checklocality1exist($str)
{
global $db;
$query = $db->prepare("SELECT * FROM area WHERE fld_sublocality_1='".$str."'");
$query->execute();
$total = $query->rowCount();
return($total);	
}

function checkid($str)
{
	global $db;
	$query = $db->prepare("SELECT * FROM area WHERE fld_sublocality_1='".$str."'");
	$query->execute();
	$totalval = $query->fetch();
	return($totalval['fld_id']);	
}
function checkid2($str, $str1)
{
	global $db;
	$query = $db->prepare("SELECT * FROM area WHERE fld_sublocality_2='".$str."' and  fld_sublocality_1='".$str1."'");
	$query->execute();
	$totalval = $query->fetch();
	return($totalval['fld_id']);	
}

function getmaxvisitor($str)
{
global $db;
$query = $db->prepare("SELECT * FROM viewers WHERE vendor_id='".$str."'");
$query->execute();
$total = $query->rowCount();
return($total);	
}


function checklocality2exist($str, $str1)
{
global $db;
$query = $db->prepare("SELECT * FROM area WHERE fld_sublocality_2='".$str."' and fld_sublocality_1 = '".$str1."'");
$query->execute();
$total = $query->rowCount();
return($total);	
}
function get_max_vendor()
{
global $db;	
$query = $db->prepare("SELECT MAX(fld_id) as m FROM vendor");
$query->execute();
$result = $query->fetch();
//print_r($result);
//exit;
return($result['m']);	
}

function getCoordinates($address){
$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern
$url = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";
$response = file_get_contents($url);
$json = json_decode($response,TRUE); //generate array object from the response from the web
return ($json['results'][0]['geometry']['location']['lat']."_".$json['results'][0]['geometry']['location']['lng']);
}


function get_min_vendor()
{
global $db;	
$query = $db->prepare("SELECT MIN(fld_id) as mi FROM vendor");
$query->execute();
$result = $query->fetch();
return($result['mi']);	
}

function getdistance($from, $to)
{
	//echo LINK_URL_HOME;
	if(LINK_URL_HOME == "http://192.168.2.1/motonik/")
	{
		//offline key
		//$apikey="AIzaSyDmygIXim95TORXQ0O4Cb-JoRe7VQB48kA";
		$apikey="AIzaSyB_BhYzatkEsd4j9dF4XzOQmIzxiBT0De0";	
	}
	else
	{
		//online key
		// Server Key
		$apikey="AIzaSyCQO694fWm0YiK_BeaH_OCxPkANGkvzfzE";
	}

$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(' ', '+', $from)."&destination=".str_replace(' ', '+', $to)."&sensor=true&key=".$apikey."&signed_in=true";
////------- Offline  Key  AIzaSyDnY_7t6OLxQ6vASGjOu-kNwOA8-NLCmOs    -----------------//

/*$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(' ', '+', $from)."&destination=".str_replace(' ', '+', $to)."&sensor=true";*/
				//echo $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                $response_all = json_decode($response);
                //print_r($response);
                $distance = $response_all->routes[0]->legs[0]->distance->text;
				return $distance;
				
}

function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;
    $kilometers = $miles * 1.609344;
    return $kilometers;
}

function get_distance($lat1, $lat2, $long1, $long2)
{
    /* These are two points in New York City */
    $point1 = array('lat' => $lat1, 'long' => $long1);
    $point2 = array('lat' => $lat2, 'long' => $long2);

    $distance = getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
    return $distance;
}


function getlocationname($id)
{
global $db;
$query = $db->prepare("SELECT name FROM location WHERE location_id = '".$id."'");
$query->execute();
$result = $query->fetch();
return($result['name']);
}


function addbooking($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO booking SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function addbookingServcie($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO booking_registration SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function editbooking($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "UPDATE booking SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_mdate = '".$date."' WHERE fld_id='".$id."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$c=$stmt->execute();
return($c);
}



function addpayment($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO payment   SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function labourcharge($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO labour_charges SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}


function update_labourcharge($field, $id)
{
global $db;
$date = date('Y-m-d H:i:s');
$query = "Update  labour_charges  SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."' WHERE labour_id='".$id."'";
echo $query;
exit();
$result = mysqli_query($con,$query);
$lastid = mysqli_insert_id();
return($lastid);
}

function addnewservice($field)
{
global $db;
$date = date('Y-m-d H:i:s');
//print_r($field); exit;
$query = "INSERT INTO add_services SET ";
foreach($field as $key => $values)
{
$query .= "$key= '".$values."', ";
}
$query .=" fld_date = '".$date."'";
//echo $query; exit;
$stmt=$db->prepare($query);
$stmt->execute();
$lastid = $db->lastInsertId();
return($lastid);
}

function upload_all_photo2($file,$target,$name)
{
	$serverpath = $target;
	//echo $serverpath;		// Path to where images should be uploaded to on the server.
	$urltoimages = $target; 	// Web address to where the images are accessible from.
	$maxsize = "5000000000"; 				// Example - 20000 is the same as 20kb
	// CONFIG END
	$send_message = "";
	
	echo $picture = $_FILES[$file]['name'];
	
	// If you add your own file types don't forget to add an uppercase version.
	$allowedfiles[] = "pdf";
	$allowedfiles[] = "PDF";
	$done = "No";
	global $Error_Uploading_Image;
	$Error_Uploading_Image = 1;		  
	
	if($_FILES[$file]['size'] > $maxsize)
	{
		$send_message = "File size is too big - please reduce file size and try again.";
		$Error_Uploading_Image = 0;	
	}
	else 
	{	
		$newname=$name;
		$pos = strpos($picture,".",0);
		$ext = trim(substr($picture,$pos+1,strlen($picture))," ");	
		$newfile = $newname . "." . $ext;
		
		if (strstr($serverpath,'../')!='')
		{
			$mypath = trim(substr($serverpath,3,strlen($serverpath))," ");
		}
		else
		{
			$mypath = $serverpath;
		}
		
		$pathsend = "$mypath/$newfile";
		
		$path = "$serverpath/$newfile";
		
		$send_message = $pathsend;
		//echo substr($picture, strpos($picture, ".") + 1);
		foreach($allowedfiles as $allowedfile) 
		{					
			/*if (substr($picture, -3) == $allowedfile)*/
			//if (substr($picture, -3) == $allowedfile)
			
			if (substr($picture, strpos($picture, ".") + 1) == $allowedfile) 
			{
				//echo "bbb";
					move_uploaded_file($_FILES[$file]['tmp_name'], "$path");
					$done  = "yes";
					$photo = 1; 
			}
		}
		//exit();
		if($done != "yes") 
		{ 
		 $send_message = "error"; 
		 $Error_Uploading_Image = 0;	
		 }
				
	}
	
	return $send_message;
}


?>