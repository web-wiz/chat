<?php namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
//use Illuminate\Session\SessionManager;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\Crypt;
//use Illuminate\Support\Facades\Auth;
use Auth;
use Config;
use Crypt;
use App\User;
use App\Message;
use Illuminate\Session\SessionManager;

class Chat implements MessageComponentInterface 
{

	protected $clients;

	public function __construct() 
	{
		$this->clients = new \SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn) 
	{
		// Store the new connection to send messages to later
		$this->clients->attach($conn);

		// Create a new session handler for this client
		$session = (new SessionManager(app()))->driver();
		// Get the cookies
		$cookies = $conn->WebSocket->request->getCookies();
		// Get the laravel's one
		$laravelCookie = urldecode($cookies[Config::get('session.cookie')]);
		// get the user session id from it
		$idSession = Crypt::decrypt($laravelCookie);
		// Set the session id to the session handler
		$session->setId($idSession);
		// Bind the session handler to the client connection
		$conn->session = $session;
		$conn->session->start();

		// online statuses
		$user_id = $conn->session->get(Auth::getName());
		if($user_id) // user is logged via http session
		{
			$user = User::find($user_id);
			if($user) // user found
			{
				$conn->user_id = $user_id;
				$conn->name = strip_tags( $user->name );  /** @todo must to replace by HTMLPurifier for full XSS-prevention **/
				$conn->is_admin = $user->roles == 2 ? true : false;

				$online_users = array();
				foreach ($this->clients as $client) 
				{
					if ($client !== $conn) 
					{
						// report online users that current user is online
						if( $user->roles != 2 ) // current user is not admin
						{
							$client->send( json_encode(array( 
								'type'		=> 'status',
								'user_id'	=> $user_id,
								'user_name'	=> $user->name,
								'message'	=> 1 
							)));
						}

						// gather online users id for current user
						$client->session->start();
						$id = $client->session->get(Auth::getName());
						$online_users[] = $id;
					}
				}
				// sending online users to current user
				$conn->send( json_encode(array( 
					'type'		=> 'statuses',
					'user_ids'	=> $online_users,
				)));

				// log message
				echo "New connection! ResourceID ({$conn->resourceId} for user {$user->name})\n";
			}
			else // user not found
			{
				echo "User {$user_id} not found\n";
				$conn->close();
			}
		}
		else // user is not logged via http session
		{
			echo "ResourceID ({$conn->resourceId}) is not logged via an http session.\n";
			$conn->close();
		}
	}

	public function onMessage(ConnectionInterface $conn, $msg) 
	{
		$conn->session->start();
		$user_id = $conn->session->get(Auth::getName());
		if($user_id) // user is logged via http session
		{
			$user = User::find($user_id);
			if($user) // user found
			{
				$data = json_decode( $msg );

				if( $user->roles != 2 ) // current user is not admin
				{
					if( $data->to_id != $conn->user_id ) // not self message
					{
						$to_user = User::find( $data->to_id );
						if( $to_user )
						{
							// put message to database
							$message = new Message;
							$message->from_id		= $conn->user_id;
							$message->to_id			= $data->to_id;
							$message->message		= $data->message;
							$message->created_at	= time();
							$message->save();

							$admin = false;
							foreach($this->clients as $client) 
							{
								$admin = $client->is_admin ? $client : $admin;
								 
								if($client->user_id == $data->to_id) 
								{
									$client->send(json_encode(array(
										'type'		=> 'message',
										'from_id'	=> $conn->user_id,
										'from_name'	=> $conn->name,
										'message_id'	=> $message->id,
										'message'	=> strip_tags($data->message) /** @todo must to replace by HTMLPurifier for full XSS-prevention **/
									)));
								}
							}
							// self message
							$conn->send(json_encode(array(
								'type'		=> 'self',
								'message_id'	=> $message->id,
								'message'	=> strip_tags($data->message) /** @todo must to replace by HTMLPurifier for full XSS-prevention **/
							)));
							// send message to admin
							if($admin)
							{
								$admin->send(json_encode(array(
									'type'		=> 'message',
									'from_id'	=> $conn->user_id,
									'to_id'		=> $data->to_id,
									'from_name'	=> $conn->name,
									'to_name'	=> $to_user->name,
									'message_id'	=> $message->id,
									'message'	=> strip_tags($data->message) /** @todo must to replace by HTMLPurifier for full XSS-prevention **/
								)));
							}
							
							echo "Message from {$user->name}\n";
						}
						else
						{
							echo "Message from {$user->name}: user for this message not found\n";
						}
					}
					else
					{
						echo "Self message: {$user->name}";
					}
				}
				else // current user is admin
				{
					$client_ids = explode( '-', $data->user_ids );
					
					// delete from db
					Message::destroy($data->message_ids);

					// delete from users chat
					foreach($this->clients as $client) 
					{
						if(in_array($client->user_id, $client_ids)) 
						{
							$client->send(json_encode(array(
								'type'		=> 'delete',
//								'from_id'	=> $client_ids[0] == $client->user_id ? $client_ids[1] : $client_ids[0],
								'message_ids'	=> $data->message_ids,
							)));
						}
					}
					
					echo "Delete messages " . implode(', ', $data->message_ids) . "\n";
				}
			}
			else // user not found
			{
				echo "User {$user_id} not found\n";
				$conn->close();
			}
		}	
		else // user is not logged via http session
		{
			echo "ResourceID ({$conn->resourceId}) is not logged via an http session.\n";
			$conn->close();
		}
	}

	public function onClose(ConnectionInterface $conn) 
	{
		$conn->session->start();

		// online statuses
		$username = 'not authorized';
		$user_id = $conn->session->get(Auth::getName());
		if($user_id) // user is logged via http session
		{
			$user = User::find($user_id);
			if($user) // user found
			{
				$username = $user->name;
				if( $user->roles != 2 ) // current user is not admin
				{
					foreach ($this->clients as $client) 
					{
						if ($client !== $conn) 
						{
							// report online users that current user is offline
							$client->send( json_encode(array( 
								'type'		=> 'status',
								'user_id'	=> $user_id,
								'message'	=> 0 
							)));
						}
					}
				}
			}
			else // user not found
			{
				$username = 'not found';
			}
		}

		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);

		// log message
		echo "Connection {$conn->resourceId} ({$username}) has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) 
	{
		echo "An error has occurred (resourceID {$conn->resourceId}): {$e->getMessage()}\n";

		$conn->close();
	}

}