<?php 
namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Message;

class HomeController extends Controller {

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
		$this->middleware('user');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		// chat config
		$chat_port = \Request::input("p");
		$chat_port = $chat_port ?: 9090;
		
		// user data
		$user = Auth::user();
		
		// users list
		$users_list = User::where( 'roles', '=', 1 )
		->where( 'id', '<>', $user->id )
		->get();
		
		// get messages
		$messages = array();
		foreach($users_list as $one)
		{
			$messages[$one->id] = Message::where(function($query) use ($user){
				$query->where('from_id', '=', $user->id)
				->orWhere( 'to_id', '=', $user->id );
			})
			->where(function($query) use($one){
				$query->where('from_id', '=', $one->id)
				->orWhere( 'to_id', '=', $one->id );
			})
			->orderBy('created_at')
			->get();
		}		
		
		return view('home', compact("chat_port", "user", "users_list", "messages"));
	}

}
