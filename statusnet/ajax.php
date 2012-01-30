<?php
$my_conn = mysql_connect("statusnet", "statusnet", "PASSWORD") or die(mysql_error());
mysql_select_db("statusnet", $my_conn ) or die(mysql_error());

$profile_id = $_GET['user_id'];

 $points_array = array();
    $labels_array = array();
	$query = "SELECT * FROM weekly_points where profile_id =" . $profile_id ; 
		 
	$result = mysql_query($query) or die(mysql_error());


	while($row = mysql_fetch_array($result)){
		 array_push($points_array,$row['cumulative_points']);
	       array_push($labels_array,$row['week_desc']);
	}



mysql_close($my_conn);
echo json_encode(array("points"=>$points_array,"names" => $labels_array));
  ?>
