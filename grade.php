<?php
require 'constants.php';
$mongo = new MongoDB\Driver\Manager("mongodb://localhost:27017");    
$bulk = new MongoDB\Driver\BulkWrite;
$quiz = new MongoDB\Driver\BulkWrite;
$quizes_center = new MongoDB\Driver\BulkWrite;
// debug($_POST, false);
############# records for per student given quiz ####
$check['student_name'] = $_POST['student_name'];
$check['quiz_id'] = $_POST['quiz_id'];
####################################################
############# total quizes taken on a perticular center ###
// $quiz_given['center_id'] = $_POST['center_id'];
$quiz_given['center_id'] = 'faridabad';
$quiz_given['quiz_id'] = $_POST['quiz_id'];
$filter= array( 'center_id' => $quiz_given['center_id']/*$_POST['center_id']*/ );
$options = [];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $mongo->executeQuery('Data.center_quizes', $query);	
foreach ($rows as  $value) {
	$data_for_quizes[] = json_decode(json_encode($value),true);
}
// debug($data_for_quizes);die();
foreach ($data_for_quizes as $value) {
	$data_quizes[] = $value['quiz_id'];
}
if (!in_array( $quiz_given['quiz_id'] , $data_quizes ) ){
	$quizes_center->insert($quiz_given);
	$mongo->executeBulkWrite('Data.center_quizes', $quizes_center);
}
##########################################################
$data = $_POST;
// $data['student_name'] = 'Test_student';
// $data['session_id'] = '15';
$data['total_correct'] = 0;
foreach( $data['given_answer'] as $key => $value ) {
	if( $data['correct_answer'][$key] == $value ) {
		$data['total_correct'] = $data['total_correct'] + 1;
	}
}
$data['correct_percent'] = ($data['total_correct']/count($data['given_answer']))*100;
$data['session'] = date("Y-m-d")."-".date("H");
######## just for test
// $data['center_id'] = $_POST['center_id'];
$data['center_id'] = 'faridabad';
######################
$bulk->insert($data);
$quiz->insert($check);
$mongo->executeBulkWrite('Data.Collection', $bulk);    # 'schooldb' is database and 'student' is collection.
$mongo->executeBulkWrite('Data.given_quiz', $quiz);  
?>

<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<title>Quiz</title>
	
	<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>

	<div id="page-wrap">
		<h1>Thank you</h1>
	</div>
	
</body>

</html>