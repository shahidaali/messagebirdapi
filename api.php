<?php
header('Content-Type: application/json');

require_once __DIR__.'/init.php';
$messageBird = new \MessageBird\Client($API_KEY);

$request = $_REQUEST;

$action = isset($request['action']) ? $request['action'] : "";
$api_passcode = isset($request['api_passcode']) ? $request['api_passcode'] : "";

$response = [
	'status' => 'error',
	'message' => 'Nothing to process',
	'data' => []
];

if($api_passcode == "" || $api_passcode != API_PASSCODE) {
	$response['message'] = "Invalid api passcode";
	echo json_encode($response);
	exit();
}

if( $action == "get_conversations" ) {
	$optionalParameters = array(
	    'limit' => 20,
	    'offset' => 0,
	    'status' => 'active',
	    'include' => 'content',
	);

	try {
	    $conversations = $messageBird->conversations->getList($optionalParameters);
	    $response['message'] = "Conversations";
	    $response['status'] = 'success';

	    $conversation_items = [];
	    if($conversations->items) {
	    	foreach ($conversations->items as $key => $conversation) {
	    		$db->select('conversations', ['conversation_id' => $conversation->id], null, 'id DESC');
				$item = $db->row();

				if( empty($item->id) ) {
					$db->insert(
						'conversations', [
							'last_used' => date('Y-m-d H:i:s'),
							'conversation_id' => $conversation->id,
							'total_messages' => 0,
							'conversation' => json_encode($conversation),
							'created' => date('Y-m-d H:i:s')
						]
					);
					$conversation->new_messages = $conversation->messages->totalCount;
				}
				else {
					$conversation->new_messages = $conversation->messages->totalCount - $item->total_messages;
				}

	    		$conversation_items[] = $conversation;
	    	}
	    }

	    $response['data']['conversations'] = $conversation_items;
	} catch (\Exception $e) {
	    $response['message'] = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
	}	
}

if( $action == "get_messages" ) {
	if(!empty($request['conversation_id'])) {
		try {
		    $messages = $messageBird->conversationMessages->getList($request['conversation_id']);
		    $response['data']['messages'] = array_reverse($messages->items);
		    $response['message'] = "Messages";
		    $response['status'] = 'success';

		    $optionalParameters = array(
			    'include' => 'content',
			);

		    $db->update(
				'conversations', [
					// fields to be updated
					'total_messages' => $request['total_count'] ?? 0,
					'last_used' => date('Y-m-d H:i:s')
				], [
					// 'WHERE' clause
					'conversation_id' => $request['conversation_id']
				]
			);
		} catch (\Exception $e) {
		    $response['message'] = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
		}	
	} else {
		$response['message'] = "Conversation id is missing";
	}
}

if( $action == "post_message" ) {
	$error = "";

	if(empty($request['reply']) && empty($request['media'])) {
		$error = "Please enter message."; 
	}
	if(empty($request['phone'])) {
		$error = "Phone no is missing."; 
	}
	if(empty($request['conversation_id'])) {
		$error = "Conversation id is missing."; 
	}

	if(empty($error)) {
		$content = new \MessageBird\Objects\Conversation\Content();
		$message = new \MessageBird\Objects\Conversation\Message();

		if(!empty($request['media'])) {
			$content->image = array(
			    'url' => $request['media']
			);

			$message->type = \MessageBird\Objects\Conversation\Content::TYPE_IMAGE;
		} 
		else {
			$message->type = \MessageBird\Objects\Conversation\Content::TYPE_TEXT;
		}

		$content->text = $request['reply'];

		$message->channelId = CHANNEL_ID;
		$message->content = $content;
		$message->to = $request['phone'];

		try {
		    $conversation = $messageBird->conversationMessages->create(
		        $request['conversation_id'],
		        $message
		    );

		    $response['data']['conversation'] = $conversation;
		    $response['message'] = "Message sent.";
		    $response['status'] = 'success';

		} catch (\Exception $e) {
		    $response['message'] = sprintf("API ERROR: %s: %s", get_class($e), $e->getMessage());
		}	
	} else {
		$response['message'] = $error;
	}
}

header('Content-Type: application/json');
echo json_encode($response);
exit();