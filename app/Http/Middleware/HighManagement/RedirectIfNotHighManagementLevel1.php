<?php
namespace App\Http\Middleware\HighManagement;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// load string helper if somehow user not passing an array
use Illuminate\Support\Str;


class RedirectIfNotHighManagementLevel1
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
		// dd($request->user()->isHighManagementlvl1([1]));

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
		// $deptP = $request->user()->belongstostaff()->where('div_id', $hmu)->first();
		$deptHM = $request->user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
		// dd($deptHM);

		if ($dept == 'NULL' || $dept == 'null') {
			if ( !(in_array($userH, $hmu) || $request->user()->isAdmin()) )
			{
				return redirect()->back();
			}
		}
		else
		{
			if (Str::contains($dept, '|'))									// $dept got more than 1 dept ( string '|' )
			{
				$hmdept = explode("|", $dept);								// convert $hm to array
				// dd($hmdept);

				if( !( (in_array($deptHM->id, $hmdept) && in_array($userH, $hmu)) || $request->user()->isAdmin()) )
				{
					return redirect()->back();
				}
			}
			else															// $dept got only 1 dept ( no string '|' )
			{
				if ( !( ($deptHM->id == $dept && in_array($userH, $hmu)) || $request->user()->isAdmin() ) )
				{
					return redirect()->back();
				}
			}
		}
		return $next($request);
	}
}
