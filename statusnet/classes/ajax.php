<?php

//Get Post Variables. The name is the same as
//what was in the object that was sent in the jQuery
if (isset($_POST['sendValue'])){
    $value = $_POST['sendValue'];  
}else{
    $value = "";
}

//Because we want to use json, we have to place things in an array and encode it for json.
//This will give us a nice javascript object on the front side.
echo json_encode(array("returnValue"=>"This is returned from PHP : ".$value)); 

?>
