<?php
//debug file
require 'constants.php';
// include database file
include_once 'database.php';
//DB connection
$db = new DbManager();
$mongo = $db->getConnection();
//db and tables 
$dbname = 'quiz';
$collection_quizdata            = 'quiz_data';           //   All quiz data per student
$collection_given               = 'given_quiz';         // record the quiz id per student
// $collection_center              = 'center_quizes';  //  given quizes per center
$collection_totalquiz_inprogram = 'program_quizes';   //    quiz id and their respective programs 
//
$quiz_data     = new MongoDB\Driver\BulkWrite;
$student_quiz  = new MongoDB\Driver\BulkWrite;
$quizes_center = new MongoDB\Driver\BulkWrite;
$programs_quiz = new MongoDB\Driver\BulkWrite;
############# records for per student given quiz ####
$check['student_name'] = $_POST['student_name'];
$check['quiz_id']      = $_POST['quiz_id'];
$check['programIds']   = $_POST['programIds'];
//student name and given quiz 
$student_quiz->insert($check);
$mongo->executeBulkWrite("$dbname.$collection_given", $student_quiz);  
####################################################
###### total quizes taken on a perticular center ###
// $quiz_given['center_id'] = $_POST['center_id'];
// $quiz_given['center_id'] = 'faridabad';
// $quiz_given['quiz_id']   = $_POST['quiz_id'];
// $filter  = array( 'center_id' => $quiz_given['center_id']/*$_POST['center_id']*/ );
// $options = [];
// $query   = new MongoDB\Driver\Query($filter, $options);
// $rows    = $mongo->executeQuery("$dbname.$collection_center", $query);	
// $data_for_quizes = json_decode(json_encode(iterator_to_array($rows)),true);

// foreach ($data_for_quizes as $value) {
// 	$data_quizes[] = $value['quiz_id'];
// }
// if (!in_array( $quiz_given['quiz_id'] , $data_quizes ) ){
// 	$quizes_center->insert($quiz_given);
// 	$mongo->executeBulkWrite("$dbname.$collection_center", $quizes_center);
// }
##########################################################
$data = $_POST
$data['total_correct'] = 0;
foreach( $data['given_answer'] as $key => $value ) {
	if( $data['correct_answer'][$key] == $value ) {
		$data['total_correct'] = $data['total_correct'] + 1;
	}
}
$data['correct_percent'] = ($data['total_correct']/count($data['given_answer']))*100;
$data['session']         = date("d-m-Y")."-".date("H");
$data['date']            = date("d-m-Y");
$data['month']           = date('m-Y');
// $data['center_id']       = 'faridabad';    //for test
$quiz_data->insert($data);
$mongo->executeBulkWrite('$dbname.$ollection_quizdata', $quiz_data); 
##########################################################
//insert quiz_id and their programs
$data_pq['programIds']     = $_POST['programIds'];
$data_pq['quiz_id']      = $_POST['quiz_id'];

$filter  = array( 'quiz_id' => $_POST['quiz_id'],'programIds' => $_POST['programIds'] );
$options = [];
$query   = new MongoDB\Driver\Query($filter, $options);
$rows    = $mongo->executeQuery("$dbname.$collection_totalquiz_inprogram", $query);	
$data_p  = json_decode(json_encode(iterator_to_array($rows)),true);
if ( count($data_p) == 0 ){
	$programs_quiz->insert($data_pq);
	$mongo->executeBulkWrite("$dbname.$collection_totalquiz_inprogram", $programs_quiz);
}
####################
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