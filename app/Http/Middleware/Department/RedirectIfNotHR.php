<?php
namespace App\Http\Middleware\Department;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class RedirectIfNotHR
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): Response
	{
		// dd($request->user()->isHR());
		if ( !$request->user()->isHR() ) {
			return redirect()->back();
		}
		return $next($request);
	}
}
