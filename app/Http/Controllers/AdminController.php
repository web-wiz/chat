<?php 
namespace App\Http\Controllers;

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

		$chatPort = \Request::input("p");
		$chatPort = $chatPort ?: 9090;
		return view('admin.home', compact("chatPort"));
	}

}
