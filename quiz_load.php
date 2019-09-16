<?php
//debug file
require 'constants.php';
// include database file
include_once 'database.php';
//DB connection
$db = new DbManager();
$m = $db->getConnection();
//db name and table
$dbname = 'quiz';
$collection = 'given_quiz';
########################## API hit
if( isset($_GET['quiz_id']) && !empty($_GET['quiz_id']) ) {
	$jsonArr = array(
		'quizId' => $_GET['quiz_id']
	);
	$headers[0] = 'Content-Type: application/json';
	// $headers[1] = 'securityToken: aTA3eEI2bHNMTXpNd3RiQU5GUUt1QnNQd2N4akJUTWhlSlBJOFQyb3hJZ0JuNW0xMkZVY2pURmo1M3l3VU9NSg==';
	$headers[1] = 'securityToken: '.$_GET['securityToken'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.virohan.com/myclassroom/v1/quiz/info');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonArr));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($response, true);
	if( isset($result['data']) && is_array($result['data']) ) {
		$responseData = $result['data'];
		$mcqAnswerTypes = array('a', 'b', 'c', 'd', 'e');
	} else {
		// echo "hi this is end!";
		die($result['message']);
	}
} else {
	die('Enter a quiz_id');
	// header("Location: Index.php");
}
################### checking that student already given the test or not ##############
$filter= array('student_name' => $responseData['studentInfo']['first_name']." ".$responseData['studentInfo']['last_name'] );
$options = [];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $m->executeQuery("$dbname.$collection", $query);
$data = json_decode(json_encode(iterator_to_array($rows)),true);
if( count($data) > 0 ) {
	foreach ($data as $key => $value) {
		if($value['quiz_id'] == $_GET['quiz_id']) {
			$message = "You have already given the quiz.";
			echo "<script type='text/javascript'>alert('$message');</script>";
			die();
		}
	}}
######################################################################################
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>PHP Quiz</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
	<div id="page-wrap">
		<h1><?= $responseData['quizInfo']['name']; ?></h1>
		<form action="grade.php" method="POST" id="quiz">
		  <?php $_id = uniqid()  ?>
		  <input type = "hidden" name="_id" value="<?= $_id; ?>"  />
		  <input type = "hidden" name="quiz_id" value="<?= $responseData['quizInfo']['id']; ?>"  />
		  <input type = "hidden" name="student_id" value="<?= $responseData['studentInfo']['id']; ?>"  />
		  <input type = "hidden" name="student_name" value="<?= $responseData['studentInfo']['first_name']." ".$responseData['studentInfo']['last_name']; ?>"  />
		  <input type = "hidden" name="programIds" value="<?= $responseData['quizInfo']['programIds']; ?>"  />
			<ol>
				<?php foreach( $responseData['quizQuestions'] as $key => $question ) : ?>
					<?php if( !empty($question['mcq_question_id']) ) : ?>
						<div class="mcq_question">
							<div class="question">
								<strong><span>Q.</span>
								<span><?= strip_tags($question['mcq_question']); ?></span></strong>
							</div>
							<?php foreach( $mcqAnswerTypes as $answerType ) : ?>
								<?php if( $question['mcq_option'.$answerType] != "" ): ?>
									<div class="option option<?= $answerType ?>">
										<input type="radio" name="given_answer[<?= $key ?>]" value="<?= $answerType ?>" />
										<label><?= $question['mcq_option'.$answerType]; ?></label>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
							<br>
						</div>
					<?php endif; ?>
					<input type = "hidden" name="correct_answer[]" value="<?= $question['mcq_answer']; ?>"  />
				<?php endforeach; ?>
			</ol>
			<input type="submit"/>
		</form>
	</div>
</body>
</html>	

