<?php
	header("content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$input_message = $_REQUEST['Body'];
	$input_arr = explode(' ' , $input_message);
	$username = $input_arr[0];
	$steps = $input_arr[1];
?>

<Response>
	<Sms>Hello. Your username was : <?php echo $username; ?> and You had taken <?php $steps ?> steps</Sms>
</Response>
