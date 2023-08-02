<?php

namespace App\Http\Controllers\HumanResources\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

// load validation
use App\Http\Requests\HumanResources\Leave\HRLeaveRequestStore;

// load models
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\DepartmentPivot;

// load array helper
use Illuminate\Support\Arr;

// load custom helper
use App\Helpers\UnavailableDate;
use Illuminate\Support\Arr;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;

use Session;

class HRLeaveController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('leaveaccess', ['only' => ['show', 'edit', 'update']]);
	}
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return view('humanresources.leave.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view('humanresources.leave.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(HRLeaveRequestStore $request)//: RedirectResponse
	{
		return $request->all();
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// initial setup for create a leave
		$user = \Auth::user()->belongstostaff;								// for specific user
		$daStart = Carbon::parse($request->date_time_start);				// date start : for manipulation


		// in time off, there only date_time_start so...
		if( empty( $request->date_time_end ) ) {
			$request->date_time_end = $request->date_time_start;
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// if a user select more than 1 day and setting double date is on, we need to count the remaining day that is not overlapping
		$blockdate = UnavailableDate::blockDate(\Auth::user()->belongstostaff->id);
		$period = \Carbon\CarbonPeriod::create($request->date_time_start, '1 days', $request->date_time_end);
		$lea = [];
		foreach ($period as $value) {
			$lea[] = Carbon::parse($value)->format('Y-m-d');
		}
		$totalday = count($lea);

		$leav = [];
		foreach ($blockdate as $val1) {
			$va1 = Carbon::parse($val1);
			foreach ($period as $val2) {
				if(Carbon::parse($val1)->equalTo(Carbon::parse($val2))){
					$leav[] = Carbon::parse($val1)->format('Y-m-d');
				}
			}
		}
		$filtered = array_diff($lea, $leav);			// get all the dates that is not in $blockdate
		$totaldayfiltered = count($filtered);			// total days

		// return [$lea, $leav, $filtered];

		if($totalday == $totaldayfiltered){
			$noOverlap = true;							// meaning we CAN take $request->date_time_end $request->date_time_start as is to be insert in database
			$dateStart = $request->date_time_start;
			$dateEnd  = $request->date_time_end;
		} else {
			$noOverlap = false;							// meaning we CANT take $request->date_time_end $request->date_time_start as is to be insert in database, instead we need to separate it row by row to be inserted into database.
			// we need to loop entire date which is available 1 by 1
			$date = [];
			foreach($filtered as $d){
				$date[] = ['date_time_start' => $d, 'date_time_end' => $d];
			}
		}
		$c = count($date);
		// return $c;

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// generate code for approver
		$code = mt_rand(100000,999999);

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// check if total leave day equals to or lower that entitlement based on year leave date
		// $daStart;
		// $entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
		// $entitlement = $user->hasmanyleaveentitlement()->where('year', 2024)->first();

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// start insert into DB
		// AL & EL-AL
		if($request->leave_type_id == 1 || $request->leave_type_id == 5) {
			// check entitlement
			$entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
			if(!$entitlement) {								// kick him out if there is no entitlement been configured for entitlement
			    Session::flash('flash_message', 'Please check your entitlement based on the date leave you applied');
			    return redirect()->back();
			}

			// check date as above
			if ($noOverlap) {				// true: date choose not overlapping date with unavailable date
				$l = $user->hasmanyleave()->insert($request->only(Arr::add(['leave_type_id', 'reason', 'date_time_start', 'date_time_end'], 'verify_code', $code)));
			} else {						// false: date choose overlapping date with unavailable date
				
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// UPL & EL-UPL & MC-UPL
		if($request->leave_type_id == 3 || $request->leave_type_id == 6 || $request->leave_type_id == 11) {
			// solve date part

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// MC
		if($request->leave_type_id == 2) {
			// solve date part

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// NRL & EL-NRL
		if($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// solve date part

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// ML
		if($request->leave_type_id == 7) {

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// TF
		if($request->leave_type_id == 9) {

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// S-UPL
		if($request->leave_type_id == 12) {
			// solve date part

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// determining apporover (supervisor, HOD, director, HR)
		// determine $user branch/location
		$branch = $user->belongstomanydepartment->where('pivot.main', 1)->first()->branch_id;

		// determine $user category
		$category = $user->belongstomanydepartment->where('pivot.main', 1)->first()->category_id;

		//////////////////////////////////////////////////////////////////////////////
		// this section need to be look once there is more branch and category
		if($user->belongstoleaveapprovalflow->supervisor_approval == 1){				//supervisor: div_id = 4
			// search supervisor div_id = 4
			if ($branch == 1){							// IPMA A
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 4]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 4]])->get();
					}
				}
			} else {									// IPMA B
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 4]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 4]])->get();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////
		if($user->belongstoleaveapprovalflow->hod_approval == 1){
			// search hod div_id = 4
			if ($branch == 1){							// IPMA A
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 1]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 1]])->get();
					}
				}
			} else {									// IPMA B
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 1]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 1]])->get();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////
		if($user->belongstoleaveapprovalflow->director_approval == 1){
			// search director div_id = 2
			if ($branch == 1){							// IPMA A
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 2]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 2]])->get();
					}
				}
			} else {									// IPMA B
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 2]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 2]])->get();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////
		if($user->belongstoleaveapprovalflow->hr_approval == 1){
			// search hr div_id = 3
			if ($branch == 1){							// IPMA A
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 3]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 1],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 3]])->get();
					}
				}
			} else {									// IPMA B
				if ($category == 1) {					// Office
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 1]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 3]])->get();
					}
				} else {								// Production
					$r = DepartmentPivot::where([['branch_id', 2],['category_id', 2]])->get();
					$t = [];
					foreach($r as $x){
						$t[] = $x->belongstomanystaff()->where([['active', 1],['div_id', 3]])->get();
					}
				}
			}
		}
		//end section
		//////////////////////////////////////////////////////////////////////////////
		// return $t;
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



























	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeave $leave)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeave $leave)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(HRLeaveRequestStore $request, HRLeave $leave)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $leave)
	{
		//
	}
}
