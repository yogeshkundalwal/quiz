<?php
// include database file
include_once 'database.php';
//DB connection
$db = new DbManager();
$m = $db->getConnection();
//
$dbname     = 'Data';
$collection = 'given_quiz';
############### delete entry in given quiz collection if strudents have less then 80 %
$no_of_studentabove80 = $_POST['percentage'];
if($no_of_studentabove80 < 80){
	$bul = new MongoDB\Driver\BulkWrite;
	$bul->delete(['quiz_id' => $quiz_id], []);
	$result = $m->executeBulkWrite("$dbname.$collection", $bul);
	echo 'delete successfully';
}
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
		<h1>Deleted</h1>
	</div>
</body>
</html>