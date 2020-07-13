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

$messageBird = new \MessageBird\Client($API_KEY);

$form_message = "";
$form_class = "";
// echo '<pre>';

$reply = "";
if(isset($_POST['reply'])) {
	$reply = $_POST['reply'];

	if(!empty($reply)) {
		$content = new \MessageBird\Objects\Conversation\Content();
		$content->text = $reply;

		$message = new \MessageBird\Objects\Conversation\Message();
		$message->channelId = CHANNEL_ID;
		$message->content = $content;
		$message->to = $contact->phone;
		$message->type = \MessageBird\Objects\Conversation\Content::TYPE_TEXT;

		try {
		    $conversation = $messageBird->conversationMessages->create(
		        $contact->conversation_id,
		        $message
		    );

		    $form_message = "Message sent.";
	    	$form_class = "success";

	    	$db->update(
				'contacts', [
					// fields to be updated
					'message_sent' => $reply,
					'is_sent' => 1,
					'sent_time' => date('Y-m-d H:i:s')
				], [
					// 'WHERE' clause
					'id' => $id
				]
			);

			$reply = "";
		    // print_r($conversation);

		} catch (\Exception $e) {
		    $form_message = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
			$form_class = "error";
		}
	}
}

try {
    $messages = $messageBird->conversationMessages->getList($contact->conversation_id);

    //  print_r($messages);
} catch (\Exception $e) {
    $form_message = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
	$form_class = "error";
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Send Message</title>
	<link rel="stylesheet" type="text/css" href="public/css/style.css">
</head>
<body>
	<h2>Conversation With: <?= $contact->phone ?> (<?= $contact->first_name ?? "Unknown"; ?>)</h2>

	<?php if(!empty($form_message)): ?>
		<p class="form-message <?= $form_class; ?>"><?= $form_message; ?></p>
	<?php endif; ?>

	<?php if(!empty($messages->items)): ?>
		<div class="inbox">
			<ul>
				<?php foreach (array_reverse($messages->items) as $key => $m) { ?>
					<?php // if($m->type != "text") continue; ?>
					<li class="inbox-item <?= $m->direction; ?>">
						<!-- <div class="m-phone"><strong><?= $m->to; ?></strong></div> -->
						<div class="m-message"><?= $m->type == 'hsm' ? "[SOFTNET_WELCOME_MESSAGE]" : $m->content->text; ?></div>
						<div class="m-date"><?= date('F j, Y h:i:s a', strtotime($m->createdDatetime)); ?></div>
						<div class="m-status <?= $m->status; ?>"></div>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php endif; ?>

	<form action="" method="POST">
		<p>
			<label>Enter Message</label><br>
			<textarea cols="60" rows="5" name="reply"><?= $reply ?></textarea>
		</p>
		<p>
			<button type="submit">Send</button>
		</p>
	</form>
</body>
</html>