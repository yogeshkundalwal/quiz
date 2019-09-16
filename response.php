<?php
require 'constants.php';
// include database file
include_once 'database.php';
//DB connection
$db = new DbManager();
$m = $db->getConnection();
$dbname      = 'quiz';
$collection  = 'quiz_data';
##################################### no of quiz given by a student till now ########
// function quiz_given_bystudent() {
// 	$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
// 	$filter1= array('student_name' => "Yogesh Kundalwal");
// 	$options1 = [];
// 	$query1 = new MongoDB\Driver\Query($filter1, $options1);
// 	$rows1 = $m->executeQuery('Data.given_quiz', $query1);
// 	$quiz_data = json_decode(json_encode(iterator_to_array($rows1)),true);
// 	$quiz_count = count($quiz_data);
// 	return $quiz_count;
// }
#####################################################################################
$filter=[];
$options = [];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $m->executeQuery("$dbname.$collection", $query);
$data = json_decode(json_encode(iterator_to_array($rows)),true);
$uni_session[] = $data['0']['session'];
$programs[] = $data['0']['programIds'];
// $centers[] = $data['0']['center_id'];
$uni_quiz_id[] = $data['0']['quiz_id'];
foreach ($data as $value) {
	if (!in_array($value['session'], $uni_session))
	{
		$uni_session[] = $value['session'];
	}
	// if (!in_array($value['center_id'], $centers))
	// {
	// 	$centers[] = $value['center_id']; 
	// }
	if (!in_array($value['programIds'], $centers))
	{
		$programs[] = $value['programIds']; 
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
			table {

				  border-collapse: collapse;
				  width: 100%;
				}

				th, td {
				  text-align: left;
				  padding: 8px;
				}

				tr:nth-child(even){background-color: #f2f2f2}

				th {
				  background-color: #4CAF50;
				  color: white;
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
			<option value="">Select program</option>
				<?php foreach ($programs as $value) : ?>
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
			$filter_a = array('programIds' => $_POST['center_id'], 'session' => $_POST['session_id'], 'quiz_id' => $_POST['quiz_id'] );
			$options_a = [];
			$query_a = new MongoDB\Driver\Query($filter_a, $options_a);
			$rows_a = $m->executeQuery("$dbname.$collection", $query_a);
			$analytics_data = json_decode(json_encode(iterator_to_array($rows_a)),true);
			############ API hit to get questions and options
			$jsonArr = array(
				'quizId' => $_POST['quiz_id']
			);
			$headers[0] = 'Content-Type: application/json';
			$headers[1] = 'securityToken: aTA3eEI2bHNMTXpNd3RiQU5GUUt1QnNQd2N4akJUTWhlSlBJOFQyb3hJZ0JuNW0xMkZVY2pURmo1M3l3VU9NSg==';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.virohan.com/myclassroom/v1/quiz/info');
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
						if( $analytics_data['0']['correct_answer'][$key] == $option ) {
							if(isset($value[$option]) && !empty($value[$option])){  ?>
								<span style="color: green;"> Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = <?= ($value[$option]/$no_of_student)*100 ?>%</span>
								<br>
							<?php } 
							else{ ?>
								<span style="color: green;">Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = 0</span>
								<br>	
							<?php } 
						}
						else {
							if(isset($value[$option]) && !empty($value[$option])){  ?>
								<span> Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = <?= ($value[$option]/$no_of_student)*100 ?>%</span>
								<br>
							<?php } 
							else{ ?>
								<span>Selected option <?= $option ?> : <strong><?= $responseData_['quizQuestions'][$key]['mcq_option'.$option]?></strong> = 0</span>
								<br>	
							<?php } 
						}
					endif;?>
				<?php endforeach ?> 
				<br>
			<?php endforeach ?>
				</div>
				<table>
					<thead>
						<tr>
							<th>student_id</th>
							<th>student_name</th>
							<th>Performance</th>
							<th>Answered Correctly</th>
							<th>Answered Inorrectly</th>
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
				<font action = 'deletequiz.php'>
					<input type = "hidden" name="percentage" value="<?= $no_of_studentabove80; ?>"  />
					<input type="submit" name="repeat_quiz">
				</font>
				<!-- <p id="demo"></p> -->
			<?php endif ?>
	</body>
</html>
