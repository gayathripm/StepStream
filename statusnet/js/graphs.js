jQuery(document).ready()
{
alert("inside");
var user_id_graph = document.getElementById('user_id_graph').value;	

alert(user_id_graph);
 	var input_array = new Array();
var xmlhttp = false;
if(!xmlhttp)
{
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
alert("mozilla");	  
	xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
alert("ie");
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function()
	  {
               alert("inside on state change");
alert("status" + xmlhttp.status);
alert("ready state" + xmlhttp.readyState);
		  if (xmlhttp.readyState==4 && xmlhttp.status==200)
		    {
		   alert("object ready!!!");

				     alert(xmlhttp.responseText);
   var json_data_object = eval("(" + xmlhttp.responseText + ")");


		      for(var i = 0; i < json_data_object.points.length ; i++)
			{
			      input_array[i] =  [json_data_object.points[i], {label: json_data_object.names[i]}]; 

			      alert("points" + json_data_object.points[i]);
			      alert("name" + json_data_object.names[i]);
			}
		   alert("length1" + input_array.length);

		    }
	  }
	xmlhttp.open("GET","http://statusnet/ajax.php?user_id=" + user_id_graph,true);
	xmlhttp.send();


   jQuery('#steps_graph_div').tufteBar({
          data: input_array,
          barWidth: 0.8,
          barLabel:  function(index) { return this[0] + ' points' },
          axisLabel: function(index) { return this[1].label },
          color:     function(index) { return ['#E57536', '#82293B'][index % 2] }
        });
}


}



