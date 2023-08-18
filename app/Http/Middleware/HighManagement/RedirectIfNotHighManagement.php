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
		$hmu = [];
		if (Str::contains($highManagement, '|')) {
			$hms = explode("|", $highManagement);									// convert $hm to array
			foreach ($hms as $hm1) {
				$hmu[] += $hm1;
			}
		} else {
			$hmu = [$highManagement];
		}
		$deptP = $request->user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();

		// dd( [(true || false), (true && false)] );
		// dd( ($request->user()->isHighManagement($hmu) && $deptP->id == $dept) );

		if ($dept === 'NULL') {
			if (!$request->user()->isHighManagement($hmu)) {
				return redirect()->back();
			}
		}

		if ($dept != 'NULL') {
			if( !($request->user()->isHighManagement($hmu) && $deptP->id == $dept) ) {
				return redirect()->back();
			}
		}

		if(!$request->user()->isAdmin()) {
			return redirect()->back();
		}
		return $next($request);
	}
}
