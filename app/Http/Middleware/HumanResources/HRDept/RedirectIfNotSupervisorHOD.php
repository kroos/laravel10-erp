<?php
namespace App\Http\Middleware\HumanResources\HRDept;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class RedirectIfNotSupervisorHOD
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
		// dd($request->user()->isSupervisorHOD());
		if ( !$request->user()->isSupervisorHOD() ) {
			return redirect()->back();
		}
		return $next($request);
	}
}
