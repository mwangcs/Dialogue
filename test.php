<!--  //request= new XMLHttpRequestObject()
          // request.open("POST", "test.php", true)
          // request.setRequestHeader("Content-type", "application/json")
          // request.send(v) -->

<?php
	$str_json = file_get_contents('php://input');
	echo $str_json;
	echo "test";
	// $jfo = Json_decode($str_json);
	// $intent = $jfo->intent;
	// echo $intent;
	// $entities = $jfo->entities:

	// foreach ($entities as $entity){
	// 	echo $entity;
	// }
?>