<?php
namespace App\Http\Middleware\SystemAccess;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class RedirectIfNotSystemAdmin
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
		// dd($request->user()->isAdmin());
		if ( !$request->user()->isAdmin() ) {
			return redirect()->back();
		}
		return $next($request);
	}
}
