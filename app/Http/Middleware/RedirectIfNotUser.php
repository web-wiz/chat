<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

class RedirectIfNotUser {

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
		if($request->user()->roles != 1)
		{
			return new RedirectResponse(url('/admin/home'));
		}

		return $next($request);
	}

}
