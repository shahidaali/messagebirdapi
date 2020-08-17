<?php
require_once __DIR__.'/init.php';

header("Content-Type: application/json; charset=UTF-8");

$request = $_POST;

// echo json_encode($_REQUEST);
// exit();

// $messages = [];
// if(empty($_POST['contact_msisdn'])) {
// 	$messages[] = "Request msisdn is missing.";
// }

$date = date('Y-m-d H:i:s');
$file = fopen("log.txt", "a");        
fwrite($file, "\r\nTEST ({$date})\r\n");
fwrite($file, json_encode($request));
fwrite($file, "\r\nTEST\r\n");
fclose($file);
exit();

if(empty($messages)) {
	$db->insert(
		'contacts', [
			'first_name' => $_POST['contact_firstName'],
			'last_name' => $_POST['contact_lastName'],
			'contact_id' => $_POST['contact_id'],
			'phone' => $_POST['contact_msisdn'],
			'message_received' => $_POST['payload'],
			'conversation_id' => $_POST['conversationId'],
			'request_data' => json_encode($_POST),
			'created' => date('Y-m-d H:i:s')
		]
	);

	echo json_encode([
		'status' => 'success',
		'message' => 'Contact saved.'
	]);
	exit();
}
echo json_encode([
	'status' => 'error',
	'message' => implode(",", $messages)
]);
exit();