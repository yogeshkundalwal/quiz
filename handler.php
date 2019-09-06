<?php

// var_dump($_POST['action']);
$action = $_REQUEST['action'];

$response = array(
	'status' => 'failed',
	'message' => 'Something went wrong'
);

switch ($action) {
	case 'submit-response':
		$response = array(
			'status' => 'success',
			'message' => 'Here we go' 
		);
		break;
}

// \Util::returnJSON($response);
return $response;