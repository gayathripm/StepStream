<?php
mysql_connect("statusnet", "statusnet", "PASSWORD") or die(mysql_error());
mysql_select_db("statusnet") or die(mysql_error());
$graphType = $_GET['graphType'];
$user_id = $_GET['user_id'];

if($graphType == 'relative')
{
 $points_array = array();
    $labels_array = array();
	$query = "SELECT * FROM user_points"; 
		 
	$result = mysql_query($query) or die(mysql_error());


	while($row = mysql_fetch_array($result)){
		 array_push($points_array,$row['cumulative_points']);
	       array_push($labels_array,$row['nickname']);
	}

}

else if($graphType == 'individual')
{
 $points_array = array();
    $labels_array = array();
	$query = "SELECT * FROM weekly_points where profile_id =1"; 
		 
	$result = mysql_query($query) or die(mysql_error());


	while($row = mysql_fetch_array($result)){
		 array_push($points_array,$row['cumulative_points']);
	       array_push($labels_array,$row['week_desc']);
	}

}


echo json_encode(array("points"=>$points_array,"names" => $labels_array));
  ?>
