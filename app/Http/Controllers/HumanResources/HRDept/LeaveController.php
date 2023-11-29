<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeaveAmend;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeaveApprovalHOD;
use App\Models\HumanResources\HRLeaveApprovalDirector;
use App\Models\HumanResources\HRLeaveApprovalHR;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

use \App\Helpers\UnavailableDateTime;


class LeaveController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);		// all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);	// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.leave.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeave $hrleave): View
	{
		return view('humanresources.hrdept.leave.show', ['hrleave' => $hrleave]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeave $hrleave): View
	{
		return view('humanresources.hrdept.leave.edit', ['hrleave' => $hrleave]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeave $hrleave): RedirectResponse
	{
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// initial setup for create a leave
		$user = Staff::find($hrleave->staff_id);
		// dd($user);																									// for specific user
		$daStart = Carbon::parse($request->date_time_start);															// date start : for manipulation

		// start to give back the AL, MC, Maternity & Replacement Leave
		$t = $daStart->copy()->format('Y');

		$r1 = $hrleave->belongstomanyleaveannual()->first();
		$r2 = $hrleave->belongstomanyleavemc()->first();
		$r3 = $hrleave->belongstomanyleavematernity()->first();
		$r4 = $hrleave->belongstomanyleavereplacement()->first();

		if( $request->missing('date_time_end') ) {																		// in time off, there only date_time_start so...
			$request->date_time_end = $request->date_time_start;
		}

		$ye = $daStart->copy()->format('y');																			// strip down to 2 digits

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// need to enable it back later after update
		$b4leavestatus = Session::flash('hrleave', $hrleave->leave_status_id);
		// need to change leave status so unavailable date will not detect the date
		$hrleave->update(['leave_status_id' => 3]);

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// if a user select more than 1 day and setting double date is on, we need to count the remaining day that is not overlapping
		$blockdate = UnavailableDateTime::blockDate($user->id);
		// dd($blockdate);

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
				if(Carbon::parse($val1)->EqualTo(Carbon::parse($val2))){
					$leav[] = Carbon::parse($val1)->format('Y-m-d');
				}
			}
		}
		$filtered = array_diff($lea, $leav);																			// get all the dates that is not overlapped
		$totaldayfiltered = count($filtered);																			// total days

		$dateStartEnd = [];
		if($totalday == $totaldayfiltered){
			$noOverlap = true;																							// meaning we CAN take $request->date_time_end $request->date_time_start as is to be insert in database
		} else {
			$noOverlap = false;																							// meaning we CANT take $request->date_time_end $request->date_time_start as is to be insert in database, instead we need to separate it row by row to be inserted into database.
			// we need to loop entire date which is available 1 by 1
			foreach($filtered as $d){
				$dateStartEnd[] = ['date_time_start' => $d, 'date_time_end' => $d];
			}
		}
		// dd([$totalday, $noOverlap, $totaldayfiltered, (!$noOverlap && !($hrleave->period_day == $totalday))]);

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// overlapped date with other date
		// this need to comment out soon when i finished with the update leave
		// if (!$noOverlap && !($hrleave->period_day == $totalday)) {
		if (!$noOverlap) {
			if ($request->leave_type_id != 7) {
				Session::flash('flash_danger', 'Date leave overlapped with other LEAVE, PUBLIC HOLIDAY and RESTDAY');
				$hrleave->update(['leave_status_id' => $b4leavestatus]);
				return redirect()->back()->withInput();
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// calculate leave balance

		// change leave to AL/EL-AL
		if($request->leave_type_id == 1 || $request->leave_type_id == 5) {
			// change leave to AL/EL-AL from AL/EL-AL
			if ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if((($r1->annual_leave_balance) + $hrleave->period_day) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} else {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				}
			}

			// change leave to AL/EL-AL from OTHERS (MC, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10)) {
				$r1a = $user->hasmanyleaveannual()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if($r1a->annual_leave_balance < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if($r1a->annual_leave_balance < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if($r1a->annual_leave_balance < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} else {
						if($r1a->annual_leave_balance < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				}
			}
		}

		// change leave to MC
		if ($request->leave_type_id == 2) {
			// change leave to MC from MC
			if ($hrleave->leave_type_id == 2) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if((($r2->mc_leave_balance) + $hrleave->period_day) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} else {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				}
			}
			// change leave to MC from OTHERS (AL/EL-AL, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10)) {
				$r2a = $user->hasmanyleavemc()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r2a->mc_leave_balance) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if(($r2a->mc_leave_balance) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r2a->mc_leave_balance) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} else {
						if(($r2a->mc_leave_balance) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				}
			}
		}

		// change leave to ML
		if ($request->leave_type_id == 7) {
			// change leave to ML from ML
			if ($hrleave->leave_type_id == 7) {
				if((($r3->maternity_leave_balance) + $hrleave->period_day) < $totalday){
					Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					return redirect()->back();
				}
			}

			// change leave to ML from OTHERS (AL/EL-AL, MC, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10)) {
				$r3a = $user->hasmanyleavematernity()->where('year', $t)->first();
				if(($r3a->maternity_leave_balance) < $totalday){
					Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					return redirect()->back();
				}
			}
		}

		// change leave to NRL/EL-NRL
		if ($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// change leave to NRL/EL-NRL from NRL/EL-NRL

			// using new replacement leave
			$r4a = HRLeaveReplacement::find($request->id);

			if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {
				// careful, we've got 2 condition
				// 1. using old nrl entitlement
				// 2. using new nrl entitlement
				// so if using old entitlement
				if ($request->id == $r4->id) {
					if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
						if($request->leave_cat == 2){																										// half day
							if((($r4->leave_balance) + $hrleave->period_day) < 0.5){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						} elseif($request->leave_cat == 1) {
							if((($r4->leave_balance) + $hrleave->period_day) < 1){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if((($r4->leave_balance) + $hrleave->period_day) < $totalday){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						} else {
							if((($r4->leave_balance) + $hrleave->period_day) < $totaldayfiltered){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						}
					}
				} else {
					// using new NRL entitlement
					if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
						if($request->leave_cat == 2){																										// half day
							if($r4a->leave_balance < 0.5){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						} elseif($request->leave_cat == 1) {
							if($r4a->leave_balance < 1){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if($r4a->leave_balance < $totalday){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						} else {
							if($r4a->leave_balance < $totaldayfiltered){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								return redirect()->back();
							}
						}
					}
				}
			}

			// change leave to NRL/EL-NRL from OTHERS (AL/EL-AL, ML, MC)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 2)) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r4a->leave_balance) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if(($r4a->leave_balance) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r4a->leave_balance) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					} else {
						if(($r4a->leave_balance) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							return redirect()->back();
						}
					}
				}
			}
		}

		// change leave to TF from TF
		if ($request->leave_type_id == 9) {
			// change leave to MC from TF
			if ($hrleave->leave_type_id == 9)
			{
				// convert $request->time_start and $request->time_end to mysql format
				$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
				$te = Carbon::parse($request->date_time_start.' '.$request->time_end);

				if ( $ts->gte($te) ) { // time start less than time end
					Session::flash('flash_danger', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					return redirect()->back()->withInput();
				}
			}

			// change leave to TF from OTHERS
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || $hrleave->leave_type_id == 2 || $hrleave->leave_type_id == 7 || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12))
			{

			}
		}

		// test
		// $hrleave->update(['leave_status_id' => $b4leavestatus]);
		// Session::flash('flash_message', 'Pass Checkpoint 1');
		// return redirect()->back()->withInput();
		// exit;
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// give back all to entitlement
		// AL & EL-AL
		if ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) {
			if (!$r1) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
				$hrleave->update(['leave_status_id' => $b4leavestatus]);
				return redirect()->back()->withInput();
			}
			// $r1 = HRLeaveAnnual::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r1->annual_leave_utilize;
			$balance = $r1->annual_leave_balance;
			$total = $r1->annual_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleaveannual()->where('year', $daStart->copy()->year)->first();
			$r1->update([																								// update entitlements
							'annual_leave_utilize' => $newutilize,
							'annual_leave_balance' => $newbalance,
						]);
		}

		// MC
		if ($hrleave->leave_type_id == 2) {																				// give back all to MC
			if (!$r2) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
				$hrleave->update(['leave_status_id' => $b4leavestatus]);
				return redirect()->back()->withInput();
			}
			// $r2 = HRLeaveMC::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r2->mc_leave_utilize;
			$balance = $r2->mc_leave_balance;
			$total = $r2->mc_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r2->update([
							'mc_leave_utilize' => $newutilize,
							'mc_leave_balance' => $newbalance,
						]);
		}

		// ML
		if ($hrleave->leave_type_id == 7) {																				// give back all to ML
			if (!$r3) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
				$hrleave->update(['leave_status_id' => $b4leavestatus]);
				return redirect()->back()->withInput();
			}
			// $r3 = HRLeaveMaternity::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r3->maternity_leave_utilize;
			$balance = $r3->maternity_leave_balance;
			$total = $r3->maternity_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r3->update([
							'maternity_leave_utilize' => $newutilize,
							'maternity_leave_balance' => $newbalance,
						]);
		}

		// NRL & EL-NRL
		if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {											// give back all to NRL & EL-NRL
			if (!$r4) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
				$hrleave->update(['leave_status_id' => $b4leavestatus]);
				return redirect()->back()->withInput();
			}
			// $r4 = HRLeaveReplacement::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r4->leave_utilize;
			$balance = $r4->leave_balance;
			$total = $r4->leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r4->update([
							'leave_utilize' => $newutilize,
							'leave_balance' => $newbalance,
						]);
		}

		// dd($r1, $r2, $r3, $r4);
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for AL & EL-AL

		// change leave to AL/EL-AL
		if($request->leave_type_id == 1 || $request->leave_type_id == 5) {
			// change leave to AL/EL-AL from AL/EL-AL
			if ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if((($r1->annual_leave_balance) + $hrleave->period_day) >= 0.5){
							$r1->update([
								'annual_leave_utilize' => $r1->annual_leave_utilize + 0.5,
								'annual_leave_balance' => $r1->annual_leave_balance - 0.5
							]);
							$time = explode( '/', $request->half_type_id );
							$data = $request->only(['leave_type_id', 'leave_cat']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['half_type_id' => $time[0]];
							$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
							$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
							$data += ['period_day' => 0.5];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
						}
					} elseif($request->leave_cat == 1) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) >= 1){
							$r1->update([
								'annual_leave_utilize' => $r1->annual_leave_utilize + 1,
								'annual_leave_balance' => $r1->annual_leave_balance - 1
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => 1];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) >= $totalday){
							$r1->update([
								'annual_leave_utilize' => $r1->annual_leave_utilize + $totalday,
								'annual_leave_balance' => $r1->annual_leave_balance - $totalday
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => $totalday];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
						}
					}
					// else
					// {
					// 	if(($r1->annual_leave_balance + $hrleave->period_day) >= $totaldayfiltered){
					// 		$r1->update([
					// 			'annual_leave_utilize' => $r1->annual_leave_utilize + $totaldayfiltered,
					// 			'annual_leave_balance' => $r1->annual_leave_utilize - $totaldayfiltered
					// 		]);
					// 	}
					// }
				}
			}

			// change leave to AL/EL-AL from OTHERS (MC, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				// detach all entitlement especially for MC, ML, NRL/EL-NRL

				// $hrleave->belongstomanyleaveannual()->detach($r1->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				$r1a = $user->hasmanyleaveannual()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r1a->annual_leave_balance) >= 0.5){
							$r1a->update([
								'annual_leave_utilize' => $r1a->annual_leave_utilize + 0.5,
								'annual_leave_balance' => $r1a->annual_leave_balance - 0.5
							]);
							$time = explode( '/', $request->half_type_id );
							$data = $request->only(['leave_type_id', 'leave_cat']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['half_type_id' => $time[0]];
							$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
							$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
							$data += ['period_day' => 0.5];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleaveannual()->attach($r1a->id);
						}
					} elseif($request->leave_cat == 1) {
						if(($r1a->annual_leave_balance) >= 1){
							$r1a->update([
								'annual_leave_utilize' => $r1a->annual_leave_utilize + 1,
								'annual_leave_balance' => $r1a->annual_leave_balance - 1
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => 1];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleaveannual()->attach($r1a->id);
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r1a->annual_leave_balance) >= $totalday){
							$r1a->update([
								'annual_leave_utilize' => $r1a->annual_leave_utilize + $totalday,
								'annual_leave_balance' => $r1a->annual_leave_balance - $totalday
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => $totalday];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleaveannual()->attach($r1a->id);
						}
					}
					// else
					// {
					// 	if(($r1a->annual_leave_balance) >= $totaldayfiltered){

					// 	}
					// }
				}
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for MC
		// change leave to MC
		if ($request->leave_type_id == 2) {
			// change leave to MC from MC
			if ($hrleave->leave_type_id == 2) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if((($r2->mc_leave_balance) + $hrleave->period_day) >= 0.5){
							$r2->update([
								'mc_leave_utilize' => $r2->mc_leave_utilize + 0.5,
								'mc_leave_balance' => $r2->mc_leave_balance - 0.5
							]);
							$time = explode( '/', $request->half_type_id );
							$data = $request->only(['leave_type_id', 'leave_cat']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['half_type_id' => $time[0]];
							$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
							$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
							$data += ['period_day' => 0.5];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);

						}
					} elseif($request->leave_cat == 1) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) >= 1){
							$r2->update([
								'mc_leave_utilize' => $r2->mc_leave_utilize + 1,
								'mc_leave_balance' => $r2->mc_leave_balance - 1
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => 1];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) >= $totalday){
							$r2->update([
								'mc_leave_utilize' => $r2->mc_leave_utilize + $totalday,
								'mc_leave_balance' => $r2->mc_leave_balance - $totalday
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => $totalday];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
						}
					}
					// else
					// {
					// 	if((($r2->mc_leave_balance) + $hrleave->period_day) >= $totaldayfiltered){
					// 	}
					// }
				}
			}
			// change leave to MC from OTHERS (AL/EL-AL, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {

				// detach all entitlement especially for MC, ML, NRL/EL-NRL
				$hrleave->belongstomanyleaveannual()->detach($r1->id);
				// $hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				// need to remove backup if any, since MC got no backup
				$hrleave->hasmanyleaveapprovalbackup()->where('leave_id', $hrleave->id)->delete();

				$r2a = $user->hasmanyleavemc()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r2a->mc_leave_balance) >= 0.5){
							$r2a->update([
								'mc_leave_utilize' => $r2a->mc_leave_utilize + 0.5,
								'mc_leave_balance' => $r2a->mc_leave_balance - 0.5
							]);
							$time = explode( '/', $request->half_type_id );
							$data = $request->only(['leave_type_id', 'leave_cat']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['half_type_id' => $time[0]];
							$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
							$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
							$data += ['period_day' => 0.5];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavemc()->attach($r2a->id);
						}
					} elseif($request->leave_cat == 1) {
						if(($r2a->mc_leave_balance) >= 1){
							$r2a->update([
								'mc_leave_utilize' => $r2a->mc_leave_utilize + 1,
								'mc_leave_balance' => $r2a->mc_leave_balance - 1
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => 1];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavemc()->attach($r2a->id);
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r2a->mc_leave_balance) >= $totalday){
							$r2a->update([
								'mc_leave_utilize' => $r2a->mc_leave_utilize + $totalday,
								'mc_leave_balance' => $r2a->mc_leave_balance - $totalday
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => $totalday];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavemc()->attach($r2a->id);
						}
					}
					// else
					// {
					// 	if(($r2a->mc_leave_balance) >= $totaldayfiltered){
					// 	}
					// }
				}
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for ML
		// change leave to ML
		if ($request->leave_type_id == 7) {
			// change leave to ML from ML
			if ($hrleave->leave_type_id == 7) {
				if((($r3->maternity_leave_balance) + $hrleave->period_day) >= $totalday){
					$r3->update([
						'maternity_leave_utilize' => $r3->maternity_leave_utilize + $totalday,
						'maternity_leave_balance' => $r3->maternity_leave_balance - $totalday
					]);
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['period_day' => $totalday];
					if ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						$request->document->storeAs('public/leaves', $fileName);
						$data += ['softcopy' => $fileName];
					}
					$hrleave->update($data);
				}
			}

			// change leave to ML from OTHERS (AL/EL-AL, MC, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				// detach all entitlement especially for MC, ML, NRL/EL-NRL

				$hrleave->belongstomanyleaveannual()->detach($r1->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				// $hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				$r3a = $user->hasmanyleavematernity()->where('year', $t)->first();
				if(($r3a->maternity_leave_balance) >= $totalday){
					$r3a->update([
						'maternity_leave_utilize' => $r3a->maternity_leave_utilize + $totalday,
						'maternity_leave_balance' => $r3a->maternity_leave_balance - $totalday
					]);
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['period_day' => $totalday];
					if ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						$request->document->storeAs('public/leaves', $fileName);
						$data += ['softcopy' => $fileName];
					}
					$hrleave->update($data);
					$hrleave->belongstomanyleavematernity()->attach($r3a->id);
				}
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for NRL/EL-NRL
		// change leave to NRL/EL-NRL
		if ($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// change leave to NRL/EL-NRL from NRL/EL-NRL

			// using new replacement leave
			$r4a = HRLeaveReplacement::find($request->id);

			if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {
				// careful, we've got 2 condition
				// 1. using old nrl entitlement
				// 2. using new nrl entitlement
				// so if using old entitlement
				if ($request->id == $r4->id) {
					if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
						if($request->leave_cat == 2){																										// half day
							if((($r4->leave_balance) + $hrleave->period_day) >= 0.5){
								$r4->update([
									'leave_utilize' => $r4->leave_utilize + 0.5,
									'leave_balance' => $r4->leave_balance - 0.5
								]);
								$time = explode( '/', $request->half_type_id );
								$data = $request->only(['leave_type_id', 'leave_cat']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['half_type_id' => $time[0]];
								$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
								$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
								$data += ['period_day' => 0.5];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
							}
						} elseif($request->leave_cat == 1) {
							if((($r4->leave_balance) + $hrleave->period_day) >= 1){
								$r4->update([
									'leave_utilize' => $r4->leave_utilize + 1,
									'leave_balance' => $r4->leave_balance - 1
								]);
								$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['period_day' => 1];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if((($r4->leave_balance) + $hrleave->period_day) >= $totalday){
								$r4->update([
									'leave_utilize' => $r4->leave_utilize + $totalday,
									'leave_balance' => $r4->leave_balance - $totalday
								]);
								$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['period_day' => $totalday];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
							}
						}
						// else {
						// 	if((($r4->leave_balance) + $hrleave->period_day) >= $totaldayfiltered){
						// 	}
						// }
					}
				} else {
					// using new NRL entitlement
					if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
						if($request->leave_cat == 2){																										// half day
							if($r4a->leave_balance >= 0.5){
								$r4a->update([
									'leave_utilize' => $r4a->leave_utilize + 0.5,
									'leave_balance' => $r4a->leave_balance - 0.5
								]);
								$time = explode( '/', $request->half_type_id );
								$data = $request->only(['leave_type_id', 'leave_cat']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['half_type_id' => $time[0]];
								$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
								$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
								$data += ['period_day' => 0.5];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
								$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
							}
						} elseif($request->leave_cat == 1) {
							if($r4a->leave_balance >= 1){
								$r4a->update([
									'leave_utilize' => $r4a->leave_utilize + 1,
									'leave_balance' => $r4a->leave_balance - 1
								]);
								$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['period_day' => 1];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
								$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if($r4a->leave_balance >= $totalday){
								$r4a->update([
									'leave_utilize' => $r4a->leave_utilize + $totalday,
									'leave_balance' => $r4a->leave_balance - $totalday
								]);
								$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
								$data += ['reason' => ucwords(Str::lower($request->reason))];
								$data += ['period_day' => $totalday];
								if ($request->file('document')) {
									$file = $request->file('document')->getClientOriginalName();
									$currentDate = Carbon::now()->format('Y-m-d His');
									$fileName = $currentDate . '_' . $file;
									$request->document->storeAs('public/leaves', $fileName);
									$data += ['softcopy' => $fileName];
								}
								$hrleave->update($data);
								$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
							}
						}
						// else {
						// 	if($r4a->leave_balance >= $totaldayfiltered){
						// 	}
						// }
					}
				}
			}

			// change leave to NRL/EL-NRL from OTHERS (AL/EL-AL, ML, MC)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				// detach all entitlement especially for MC, ML, NRL/EL-NRL

				$hrleave->belongstomanyleaveannual()->detach($r1->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				// $hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r4a->leave_balance) >= 0.5){
							$r4a->update([
								'leave_utilize' => $r4a->leave_utilize + 0.5,
								'leave_balance' => $r4a->leave_balance - 0.5
							]);
							$time = explode( '/', $request->half_type_id );
							$data = $request->only(['leave_type_id', 'leave_cat']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['half_type_id' => $time[0]];
							$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
							$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
							$data += ['period_day' => 0.5];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
						}
					} elseif($request->leave_cat == 1) {
						if(($r4a->leave_balance) >= 1){
							$r4a->update([
								'leave_utilize' => $r4a->leave_utilize + 1,
								'leave_balance' => $r4a->leave_balance - 1
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => 1];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r4a->leave_balance) >= $totalday){
							$r4a->update([
								'leave_utilize' => $r4a->leave_utilize + $totalday,
								'leave_balance' => $r4a->leave_balance - $totalday
							]);
							$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
							$data += ['reason' => ucwords(Str::lower($request->reason))];
							$data += ['period_day' => $totalday];
							if ($request->file('document')) {
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d His');
								$fileName = $currentDate . '_' . $file;
								$request->document->storeAs('public/leaves', $fileName);
								$data += ['softcopy' => $fileName];
							}
							$hrleave->update($data);
							$hrleave->belongstomanyleavereplacement()->attach($r4a->id);
						}
					}
					// else {
					// 	if(($r4a->leave_balance) >= $totaldayfiltered){
					// 	}
					// }
				}
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for UPL/EL-UPL/MC-UPL/S-UPL
		if($request->leave_type_id == 3 || $request->leave_type_id == 6 || $request->leave_type_id == 11 || $request->leave_type_id == 12) {
			// change leave to UPL/EL-UPL/MC-UPL/S-UPL from UPL/EL-UPL/MC-UPL/S-UPL
			if ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						$time = explode( '/', $request->half_type_id );
						$data = $request->only(['leave_type_id', 'leave_cat']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					} elseif($request->leave_cat == 1) {
						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['period_day' => 1];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['period_day' => $totalday];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					}
					// else {
					// 	if((($r1->annual_leave_balance) + $hrleave->period_day) >= $totaldayfiltered){
					// 	}
					// }
				}
			}

			// change leave to UPL/EL-UPL/MC-UPL/S-UPL from OTHERS (MC, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 9) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5)) {
				// detach all entitlement especially for AL/EL-AL, MC, ML, NRL/EL-NRL
				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						$time = explode( '/', $request->half_type_id );
						$data = $request->only(['leave_type_id', 'leave_cat']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					} elseif($request->leave_cat == 1) {
						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['period_day' => 1];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['period_day' => $totalday];
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							$request->document->storeAs('public/leaves', $fileName);
							$data += ['softcopy' => $fileName];
						}
						$hrleave->update($data);
					}
					// else {
					// 	if($r1a->annual_leave_balance >= $totaldayfiltered){
					// 	}
					// }
				}
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// update database for TF
		// change leave to TF
		if($request->leave_type_id == 9)
		{
			// change leave to TF from TF
			if ($hrleave->leave_type_id == 9) {

				// convert $request->time_start and $request->time_end to mysql format
				$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
				$te = Carbon::parse($request->date_time_start.' '.$request->time_end);

				if ( $ts->gte($te) ) { // time start less than time end
					Session::flash('flash_danger', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
					return redirect()->back()->withInput();
				}

				// from user input
				$timep = CarbonPeriod::create($ts, '1 minutes', $te, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);
				// echo $timep->count().' tempoh minit masa keluar sblm tolak recess<br />';
				$timeuser = [];
				foreach($timep as $tp){
					$timeuser[] = Carbon::parse($tp)
					// ->format('h:i')
					;
				}
				$totalusermins = count($timeuser);
				// return [$timeuser, $totalusermins];

				// get working hours
				$whtime = UnavailableDateTime::workinghourtime($request->date_time_start, $hrleave->belongstostaff->id);
				$utsam = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_start_am);
				$uteam = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_end_am);
				$utspm = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_start_pm);
				$utepm = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_end_pm);
				$timeawh = CarbonPeriod::create($utsam, '1 minutes', $uteam, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);
				$timepwh = CarbonPeriod::create($utspm, '1 minutes', $utepm, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);

				$timeawh1 = [];
				foreach ($timeawh as $val1) {
					$timeawh1[] = Carbon::parse($val1)
					// ->format('h:i')
					;
				}
				$totalwhmins1 = count($timeawh1);

				$timeawh2 = [];
				foreach ($timepwh as $val2) {
					$timeawh2[] = Carbon::parse($val2)
					// ->format('h:i')
					;
				}
				$totalwhmins2 = count($timeawh2);

				$totalwh = Arr::collapse([$timeawh1, $timeawh2]);
				$totalwhmins = count($totalwh);

				foreach($totalwh as $k1){
					foreach($timeuser as $k2){
						if ( Carbon::parse($k1)->EqualTo(Carbon::parse($k2)) ) {
							$timeoverlap[] = Carbon::parse($k1)->format('h:i');
						}
					}
				}
				$timeoverlapcount = count($timeoverlap);

				// if ( $timeoverlapcount > 125 ) { // minutes over than 2 hours with contingency
				// 	Session::flash('flash_danger', 'Your Time Off exceeded more than 2 hours. Please select time correctly.');
				// 	return redirect()->back()->withInput();
				// }

				// convert minutes to hours and minutes
				$hour = floor($timeoverlapcount/60);
				$minute = ($timeoverlapcount % 60);
				$t = $hour.':'.$minute.':00';
				// echo $t;

				$data = $request->only(['leave_type_id']);
				$data += ['reason' => ucwords(Str::lower($request->reason))];
				$data += ['verify_code' => $hrleave->verify_code];
				$data += ['date_time_start' => $ts];
				$data += ['date_time_end' => $te];
				$data += ['period_time' => $t];
				$data += ['leave_no' => $hrleave->leave_no];
				$data += ['leave_year' => $ye];
				$data += ['leave_status_id' => $hrleave->leave_status_id];
				$data += ['created_at' => $hrleave->created_at];
				if ($hrleave->softcopy) {
					$data += ['softcopy' => $hrleave->softcopy];
				} elseif ($request->file('document')) {
					$file = $request->file('document')->getClientOriginalName();
					$currentDate = Carbon::now()->format('Y-m-d His');
					$fileName = $currentDate . '_' . $file;
					// Store File in Storage Folder
					$request->document->storeAs('public/leaves', $fileName);
					// storage/app/uploads/file.png
					// Store File in Public Folder
					// $request->document->move(public_path('uploads'), $fileName);
					// public/uploads/file.png
					$data += ['softcopy' => $fileName];
				}
				$l = $user->hasmanyleave()->create($data);

				}
			}

			// change leave to TF from OTHERS
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || $hrleave->leave_type_id == 2 || $hrleave->leave_type_id == 7 || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12))
			{
				// detach all entitlement especially for AL/EL-AL, MC, ML, NRL/EL-NRL
				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);



			}
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$hrleave->update(['leave_status_id' => $b4leavestatus]);
		exit;
		if($request->leave_type_id == 1 || $request->leave_type_id == 5) {
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleaveannual()->where('year', $daStart->copy()->year)->first();
			if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
				if($request->leave_cat == 2){																										// half day
					if($entitlement->annual_leave_balance >= 0.5){																					// annual_leave_balance > 0.5

						$entitle = $entitlement->annual_leave_balance - 0.5;
						$utilize = $entitlement->annual_leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'leave_cat']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						// $data += ['verify_code' => $hrleave->verify_code];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						// $data += ['leave_no' => $hrleave->leave_no];
						// $data += ['leave_year' => $ye];
						// $data += ['leave_status_id' => $hrleave->leave_status_id];
						// $data += ['created_at' => $hrleave->created_at];
						// if ($hrleave->softcopy) {
						// 	$data += ['softcopy' => $hrleave->softcopy];
						// } elseif ($request->file('document')) {
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						// $l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$hrleave->update($data);
						// $l->belongstomanyleaveannual()->attach($entitlement->id);				// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						// $hrleave->belongstomanyleaveannual()->detach($entitlement->id);
						$r1->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);// update leave_balance by substarct
					} else {
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				} elseif($request->leave_cat == 1) {																								// apply leace 1 whole day
					if($entitlement->annual_leave_balance >= 1){																					// annual_leave_balance >= 1
						$entitle = $entitlement->annual_leave_balance - 1;
						$utilize = $entitlement->annual_leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						// $data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => 1];
						// $data += ['leave_no' => $hrleave->leave_no];
						// $data += ['leave_year' => $ye];
						// $data += ['leave_status_id' => $hrleave->leave_status_id];
						// $data += ['created_at' => $hrleave->created_at];
						// if ($hrleave->softcopy) {
						// 	$data += ['softcopy' => $hrleave->softcopy];
						// } elseif ($request->file('document')) {
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						// $l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$hrleave->update($data);
						// $l->belongstomanyleaveannual()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						// $hrleave->belongstomanyleaveannual()->detach($entitlement->id);
						$r1->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);// update leave_balance by substarct
						//can make a shortcut like this also
						// shortcut to update hr_leave_annual
						// not working
						// $c = $l->belongstomanyleaveannual()->attach($entitlement->id);
						// dd($c);
						// $c->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);

					} else {
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				}
			} else {																												// apply leave for 2 OR more days
				if ($noOverlap) {																									// true: date choose not overlapping date with unavailable date
					if($entitlement->annual_leave_balance >= $totalday) {																						// annual_leave_balance > $totalday
						$entitle = $entitlement->annual_leave_balance - $totalday;
						$utilize = $entitlement->annual_leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						// $data += ['verify_code' => $hrleave->leave_verify_code];
						$data += ['period_day' => $totalday];
						// $data += ['leave_no' => $hrleave->leave_no];
						// $data += ['leave_year' => $ye];
						// $data += ['leave_status_id' => $hrleave->leave_status_id];
						// $data += ['created_at' => $hrleave->created_at];
						// if ($hrleave->softcopy) {
						// 	$data += ['softcopy' => $hrleave->softcopy];
						// } elseif ($request->file('document')) {
						if ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleaveannual()->attach($entitlement->id);									// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleaveannual()->detach($entitlement->id);
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {																														// annual_leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				} else {					// false: date choose overlapping date with unavailable date
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
					Session::flash('flash_danger', 'The date you choose overlapped with RESTDAY, PUBLIC HOLIDAY or other leaves.');
					return redirect()->back();
				}
			}

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// MC
		if($request->leave_type_id == 2) {
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleavemc()->where('year', $daStart->copy()->year)->first();
			if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
				if($request->leave_cat == 2){																										// half day
					if($entitlement->mc_leave_balance >= 0.5){																							// mc_leave_balance > 0.5

						$entitle = $entitlement->mc_leave_balance - 0.5;
						$utilize = $entitlement->mc_leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'leave_cat']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['half_type_id' => $time[0]];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																			// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);			// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavemc()->detach($entitlement->id);
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				} elseif($request->leave_cat == 1) {																								// apply leace 1 whole day
					if($entitlement->mc_leave_balance >= 1){																								// mc_leave_balance >= 1
						$entitle = $entitlement->mc_leave_balance - 1;
						$utilize = $entitlement->mc_leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);								// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavemc()->detach($entitlement->id);
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				}
			} else {																													// apply leave for 2 OR more days
				if ($noOverlap) {																										// true: date choose not overlapping date with unavailable date
					if($entitlement->mc_leave_balance >= $totalday) {																	// mc_leave_balance > $totalday
						$entitle = $entitlement->mc_leave_balance - $totalday;
						$utilize = $entitlement->mc_leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																		// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavemc()->detach($entitlement->id);
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {																														// mc_leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				} else {					// false: date choose overlapping date with unavailable date
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
					Session::flash('flash_danger', 'The date you choose overlapped with RESTDAY, PUBLIC HOLIDAY or other leaves.');
					return redirect()->back();
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// NRL & EL-NRL
		if($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// $entitlement = $user->hasmanyleavereplacement()->where('id', $request->id)->first();
			$entitlement = $r4;
			if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
				if($request->leave_cat == 2){																										// half day
					if($entitlement->leave_balance >= 0.5){																							// leave_balance > 0.5

						$entitle = $entitlement->leave_balance - 0.5;
						$utilize = $entitlement->leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'leave_cat']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);															// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavereplacement()->detach($entitlement->id);
						$user->hasmanyleavereplacement()->where('id', $entitlement->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {
						Session::flash('flash_danger', 'Please ensure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				} elseif($request->leave_cat == 1) {																								// apply leace 1 whole day
					if($entitlement->leave_balance >= 1){																							// leave_balance >= 1
						$entitle = $entitlement->leave_balance - 1;
						$utilize = $entitlement->leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);											// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($entitlement->id);	// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavereplacement()->detach($entitlement->id);
						$user->hasmanyleavereplacement()->where('id', $entitlement->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																		// true: date choose not overlapping date with unavailable date
					if($entitlement->leave_balance >= $totalday) {																					// leave_balance > $totalday
						$entitle = $entitlement->leave_balance - $totalday;
						$utilize = $entitlement->leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
						$data += ['reason' => ucwords(Str::lower($request->reason))];
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $ye];
						$data += ['leave_status_id' => $hrleave->leave_status_id];
						$data += ['created_at' => $hrleave->created_at];
						if ($hrleave->softcopy) {
							$data += ['softcopy' => $hrleave->softcopy];
						} elseif ($request->file('document')) {
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d His');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('public/leaves', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($entitlement->leave_replacement_id);			// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleavereplacement()->detach($entitlement->id);
						$user->hasmanyleavereplacement()->where('id', $entitlement->leave_replacement_id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct

					} else {																														// leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
						return redirect()->back()->withInput();
					}
				} else {																	// false: date choose overlapping date with unavailable date
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
					Session::flash('flash_danger', 'The date you choose overlapped with RESTDAY, PUBLIC HOLIDAY or other leaves.');
					return redirect()->back()->withInput();
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// ML
		if($request->leave_type_id == 7) {
			$entitlement = $user->hasmanyleavematernity()->where('year', $daStart->year)->first();										// check entitlement if configured or not
			if($entitlement->maternity_leave_balance >= $totalday) {
				// if(!$entitlement) {																									// kick him out if there is no entitlement been configured for entitlement
				// 	Session::flash('flash_danger', 'Please contact with Human Resources Manager. Most probably, HR havent configured yet entitlement.');
				// 	return redirect()->back();
				// }
				$entitle = $entitlement->maternity_leave_balance - $totalday;
				$utilize = $entitlement->maternity_leave_utilize + $totalday;
				$data = $request->only(['leave_type_id', 'date_time_start', 'date_time_end']);
				$data += ['reason' => ucwords(Str::lower($request->reason))];
				$data += ['verify_code' => $hrleave->verify_code];
				$data += ['period_day' => $totalday];
				$data += ['leave_no' => $hrleave->leave_no];
				$data += ['leave_year' => $ye];
				$data += ['leave_status_id' => $hrleave->leave_status_id];
				$data += ['created_at' => $hrleave->created_at];
				if ($hrleave->softcopy) {
					$data += ['softcopy' => $hrleave->softcopy];
				} elseif ($request->file('document')) {
					$file = $request->file('document')->getClientOriginalName();
					$currentDate = Carbon::now()->format('Y-m-d His');
					$fileName = $currentDate . '_' . $file;
					// Store File in Storage Folder
					$request->document->storeAs('public/leaves', $fileName);
					// storage/app/uploads/file.png
					// Store File in Public Folder
					// $request->document->move(public_path('uploads'), $fileName);
					// public/uploads/file.png
					$data += ['softcopy' => $fileName];
				}

				$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
				$l->belongstomanyleavematernity()->attach($entitlement->id);			// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
				$hrleave->belongstomanyleavematernity()->detach($entitlement->id);
				$user->hasmanyleavematernity()->where('year', $daStart->year)->update(['maternity_leave_balance' => $entitle, 'maternity_leave_utilize' => $utilize]);	// update leave_balance by substarct

			} else {
				Session::flash('flash_danger', 'No more maternity leave available.');
				return redirect()->back()->withInput();
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// TF
		if($request->leave_type_id == 9) {
			// convert $request->time_start and $request->time_end to mysql format
			$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
			$te = Carbon::parse($request->date_time_start.' '.$request->time_end);

			if ( $ts->gte($te) ) { // time start less than time end
				Session::flash('flash_danger', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
				return redirect()->back()->withInput();
			}

			// from user input
			$timep = CarbonPeriod::create($ts, '1 minutes', $te, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);
			// echo $timep->count().' tempoh minit masa keluar sblm tolak recess<br />';
			$timeuser = [];
			foreach($timep as $tp){
				$timeuser[] = Carbon::parse($tp)
				// ->format('h:i')
				;
			}
			$totalusermins = count($timeuser);
			// return [$timeuser, $totalusermins];

			// get working hours
			$whtime = UnavailableDateTime::workinghourtime($request->date_time_start, $hrleave->belongstostaff->id);
			$utsam = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_start_am);
			$uteam = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_end_am);
			$utspm = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_start_pm);
			$utepm = Carbon::parse($request->date_time_start.' '.$whtime->first()->time_end_pm);
			$timeawh = CarbonPeriod::create($utsam, '1 minutes', $uteam, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);
			$timepwh = CarbonPeriod::create($utspm, '1 minutes', $utepm, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);

			$timeawh1 = [];
			foreach ($timeawh as $val1) {
				$timeawh1[] = Carbon::parse($val1)
				// ->format('h:i')
				;
			}
			$totalwhmins1 = count($timeawh1);

			$timeawh2 = [];
			foreach ($timepwh as $val2) {
				$timeawh2[] = Carbon::parse($val2)
				// ->format('h:i')
				;
			}
			$totalwhmins2 = count($timeawh2);

			$totalwh = Arr::collapse([$timeawh1, $timeawh2]);
			$totalwhmins = count($totalwh);

			foreach($totalwh as $k1){
				foreach($timeuser as $k2){
					if ( Carbon::parse($k1)->EqualTo(Carbon::parse($k2)) ) {
						$timeoverlap[] = Carbon::parse($k1)->format('h:i');
					}
				}
			}
			$timeoverlapcount = count($timeoverlap);

			// if ( $timeoverlapcount > 125 ) { // minutes over than 2 hours with contingency
			// 	Session::flash('flash_danger', 'Your Time Off exceeded more than 2 hours. Please select time correctly.');
			// 	return redirect()->back()->withInput();
			// }

			// convert minutes to hours and minutes
			$hour = floor($timeoverlapcount/60);
			$minute = ($timeoverlapcount % 60);
			$t = $hour.':'.$minute.':00';
			// echo $t;

			$data = $request->only(['leave_type_id']);
			$data += ['reason' => ucwords(Str::lower($request->reason))];
			$data += ['verify_code' => $hrleave->verify_code];
			$data += ['date_time_start' => $ts];
			$data += ['date_time_end' => $te];
			$data += ['period_time' => $t];
			$data += ['leave_no' => $hrleave->leave_no];
			$data += ['leave_year' => $ye];
			$data += ['leave_status_id' => $hrleave->leave_status_id];
			$data += ['created_at' => $hrleave->created_at];
			if ($hrleave->softcopy) {
				$data += ['softcopy' => $hrleave->softcopy];
			} elseif ($request->file('document')) {
				$file = $request->file('document')->getClientOriginalName();
				$currentDate = Carbon::now()->format('Y-m-d His');
				$fileName = $currentDate . '_' . $file;
				// Store File in Storage Folder
				$request->document->storeAs('public/leaves', $fileName);
				// storage/app/uploads/file.png
				// Store File in Public Folder
				// $request->document->move(public_path('uploads'), $fileName);
				// public/uploads/file.png
				$data += ['softcopy' => $fileName];
			}
			$l = $user->hasmanyleave()->create($data);

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// UPL & EL-UPL & MC-UPL
		if($request->leave_type_id == 3 || $request->leave_type_id == 6 || $request->leave_type_id == 11) {
			if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
				if($request->leave_cat == 2){																										// half day
					$time = explode( '/', $request->half_type_id );

					$data = $request->only(['leave_type_id', 'leave_cat']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['half_type_id' => $time[0]];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
					$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
					$data += ['period_day' => 0.5];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}
					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave

				} elseif ($request->leave_cat == 1) {																								// apply leace 1 whole day
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['period_day' => 1];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}

					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave

				}
			} else {																														// apply leave for 2 OR more days
				if ($noOverlap) {																										// true: date choose not overlapping date with unavailable date
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['period_day' => $totalday];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}

					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave

				} else {					// false: date choose overlapping date with unavailable date
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
					Session::flash('flash_danger', 'The date you choose overlapped with RESTDAY, PUBLIC HOLIDAY or other leaves.');
					return redirect()->back();
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// S-UPL
		if($request->leave_type_id == 12) {
			if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
				if($request->leave_cat == 2){																										// half day
					$time = explode( '/', $request->half_type_id );

					$data = $request->only(['leave_type_id', 'leave_cat']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['half_type_id' => $time[0]];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
					$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
					$data += ['period_day' => 0.5];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}

					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave

				} elseif($request->leave_cat == 1) {																								// apply leace 1 whole day
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end', 'half_type_id']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['period_day' => 1];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}
					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
				}
			} else {																															// apply leave for 2 OR more days
				if ($noOverlap) {												// true: date choose not overlapping date with unavailable date
					$data = $request->only(['leave_type_id', 'leave_cat', 'date_time_start', 'date_time_end']);
					$data += ['reason' => ucwords(Str::lower($request->reason))];
					$data += ['verify_code' => $hrleave->verify_code];
					$data += ['period_day' => $totalday];
					$data += ['leave_no' => $hrleave->leave_no];
					$data += ['leave_year' => $ye];
					$data += ['leave_status_id' => $hrleave->leave_status_id];
					$data += ['created_at' => $hrleave->created_at];
					if ($hrleave->softcopy) {
						$data += ['softcopy' => $hrleave->softcopy];
					} elseif ($request->file('document')) {
						$file = $request->file('document')->getClientOriginalName();
						$currentDate = Carbon::now()->format('Y-m-d His');
						$fileName = $currentDate . '_' . $file;
						// Store File in Storage Folder
						$request->document->storeAs('public/leaves', $fileName);
						// storage/app/uploads/file.png
						// Store File in Public Folder
						// $request->document->move(public_path('uploads'), $fileName);
						// public/uploads/file.png
						$data += ['softcopy' => $fileName];
					}

					$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave

				} else {											// false: date choose overlapping date with unavailable date
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
					Session::flash('flash_danger', 'The date you choose overlapped with RESTDAY, PUBLIC HOLIDAY or other leaves.');
					return redirect()->back()->withInput();
				}
			}
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ($hrleave->hasmanyleaveamend()->count()) {
			foreach (HRLeaveAmend::where('leave_id', $hrleave->id)->get() as $v) {
				HRLeaveAmend::find($v->id)->update([
														'leave_id' => $l->id,
														'amend_note' => ucwords(Str::lower($v->amend_note)).'<br />'.ucwords(Str::lower($request->amend_note)),
														'staff_id' => \Auth::user()->belongstostaff->id,
														'date' => now()
				]);
			}
		} else {
			$l->hasmanyleaveamend()->create([
												'amend_note' => ucwords(Str::lower($request->amend_note)),
												'staff_id' => \Auth::user()->belongstostaff->id,
												'date' => now()
			]);
		}

		// if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
		// 	if($request->staff_id) {																								// backup only valid for non EL leave
		// 		$bid = $hrleave->hasmanyleaveapprovalbackup()->first()->id;
		// 		HRLeaveApprovalBackup::find($bid)->update(['leave_id' => $l->id]);
		// 	}
		// }
		// if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
		// 	$sid = $hrleave->hasmanyleaveapprovalsupervisor()->first()->id;
		// 	HRLeaveApprovalSupervisor::find($sid)->update(['leave_id' => $l->id]);
		// }
		// if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
		// 	$hid = $hrleave->hasmanyleaveapprovalhod()->first()->id;
		// 	HRLeaveApprovalHOD::find($hid)->update(['leave_id' => $l->id]);
		// }
		// if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
		// 	$did = $hrleave->hasmanyleaveapprovaldir()->first()->id;
		// 	HRLeaveApprovalDirector::find($did)->update(['leave_id' => $l->id]);
		// }
		// if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
		// 	$rid = $hrleave->hasmanyleaveapprovalhr()->first()->id;
		// 	HRLeaveApprovalHR::find($rid)->update(['leave_id' => $l->id]);
		// }
		// finally, we cancelled the leave
		// $hrleave->update(['leave_status_id' => 3, 'remarks' => 'Edit leave. period_day = '.$hrleave->period_day.' | period_time = '.$hrleave->period_time, 'period_day' => 0, 'period_time' => '00:00:00']);
		$b = HRAttendance::where('leave_id', $hrleave->id)->get();
		foreach ($b as $c) {
			HRAttendance::where('id', $c->id)->update(['leave_id' => null]);
		}
		Session::flash('flash_message', 'Successfully edit leave. Please check the date of leave at the attendance section for a verification');
		return redirect()->route('hrleave.show', $hrleave->id);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hrleave): JsonResponse
	{
		//
	}
}

