var Global_Response_Handler = {
  response : null,
  init : function( response ) {
    this.response = response;
    return this;
  },
  is_success : function() {
    return this.response.status == 'success';
  },
  is_error : function() {
    return this.response.status == 'error';
  },
  message : function() {
    return this.response.message;
  },
  status : function() {
    return this.response.status;
  },
  data : function() {
    return ( this.response.data !== undefined ) 
      ? this.response.data
      : {};
  },
  get_data : function(key, default_value) {
    return ( this.response.data[ key ] !== undefined ) 
      ? this.response.data[ key ]
      : default_value;
  },
  get_request : function(key, default_value) {
    return ( this.response.request[ key ] !== undefined ) 
      ? this.response.request[ key ]
      : default_value;
  }
};

Vue.component('conversations', {
  props: {
      fieldName: {
        type: String,
        default: ''
      },
  },
  data() {
    return {
      conversations: [],
      isLoading: true,
      selectedConversation: null,
      timeout: null,
      timelimit: 10000,
      search: '',
    }
  },
  computed: {
    filteredConversations: function() {
      var vm = this;

      if(vm.search == "") {
        return vm.conversations;
      }

      var search = vm.search.toLowerCase();

      var result = vm.conversations.filter(obj => {
        var str = (obj.contact.msisdn + " " + obj.contact.firstName + " " + obj.contact.lastName).toLowerCase();
        return str.indexOf(search.toLowerCase()) !== -1
      });

      return result;
    }
  },
  methods: {
    getConversations: function() {
      var vm = this;
      vm.isLoading = true;

      $.ajax({
        url: "api.php",
        method: 'GET',
        data: {
          api_passcode: GLOBAL_CONFIG.API_PASSCODE,
          action: 'get_conversations',
        },
        success: function(result){
          let response = Global_Response_Handler.init(result);
          if( response.is_success() ) {
            vm.conversations = response.get_data('conversations');
            EventBus.$emit('GLOBAL_MESSAGE', '', '');

            if(vm.selectedConversation) {
              for (var i = 0; i < vm.conversations.length; i++) {
                if(vm.conversations[i].id == vm.selectedConversation.id && vm.conversations[i].new_messages > 0) {
                  EventBus.$emit('LOAD_MESSAGES', vm.conversations[i]); 
                  vm.conversations[i].new_messages = 0;
                }
              }  
            }
            
          }
          else {
            EventBus.$emit('GLOBAL_MESSAGE', response.message(), response.status(), 'LOAD_CONVERSATIONS', 'Reload <i class="fa fa-redo"></i>');
          }

          vm.timeout = setTimeout(function(){
            vm.getConversations();
          }, vm.timelimit);

          vm.isLoading = false;
        }
      });
    },
    selectConversation: function(conversation) {
      EventBus.$emit('START_CONVERSATION', conversation);
      this.selectedConversation = conversation;
    },
    
  },
  mounted: function() {
    var vm = this;
    vm.getConversations();

    EventBus.$on('LOAD_CONVERSATIONS', function () {
      vm.getConversations();
    });
  }
});

Vue.component('chat-panel', {
  data() {
    return {
      messages: [],
      conversation: null,
      timeout: null,
      timelimit: 5000,
      isLoading: false,
      full_image: "",
    }
  },
  methods: {
    getMessages: function() {
      var vm = this;
      vm.isLoading = true;
      clearTimeout(vm.timeout);

      // Clear Selected Media
      EventBus.$emit('REMOVE_MEDIA');

      $.ajax({
        url: "api.php",
        method: 'GET',
        data: {
          api_passcode: GLOBAL_CONFIG.API_PASSCODE,
          action: 'get_messages',
          conversation_id: vm.conversation.id,
          total_count: vm.conversation.messages.totalCount
        },
        success: function(result){
          let response = Global_Response_Handler.init(result);
          if( response.is_success() ) {
            vm.messages = response.get_data('messages');

            // vm.timeout = setTimeout(function(){
            //   vm.getMessages();
            // }, vm.timelimit);

            vm.isLoading = false;

            setTimeout(function(){
              if($('.inbox').length) {
                $('.inbox').animate({
                    scrollTop: $('.inbox').prop("scrollHeight")
                }, 100);  
              }
            }, 100);
            EventBus.$emit('GLOBAL_MESSAGE', '', '');

            if($(window).width() <= 768) {
              $('body').addClass('messages-loaded');
            }
          }
          else {
            EventBus.$emit('GLOBAL_MESSAGE', response.message(), response.status(), 'LOAD_MESSAGES', 'Reload <i class="fa fa-redo"></i>');
          }
        }
      });
    },
    showFullImage(image) {
      this.full_image = image;
    },
    closeImagePopup() {
      this.full_image = "";
    }
  },
  mounted: function() {
    var vm = this;
    
    EventBus.$on('START_CONVERSATION', function (conversation) {
      vm.conversation = conversation;

      vm.getMessages();
    });

    EventBus.$on('LOAD_MESSAGES', function (conversation) {
      if(conversation) {
        vm.conversation = conversation;
      }

      vm.getMessages();
    });

    EventBus.$on('MESSAGE_POSTED', function (reply, conversation) {
      // vm.conversation = conversation;

      vm.getMessages();
    });

    EventBus.$on('MEDIA_SELECTED', function (media, media_type) {
      // vm.conversation = conversation;

      vm.media = media;
      vm.media_type = media_type;
    });
  }
});

