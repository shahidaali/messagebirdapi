<?php 
require_once __DIR__.'/init.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>SOFTNET Helpline</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="public/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="public/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="public/css/style.css">
	<link rel="stylesheet" type="text/css" href="public/css/responsive.css">

	<script type="text/javascript">
		GLOBAL_CONFIG = {};
		GLOBAL_CONFIG.API_PASSCODE = "<?php echo API_PASSCODE;  ?>";
	</script>
</head>
<body>
	<div id="vueApp" style="display: none;">
		<global-messages></global-messages>
		<div class="chat-panel">
			<conversations inline-template>
				<div class="conversations-wrap" :class="{loader: isLoading}">
					<div class="c-title">
						<h3>Conversations</h3>
					</div>
					<div class="c-search">
						<form method="post">
							<i class="fa fa-search"></i>
							<input type="text" v-model="search" placeholder="Search or start new chat">
						</form>
					</div>
					<ul class="conversations">
						<li v-for="conversation in filteredConversations" v-on:click="selectConversation(conversation)" :class="{selected: selectedConversation && conversation.id == selectedConversation.id}">
							<span class="c-pic"></span>
							<span class="c-phone">{{ conversation.contact.firstName ? conversation.contact.firstName + " " + conversation.contact.lastName : conversation.contact.msisdn }}</span>
							<span class="c-phone-alt" v-if="conversation.contact.firstName">{{ conversation.contact.msisdn }}</span>
							<span class="c-date">{{ moment(conversation.lastReceivedDatetime).format('DD/MM/YYYY') }}</span>
							<span class="c-new" v-if="conversation.new_messages > 0">{{ conversation.new_messages }}</span>
						</li>
					</ul>
				</div>
			</conversations>
			<chat-panel  inline-template>
				<div class="chat-wrap" :class="{loader: isLoading}">
					<div class="conversation-message" v-if="!conversation">
						<p>Select conversation to start chatting.</p>
					</div>
					<div class="chat-window" v-else>
						<chat-profile inline-template
							:conversation="conversation"
							>
							<div class="chat-profile">
								<span class="p-back">
									<a href="#" v-on:click.prevent="goBack"><i class="fa fa-arrow-left"></i></a>
								</span>
								<span class="p-pic"></span>
								<span class="p-phone">{{ conversation.contact.firstName ? conversation.contact.firstName + " " + conversation.contact.lastName : conversation.contact.msisdn }}</span>
								<span class="p-phone-alt" v-if="conversation.contact.firstName">{{ conversation.contact.msisdn }}</span>
							</div>
						</chat-profile>
						<ul class="inbox" v-if="messages.length">
							<li  v-for="m in messages" :class="`inbox-item ${m.direction}`">
								<div class="m-message" :class="`message-${m.type}`">
									<div class="m-message-content" v-if="m.type == 'hsm'">{{ "[SOFTNET_WELCOME_MESSAGE]"  }}</div>
									<div class="m-message-content" v-if="m.type == 'text'">{{ m.content.text }}</div>
									<div class="m-message-content" v-if="m.type == 'image'"><img :src="m.content.image.url" v-on:click="showFullImage(m.content.image.url)"></div>
									<div class="m-message-content" v-if="m.type == 'file'"><a :href="m.content.file.url" download=""><i class="fa fa-file"></i> Download File</a></div>
									<div class="m-message-content" v-if="m.type == 'video'"><a :href="m.content.video.url" download=""><i class="fa fa-video"></i> Download Video</a></div>
								</div>
								<div class="m-date">{{ moment(m.createdDatetime).format('DD/MM/YYYY h:m a') }}</div>
								<div :class="`m-status ${m.status}`"></div>
							</li>	
						</ul>
						<div class="image-popup" v-if="full_image">
							<div class="ip-title">
								<a href="#" class="ip-close" v-on:click.prevent="closeImagePopup"><i class="fa fa-times"></i></a>
								<span>Image</span>
							</div>
							<div class="ip-media">
								<img :src="full_image" alt="Media">
							</div>
						</div>
						<create-message inline-template
							:conversation="conversation"
							>
							<div>
								<div class="media-preview" v-if="media" :class="{loader: isLoading}">
									<div class="mp-title">
										<a href="#" class="mp-close" v-on:click.prevent="removeImage"><i class="fa fa-times"></i></a>
										<span>Preview</span>
									</div>
									<div class="mp-media">
										<img :src="media" alt="Media" v-if="media_type == 'image'">
									</div>
									<div class="mp-caption">
										<input type="text" v-model="reply" placeholder="Type a message">
									</div>
									<div class="text-right">
										<button type="button" class="mp-send"><i class="fa fa-paper-plane" v-on:click="postMessage"></i></button>
									</div>
								</div>
								<div class="reply-box" :class="{loader: isLoading}">
									<form v-on:submit.prevent="postMessage">
										<div class="select-media">
											<i class="fa fa-camera"></i>
											<input type="file" v-on:change="onFileChange($event)">
										</div>
										<input type="text" v-model="reply" placeholder="Type a message">
									</form>
								</div>
							</div>
						</create-message>
					</div>
				</div>
			</chat-panel>
		</div>
	</div>

	<script type="text/javascript" src="public/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="public/js/moment.min.js"></script>
	<script type="text/javascript" src="public/js/vue.js"></script>
	<script type="text/javascript" src="public/js/vue-components.js"></script>
</body>
</html>