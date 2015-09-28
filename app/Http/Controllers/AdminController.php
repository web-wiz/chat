<?php 
namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Message;

class AdminController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('admin');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getHome()
	{
//		$messages = User::find(1)->to_messages()->first()->message;
//		$user	= Message::find(2)->from_user->id;
		// chat config
		$chat_port = \Request::input("p");
		$chat_port = $chat_port ?: 9090;

		// user data
		$user = Auth::user();
		
		// users list
		$users_list = User::where( 'roles', '=', 1 )
		->get();
		
		// get messages
		$messages = array();
		foreach($users_list as $one)
		{
			foreach( $users_list as $second )
			{
				if($one->id != $second->id && $one->id < $second->id)
				{
					$messages[$one->id][$second->id] = Message::where(function($query) use ($second){
						$query->where('from_id', '=', $second->id)
						->orWhere( 'to_id', '=', $second->id );
					})
					->where(function($query) use($one){
						$query->where('from_id', '=', $one->id)
						->orWhere( 'to_id', '=', $one->id );
					})
					->orderBy('created_at')
					->get();
				}
			}
		}		
		
		return view('admin.home', compact("chat_port", "user", "users_list", "messages"));
	}

}