Vue.component('create-message', {
  props: {
    conversation: {
      type: Object,
    }
  },
  data() {
    return {
      reply: "",
      media: "",
      media_type: "",
      updated_conversation: null,
      isLoading: false,
    }
  },
  methods: {
    postMessage: function() {
      var vm = this;

      vm.isLoading = true;

      $.ajax({
        url: "api.php",
        method: 'POST',
        data: {
          api_passcode: GLOBAL_CONFIG.API_PASSCODE,
          action: 'post_message',
          conversation_id: vm.conversation.id,
          phone: vm.conversation.contact.msisdn,
          reply: vm.reply,
          media: vm.media,
          media_type: vm.media_type
        },
        success: function(result){
          let response = Global_Response_Handler.init(result);
          if( response.is_success() ) {
            vm.updated_conversation = response.get_data('conversation');
            EventBus.$emit('MESSAGE_POSTED', vm.reply, vm.updated_conversation);
            EventBus.$emit('GLOBAL_MESSAGE', '', '');
            vm.reply = "";
            vm.media = "";

            vm.isLoading = false;
          }
          else {
            EventBus.$emit('GLOBAL_MESSAGE', response.message(), response.status(), 'POST_MESSAGE', 'Try Again <i class="fa fa-redo"></i>');
          }
        }
      });
    },
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0]);
    },
    createImage(file) {
      var image = new Image();
      var reader = new FileReader();
      var vm = this;

      reader.onload = (e) => {
        vm.media = e.target.result;
        vm.media_type = "image";

        EventBus.$emit('MEDIA_SELECTED', vm.media, vm.media_type);
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.media = '';
      this.media_type = '';
    }
  },
  mounted: function() {
    var vm = this;

    EventBus.$on('REMOVE_MEDIA', function () {
      vm.removeImage();
    });

    EventBus.$on('POST_MESSAGE', function () {
      vm.postMessage();
    });
  }
});

Vue.component('chat-profile', {
  props: {
    conversation: {
      type: Object,
    }
  },
  methods: {
    goBack() {
      $('body').removeClass('messages-loaded');
    }
  },
  mounted: function() {
    var vm = this;

  }
});

Vue.component('global-messages', {
  template: `<div><div v-if="message" :class="['global-message', type]">{{message}} <a href="#" v-if="action" v-html="action_text" v-on:click.prevent="doAction(action)"></a></div></div>`,
  data() {
    return {
      message: "",
      type: "error",
      action: "",
      action_text: ""
    }
  },
  methods: {
    resetMessages() {
      this.message = "";
      this.type = "error";
      this.action = "";
      this.action_text = "";
    },
    doAction(action) {
      EventBus.$emit(action);
      this.resetMessages();
    }
  },
  mounted: function() {
    var vm = this;

    EventBus.$on('GLOBAL_MESSAGE', function (message, type, action, action_text) {
      vm.message = message;
      vm.type = type;
      vm.action = action;
      vm.action_text = action_text;
    });
  }
});

const EventBus = new Vue();
Vue.prototype.moment = moment;

var app = new Vue({
  el: '#vueApp'
})
