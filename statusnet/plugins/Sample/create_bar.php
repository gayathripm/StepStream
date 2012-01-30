<?php
include("phpgraphlib.php");
$graph = new PHPGraphLib(400,300);
$data = array("Alex"=>99, "Mary"=>98, "Joan"=>70, "Ed"=>90);
$graph->addData($data);
$graph->setTitle("Test Scores");
$graph->setTextColor("blue");
$graph->createGraph();
?>
