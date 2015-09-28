<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

class RedirectIfNotAdmin {

	/**
	 * Create a new filter instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
//		
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if($request->user()->roles != 2)
		{
			return new RedirectResponse(url('/home'));
		}

		return $next($request);
	}

}
