<?php
require_once __DIR__.'/init.php';

$db->select('contacts', [], null, 'id DESC');
$contacts = $db->result();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Message Bird</title>
	<link rel="stylesheet" type="text/css" href="public/css/style.css">
</head>
<body>
	<h2>Contacts List</h2>
	<table width="100%" border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Phone</th>
			<th>Contact ID</th>
			<th>Message Received</th>
			<th>Received Time</th>
			<th>Replied</th>
			<th>Message Sent</th>
			<th>Sent Time</th>
			<th>Actions</th>
		</tr>
		<?php foreach ($contacts as $key => $contact) { ?>
			<tr>
				<td><?= $contact->id; ?></td>
				<td><?= $contact->first_name . " " . $contact->last_name; ?></td>
				<td><?= $contact->phone; ?></td>
				<td><?= $contact->contact_id; ?></td>
				<td><?= $contact->message_received; ?></td>
				<td><?= $contact->created; ?></td>
				<td><?= $contact->is_sent ? "Yes" : "No"; ?></td>
				<td><?= $contact->message_sent; ?></td>
				<td><?= $contact->sent_time; ?></td>
				<td><a href="inbox.php?id=<?= $contact->id; ?>" target="_blank">Inbox</a></td>
			</tr>
		<?php } ?>
	</table>
</body>
</html>