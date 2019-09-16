<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database file
include_once 'db.php';

$dbname = 'Data';
$collection = 'program_quizes';

//DB connection
$db = new DbManager();
$conn = $db->getConnection();

$data = json_decode(json_encode(file_get_contents("php://input", true)),true);

$data = json_decode($data,true);
echo $data['programIds'];
if( isset($data['programIds']) & !empty($data['programIds']) ){
	$filter = array('programIds' => $data['programIds'] );
	$option = [];
	$read = new MongoDB\Driver\Query($filter, $option);

	//fetch records
	$records = $conn->executeQuery("$dbname.$collection", $read);

	$total_quiz_in_program['total_quiz'] = count(json_decode(json_encode(iterator_to_array($records)),true));

	var_dump(json_encode($total_quiz_in_program));	
}
else {
	
	var_dump(http_response_code(404));
	$json = '{
		"status":"fail",
		"code":404,
		"msg":"invalid data"
	}';
	var_dump($json);
}

?>