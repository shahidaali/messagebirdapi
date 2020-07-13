<?php
require_once __DIR__.'/init.php';
$id = $_GET['id'] ?? 0;
if(empty($id)) {
	die('ID is missing.');
}
$db->select('contacts', ['id' => $id], null, 'id DESC');
$contact = $db->row();
if(!$contact) {
	die("Contact not found.");
}

$form_message = "";
$form_class = "";

$message = "";
if(isset($_POST['message'])) {
	$message = $_POST['message'];

	if(!empty($message)) {
		$messageBird = new \MessageBird\Client($API_KEY);

		$content = new \MessageBird\Objects\Conversation\Content();
		$content->text = $message;

		$sendMessage = new \MessageBird\Objects\Conversation\SendMessage();
		$sendMessage->from = CHANNEL_ID;
		$sendMessage->to = $contact->phone; // Channel-specific, e.g. MSISDN for SMS.
		$sendMessage->content = $content;
		$sendMessage->type = 'text';

		try {
		    $sendResult = $messageBird->conversationSend->send($sendMessage);
		    if(!empty($sendResult->status) && $sendResult->status == "accepted") {
		    	$form_message = "Message sent.";
		    	$form_class = "success";

		    	$db->update(
					'contacts', [
						// fields to be updated
						'message_sent' => $message,
						'is_sent' => 1,
						'sent_time' => date('Y-m-d H:i:s')
					], [
						// 'WHERE' clause
						'id' => $id
					]
				);
		    }
		    else {
		    	$form_message = "Message not sent.";
		    	$form_class = "error";
		    }
		    // print_r($sendResult);
		} catch (\Exception $e) {
		    $form_message = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
		    $form_class = "error";
		}
	}
	else {
		$form_message = "Please enter a message.";
		$form_class = "error";
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Send Message</title>
	<link rel="stylesheet" type="text/css" href="public/css/style.css">
</head>
<body>
	<h2>Reply to: <?= $contact->phone ?> (<?= $contact->first_name ?? "Unknown"; ?>)</h2>

	<?php if(!empty($form_message)): ?>
		<p class="form-message <?= $form_class; ?>"><?= $form_message; ?></p>
	<?php endif; ?>

	<form action="" method="POST">
		<p>
			<label>Enter Message</label><br>
			<textarea cols="60" rows="5" name="message"><?= $message ?></textarea>
		</p>
		<p>
			<button type="submit">Send</button>
		</p>
	</form>
</body>
</html>