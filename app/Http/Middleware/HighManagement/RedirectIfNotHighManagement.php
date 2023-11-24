<?php
namespace App\Http\Middleware\HighManagement;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// load string helper if somehow user not passing an array
use Illuminate\Support\Str;


class RedirectIfNotHighManagement
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next, $highManagement, $dept): Response
	{
		// make sure its high management
		// dd($highManagement, $dept);

		if (Str::contains($highManagement, '|'))
		{
			$hms = explode("|", $highManagement);									// convert $hm to array
			foreach ($hms as $hm1) {
				$hmu[] = $hm1;
			}
		}
		else
		{
			$hmu[] = $highManagement;
		}
		// dd($hmu);
		// dd(is_array($hmu));

		$userH = $request->user()->belongstostaff->div_id;
		// dd($userH);
		$deptP = $request->user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();

		if ($dept == 'NULL' || $dept == 'null') {
			foreach($hmu as $hmudi) {
				if ( !($userH == $hmudi || $request->user()->isAdmin()) ) {
					dd('no dept');
					return redirect()->back();
				}
			}
		}
		else
		{
			if (Str::contains($dept, '|'))									// $dept got more than 1 dept ( string '|' )
			{
				$hmdept = explode("|", $dept);								// convert $hm to array
				// dd($hmdept);
				foreach ($hmdept as $hmdept1)
				{
					$hmdeptu[] += $hmdept1;
				}
				if(!(in_array($deptP->id, $hmdeptu) || $request->user()->isAdmin()))
				{
					return redirect()->back();
				}
			}
			else															// $dept got only 1 dept ( no string '|' )
			{
				if ( !($deptP->id == $dept || $request->user()->isAdmin()) )
				{
					return redirect()->back();
				}
			}
		}
		return $next($request);
	}
}
