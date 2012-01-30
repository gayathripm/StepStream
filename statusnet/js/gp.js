 $.post("ajax.php",{ sendValue: str },
            function(data){
                $('#awesome-graph2').html(data.returnValue);
            }, "json");
