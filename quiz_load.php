<?php
require 'constants.php';


if( isset($_POST['quiz_id']) && !empty($_POST['quiz_id']) ) {
	$jsonArr = array(
		'quizId' => $_POST['quiz_id']
		// 'secuirty_token' => $_POST['ecuirty_token']
	);
	$headers[1] = 'Content-Type: application/json';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://pulse.virohan.com/myclassroom/quizData');
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
		die($result['msg']);
	}
} else {
	header("Location: index.php");
}
################### checking that student already given the test or not ##############
$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$filter= array('student_name' => $responseData['studentInfo']['name']);
$options = [];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $m->executeQuery('Data.given_quiz', $query);
foreach ($rows as  $value) {
	$data[] = json_decode(json_encode($value),true);
}
foreach ($data as $key => $value) {
	if($value['quiz_id'] == $_POST['quiz_id']) {
		echo "you have given this quiz before!";
		// die("you have given this quiz before!");
	}
}
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
		  <input type = "hidden" name="student_name" value="<?= $responseData['studentInfo']['name']; ?>"  />
		 <!--  <input type = "hidden" name="center_id" value="<?= $responseData['studentInfo']['center']; ?>"  /> -->
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

