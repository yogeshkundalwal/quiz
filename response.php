<?php
require 'constants.php';

##################################### no of quiz given by a student till now ########
$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
function quiz_given_bystudent() {
	$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
	$filter1= array('student_name' => "Yogesh Kundalwal");
	$options1 = [];
	$query1 = new MongoDB\Driver\Query($filter1, $options1);
	$rows1 = $m->executeQuery('Data.given_quiz', $query1);
	foreach ($rows1 as  $value) {
		$quiz_data[] = json_decode(json_encode($value),true);
	}
	$quiz_count = count($quiz_data);
	return $quiz_count;
}
###################################################################################
############### delete entry in given quiz collection if strudents have less then 80 %
function deletequiz(){
	if($no_of_studentabove80 < 80){
		$bul = new MongoDB\Driver\BulkWrite;
		$bul->delete(['quiz_id' => $quiz_id], []);
		$result = $manager->executeBulkWrite('Data.given_quiz', $bul);
	}
}
#################################################################################3
$filter=[];
$options = [];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $m->executeQuery('Data.Collection', $query);
// debug($rows)
foreach ($rows as  $value) {
	$data[] = json_decode(json_encode($value),true);
}
$uni_session[] = $data['0']['session'];
$centers[] = $data['0']['center_id'];
$uni_quiz_id[] = $data['0']['quiz_id'];
foreach ($data as $value) {
	if (!in_array($value['session'], $uni_session))
	{
		$uni_session[] = $value['session'];
	}
	if (!in_array($value['center_id'], $centers))
	{
		$centers[] = $value['center_id']; 
	}
	if (!in_array($value['quiz_id'], $uni_quiz_id))
	{
		$uni_quiz_id[] = $value['quiz_id']; 
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>responses of quiz</title>
		<style>
			div.a {
			  text-indent: 50px;
			}
		</style>

		<!-- <link rel="stylesheet" type="text/css" href="css/style.css" /> -->
	</head>
	<body>
		<form action="response.php" method="POST">
			<span>Kindly fill the following details to proceed : </span>
			<select name = 'session_id'>
				<option value="">Select session</option>
				<?php foreach ($uni_session as $value) : ?>
					<option value="<?= $value ?>"><?= $value ?></option>
				<?php endforeach ?>
			</select>
			<select name = 'center_id'>
			<option value="">Select center</option>
				<?php foreach ($centers as $value) : ?>
					<option value="<?= $value ?>"><?= $value ?></option>
				<?php endforeach ?>
			</select>
			<select name = 'quiz_id'>
			<option value="">Select quiz id</option>
				<?php foreach ($uni_quiz_id as $value) : ?>
					<option value="<?= $value ?>"><?= $value ?></option>
				<?php endforeach ?>
			</select>
			<input type="submit">
		</form>
		<?php 
		if(isset($_POST['quiz_id']) && !empty($_POST['quiz_id']) && isset($_POST['session_id']) && !empty($_POST['session_id']) && isset($_POST['center_id']) && !empty($_POST['center_id'])) : 
			$filter_a = array('center_id' => $_POST['center_id'], 'session' => $_POST['session_id'], 'quiz_id' => $_POST['quiz_id'] );
			$options_a = [];
			$query_a = new MongoDB\Driver\Query($filter_a, $options_a);
			$rows_a = $m->executeQuery('Data.Collection', $query_a);
			foreach ($rows_a as  $value) {
				$analytics_data[] = json_decode(json_encode($value),true);
			}
			############ API hit to get questions and options
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
			$response_ = curl_exec($ch);
			curl_close($ch);
			$result_ = json_decode($response_, true);
			if( isset($result_['data']) && is_array($result_['data']) ) {
				$responseData_ = $result_['data'];
			} else {
				die($result['msg']);
			} 
			########### no_of_studentabove80
			$count = 0;$i = 0;
			foreach ($analytics_data as $value) {
				if($value['correct_percent'] >= 80) {
					$count = $count + 1;
				}
				$i = $i + 1;
			}
			$no_of_studentabove80 = ($count/$i)*100; 
			####################corrrected and incorrected question no #####
			foreach ($analytics_data as $key => $value) {
				for ($i=0; $i < count($value['given_answer']); $i++) { 
					if($value['given_answer'][$i] == $value['correct_answer'][$i]) {
						$corrected_no[$key][] = ($i+1);
					}
					else{
						$incorrected_no[$key][] = ($i+1);
					}
				}
			}
			###############################################################
			?>
			<div>
				<strong><span>percentage of students who have scored above 80% = <?= $no_of_studentabove80 ?>%</span></strong>
			</div>
			<br>
			<?php 
			$no_of_student = 0;
			foreach ($analytics_data as $value) {
				foreach ($value['given_answer'] as $key => $value_a) {
					$ans[$key][] = $value_a;
				}
				$no_of_student =  $no_of_student + 1;
			}
			foreach ($ans as $key => $value) {
				$result[] = array_count_values($value);
			}

			$options_ = array('a', 'b', 'c', 'd', 'e');
			foreach ($result as $key => $value) : ?>
				<div>
					<strong><span style="color:red;font:Georgia;">Question <?= ($key+1) ?> :<?= strip_tags($responseData_['quizQuestions'][$key]['mcq_question'])?> </span></strong><br>
				</div>
				<div>
				<?php foreach ($options_ as $option) : ?>
					<?php
					if( $responseData_['quizQuestions'][$key]['mcq_option'.$option] != "" ):
						if(isset($value[$option]) && !empty($value[$option])){  ?>
							<span> Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = <?= ($value[$option]/$no_of_student)*100 ?>%</span>
							<br>
							<?php } 
						else{ ?>
							<span>Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = 0</span>
							<br>	
						<?php } 
					endif;?>
				<?php endforeach ?> 
				<br>
			<?php endforeach ?>
				</div>
				<table id="myTable" border="1">
					<thead>
						<tr>
							<td>student_id</td>
							<td>student_name</td>
							<td>Performance</td>
							<td>Answered Correctly</td>
							<td>Answered Inorrectly</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach($analytics_data as $key => $value) :?>
							<tr>
								<td><?= $value['student_id'] ?></td>
								<td><?= $value['student_name'] ?></td>
								<td><?= $value['correct_percent'] ?>%</td>
								<td><?= implode(",",$corrected_no[$key]);   ?></td>
								<td><?= implode(",",$incorrected_no[$key]); ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				<button onclick="deletequiz()">Repeat Quiz</button>
				<p id="demo"></p>
			<?php endif ?>
	</body>
</html>
