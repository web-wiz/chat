@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Chat</div>

				<div class="panel-body">
					<div id="chat-messages" class="chat-messages">
						<div class="chat-messages-box common-box active"></div>
						@foreach( $users_list as $one )
							@foreach( $users_list as $second )
								@if($one->id < $second->id)
									@if(isset($messages[$one->id][$second->id]) && count($messages[$one->id][$second->id]) > 0)
										<div id="messages-box-{{ $one->id }}-{{ $second->id }}" class="chat-messages-box" data-id="{{ $one->id }}-{{ $second->id }}">
											@foreach($messages[$one->id][$second->id] as $msg)
												<div class="chat-message {{ $msg->to_id == $one->id ? 'message-from-user' : 'message-to-user' }}">{{ $msg->from_id == $one->id ? $one->name : $second->name }}: {{{ $msg->message }}}</div>
											@endforeach
										</div>
									@else
										<div id="messages-box-{{ $one->id }}-{{ $second->id }}" class="chat-messages-box empty-messages-box" data-id="{{ $one->id }}-{{ $second->id }}"></div>
									@endif
								@endif
							@endforeach
						@endforeach
					</div>
					<ul id="chat-users" class="chat-users">
						@foreach( $users_list as $one )
							<li id="user-{{ $one->id }}" class="chat-user" data-name="{{ $one->name }}" data-id="{{ $one->id }}">
								<span class="chat-user-name">{{{ $one->name }}}</span>
								<span class="chat-user-status">Offline</span>
								<span class="new-messages">0</span>
								<div class="clearfix"></div>
								
								<ul class="sub-users-list">
									@foreach ($users_list as $second)
										@if ($one->id != $second->id)
											<li id="sub-user-{{ $one->id }}-{{ $second->id }}" class="sub-user" data-box-id="{{ $one->id < $second->id ? $one->id . '-' . $second->id : $second->id . '-' . $one->id }}" data-one-id="{{ $one->id }}" data-second-id="{{ $second->id }}">{{{ $second->name }}}</li>
										@endif
									@endforeach
								</ul>
							</li>
						@endforeach
					</ul>
					<div class="clearfix"></div>
					<div class="message-form">
						<span class="message-label">Write here</span>
						<input id="message-input" class="message-input" name="chatText" type="text">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// config
	var userName	= "{{ $user->name }}";
	var userId		= "{{ $user->id }}";
	var port	= "{{ $chat_port }}";
	var uri		= "{{ explode(':', str_replace('http://', '', str_replace('https://', '', App::make('url')->to('/'))))[0] }}";
	port = port.length == 0 ? '9090' : port;

	// choosing user event
	$('#chat-users').on( 'click', '.sub-user', function(){
		var box_id = $(this).data('box-id');

		// change sub-users
		$('#chat-users').find('.sub-user').removeClass('active');
		$(this).addClass('active');
		$('#sub-user-' + $(this).data('second-id') + '-' + $(this).data('one-id')).addClass('active');
		// remove new-messages
//		$('#chat-users').find('.new-messages').removeClass('active')
//		.text('0');
		changeMessagesBox(box_id);
	});
	
	// change message's box
	function changeMessagesBox(id)
	{
		var filter = id ? '#messages-box-' + id : '.common-box';
		
		$('#chat-messages').children('.chat-messages-box').removeClass('active')
		.filter(filter).addClass('active');

		$("#chat-messages").scrollTop($("#chat-messages")[0].scrollHeight);
	}
	
	// clear active message's box
	function clearMessageBox()
	{
		$("#chat-messages").children('.chat-messages-box.active').html('');
	}
	
	// put message into chatbox
	function addMessageToChatBox(message, m_class)
	{
		m_class = m_class || '';
		
		$("#chat-messages").children('.chat-messages-box.active').append('<div class="chat-message ' + m_class + '">' + message + '</div>');
		$("#chat-messages").scrollTop($("#chat-messages")[0].scrollHeight);
	}
	
	// new message notification
	function newMessageNotification(id)
	{
		var count = $('#user-' + id).find('.new-messages').text() || 0;
		count++;
		$('#user-' + id).find('.new-messages').text(count)
		.addClass('active');
	}

	$(document).ready(function(){

		// Open WS connection
		var conn = new WebSocket('ws://'+uri+':'+port);

		// connection opened
		conn.onopen = function(e)
		{
	        clearMessageBox();
		    addMessageToChatBox("Choose chat list...");
		};

		// connection closed or cannot be established
		conn.onclose = function (event) {
	        var reason;

	        if (event.code == 1000)
	            reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
	        else if(event.code == 1001)
	            reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
	        else if(event.code == 1002)
	            reason = "An endpoint is terminating the connection due to a protocol error";
	        else if(event.code == 1003)
	            reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
	        else if(event.code == 1004)
	            reason = "Reserved. The specific meaning might be defined in the future.";
	        else if(event.code == 1005)
	            reason = "No status code was actually present.";
	        else if(event.code == 1006)
	           reason = "Abnormal error, e.g., without sending or receiving a Close control frame";
	        else if(event.code == 1007)
	            reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
	        else if(event.code == 1008)
	            reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
	        else if(event.code == 1009)
	           reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
	        else if(event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
	            reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
	        else if(event.code == 1011)
	            reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
	        else if(event.code == 1015)
	            reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
	        else
	            reason = "Unknown reason";

	        if( event.code != 1000 && event.code != 1001 )
	        {
	        	changeMessagesBox();
	        	clearMessageBox();
				addMessageToChatBox("Chat unavailable. Reason: " + reason, 'bg-danger');
	        }
	    };

	    // message from server
		conn.onmessage = function(e)
		{
			var data = JSON.parse(e.data);
			
			if(data.type == 'statuses') // checking statuses when logged in
			{
				for( key in data.user_ids )
				{
					$('#user-' + data.user_ids[key]).addClass( 'online' )
					.find('.chat-user-status').text('Online')
				}
			}
			else if(data.type == 'status') // change other user status when he logging in or logging out 
			{
				if( data.message ) // user logged in
				{
					if($('#user-' + data.user_id).length == 0) // new registred user
					{
						// append new user
						$('#chat-users').append('<li id="user-' + data.user_id + '" class="chat-user online" data-name="' + data.user_name + '" data-id="' + data.user_id + '"><span class="chat-user-name">' + data.user_name + '</span><span class="chat-user-status">Online</span><span class="new-messages">0</span><div class="clearfix"></div></li>');
						// append box for this user 
						$('#chat-messages').append('<div id="messages-box-' + data.user_id + '" class="chat-messages-box empty-messages-box" data-id="' + data.user_id + '">');
					}
					else // new user logged in user
					{
						$('#user-' + data.user_id).addClass( 'online' )
						.find('.chat-user-status').text('Online');
					}
				}
				else // user logged out
				{
					$('#user-' + data.user_id).removeClass( 'online' )
					.find('.chat-user-status').text('Offline');
				}
			}
			else if( data.type = 'message' )
			{
				var box_id = data.from_id < data.to_id ? data.from_id + '-' + data.to_id : data.to_id + '-' + data.from_id;
				var msg_class = data.from_id < data.to_id ? 'message-to-user' : 'message-from-user';

				if($('#messages-box-' + box_id).hasClass('active'))
				{
	    			addMessageToChatBox(data.from_name + ': ' + data.message, msg_class);
				}
				else
				{
					// make a notification about new messages
//					newMessageNotification( data.from_id );
					$( '#messages-box-' + box_id ).append('<div class="chat-message ' + msg_class + '">' + data.from_name + ': ' + data.message + '</div>');
				}
			}
		};

		// send message
		$('#message-input').keyup(function(e){
			if (e.keyCode == 13) // enter was pressed
			{
				var to_id	= $('#chat-messages').children('.chat-messages-box.active').data('id');
				var message = $(this).val();
				
				if( ! $('#chat-messages').children('.chat-messages-box.active').hasClass('common-box'))
				{
					// clear box and input
					$("#chat-messages").children('.chat-messages-box.active').removeClass('empty-messages-box');
					$(this).val("");

					addMessageToChatBox("Me: " + message, 'message-to-user');

					conn.send(JSON.stringify(
						{
							to_id : to_id,
							message: message 
						}
					));
				}
			}
		});
	});
</script>
@endsection
