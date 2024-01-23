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

use Illuminate\Database\Eloquent\Builder;

// load validator
use Illuminate\Support\Facades\Validator;

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
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['reject', 'cancel', 'create', 'store', 'edit', 'update', 'destroy']]);	// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.leave.index');
	}

	public function reject(): View
	{
		$reject = HRLeave::where('leave_status_id', 4)
							->where(function (Builder $query) {
								$query->whereDate('date_time_start', '>=', now()->startOfYear())
								->whereDate('date_time_end', '<=', now()->endOfYear());
							})
							->get();

		return view('humanresources.hrdept.leave.reject', ['reject' => $reject]);
	}

	public function cancel(): View
	{
		$cancel = HRLeave::where('leave_status_id', 3)
							->where(function (Builder $query) {
								$query->whereDate('date_time_start', '>=', now()->startOfYear())
								->whereDate('date_time_end', '<=', now()->endOfYear());
							})
							->get();
		return view('humanresources.hrdept.leave.cancel', ['cancel' => $cancel]);
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
		$validated = $request->validate(
			[
				'leave_type_id' => 'required',
				'reason' => 'required',
				'date_time_start' => 'required|date_format:Y-m-d',
				'date_time_end' => 'required_if:leave_type_id,1,2,3,4,5,6,7,10,11,12,13,14|date_format:Y-m-d',
				'leave_cat' => 'sometimes|required_if:date_time_start,date_time_end',
				// 'leave_cat' => [
				// 					'sometimes|required',
				// 					function ($attribute, $value, $fail) use ($request) {
				// 						if ($request->input('date_time_start') !== $request->input('date_time_end')) {
				// 							$fail('The "Leave Category" field is required when datestart and dateend have the same value.');
				// 						}
				// 					},
				// 				],
				'half_type_id' => 'required_if:leave_cat,2',
				'staff_id' => 'sometimes|required',
				'amend_note' => 'required',
				'document' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,bmp,pdf,doc,docs,csv,xls,xlsx',
				'documentsupport' => 'required_if:document,null',
				'id' => 'sometimes|required_if:leave_type_id,10|required_if:leave_type_id,4',
				'time_start' => 'required_if:leave_type_id,9',
				'time_end' => 'required_if:leave_type_id,9',
				'akuan' => 'sometimes|required',
			],
			[
				// 'leave_type_id.required' => 'Please insert year',
				// 'reason.required' => 'Please insert year',
				// 'date_time_start.*.required' => 'Please insert year',
				'date_time_end.required_if' => 'The :attribute field is required when :attribute is not Time Off.',
				// 'leave_cat.*.required' => 'Please insert year',
				// 'half_type_id.*.required' => 'Please insert year',
				// 'staff_id.*.required' => 'Please insert year',
				// 'amend_note.*.required' => 'Please insert year',
				// 'document.*.required' => 'Please insert year',
				// 'documentsupport.*.required' => 'Please insert year',
				// 'id.*.required' => 'Please insert year',
				'time_start.required_if' => 'The :attribute field is required when Leave Type is Time Off. ',
				'time_end.required_if' => 'The :attribute field is required when Leave Type is Time Off. ',
				// 'akuan.*.required' => 'Please insert year',
			],
			[
				'leave_type_id' => 'Leave Type',
				'reason' => 'Reason',
				'date_time_start' => 'Date From',
				'date_time_end' => 'Date To',
				'leave_cat' => 'Leave Category',
				'half_type_id' => 'Half Day Time',
				'staff_id' => 'Backup Person',
				'amend_note' => 'Amend Note',
				'document' => 'Upload Supporting Document',
				'documentsupport' => 'Supporting Document Acknowledgement',
				'id' => 'Replacement Leave Day',
				'time_start' => 'Time Start',
				'time_end' => 'Time End',
				'akuan' => 'Acknowledgement',
			]
		);

		// $validator->sometimes('reason', 'required|max:500', function (Fluent $input) {
		// 	return $input->games >= 100;
		// });

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
		$request->session()->put('hrleave', $hrleave->leave_status_id);
		$b4leavestatus = $request->session()->get('hrleave', null);
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
				$request->session()->forget('hrleave');
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
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} else {
						if((($r1->annual_leave_balance) + $hrleave->period_day) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				}
			}

			// change leave to AL/EL-AL from OTHERS (MC, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				$r1a = $user->hasmanyleaveannual()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if($r1a->annual_leave_balance < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if($r1a->annual_leave_balance < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if($r1a->annual_leave_balance < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} else {
						if($r1a->annual_leave_balance < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
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
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} else {
						if((($r2->mc_leave_balance) + $hrleave->period_day) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				}
			}
			// change leave to MC from OTHERS (AL/EL-AL, ML, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				$r2a = $user->hasmanyleavemc()->where('year', $t)->first();
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r2a->mc_leave_balance) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if(($r2a->mc_leave_balance) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r2a->mc_leave_balance) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} else {
						if(($r2a->mc_leave_balance) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
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
					$request->session()->forget('hrleave');
					return redirect()->back();
				}
			}

			// change leave to ML from OTHERS (AL/EL-AL, MC, NRL/EL-NRL)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				$r3a = $user->hasmanyleavematernity()->where('year', $t)->first();
				if(($r3a->maternity_leave_balance) < $totalday){
					Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					$request->session()->forget('hrleave');
					return redirect()->back();
				}
			}
		}

		// change leave to NRL/EL-NRL
		if ($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// change leave to NRL/EL-NRL from NRL/EL-NRL
// dd($request->all());
			// using new replacement leave
			$r4a = HRLeaveReplacement::find($request->id);

			if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {
				// careful, we've got 2 condition
				// 1. using old nrl entitlement
				// 2. using new nrl entitlement
				// so if using old entitlement
				if (!$request->has('id')) {
					if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
						if($request->leave_cat == 2){																										// half day
							if((($r4->leave_balance) + $hrleave->period_day) < 0.5){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						} elseif($request->leave_cat == 1) {
							if((($r4->leave_balance) + $hrleave->period_day) < 1){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if((($r4->leave_balance) + $hrleave->period_day) < $totalday){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						} else {
							if((($r4->leave_balance) + $hrleave->period_day) < $totaldayfiltered){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
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
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						} elseif($request->leave_cat == 1) {
							if($r4a->leave_balance < 1){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						}
					} else {																																// applied for more than 1 day
						if ($noOverlap) {
							if($r4a->leave_balance < $totalday){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						} else {
							if($r4a->leave_balance < $totaldayfiltered){
								Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
								$hrleave->update(['leave_status_id' => $b4leavestatus]);
								$request->session()->forget('hrleave');
								return redirect()->back();
							}
						}
					}
				}
			}

			// change leave to NRL/EL-NRL from OTHERS (AL/EL-AL, ML, MC)
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || ($hrleave->leave_type_id == 7) || ($hrleave->leave_type_id == 2) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12)) {
				if ($request->has('leave_cat')) {																										// applied for 1 full day OR half day
					if($request->leave_cat == 2){																										// half day
						if(($r4a->leave_balance) < 0.5){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} elseif($request->leave_cat == 1) {
						if(($r4a->leave_balance) < 1){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				} else {																																// applied for more than 1 day
					if ($noOverlap) {
						if(($r4a->leave_balance) < $totalday){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					} else {
						if(($r4a->leave_balance) < $totaldayfiltered){
							Session::flash('flash_danger', 'Please make sure applied leave does not exceed available leave balance');
							$hrleave->update(['leave_status_id' => $b4leavestatus]);
							$request->session()->forget('hrleave');
							return redirect()->back();
						}
					}
				}
			}
		}

		// change leave to TF from TF
		if ($request->leave_type_id == 9) {
			// convert $request->time_start and $request->time_end to mysql format
			$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
			$te = Carbon::parse($request->date_time_start.' '.$request->time_end);
			// dd($ts, $te);

			// change leave to MC from TF
			if ($hrleave->leave_type_id == 9)
			{
				if ( $ts->gte($te) ) { // time start less than time end
					Session::flash('flash_danger', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					$request->session()->forget('hrleave');
					return redirect()->back();
				}
			}

			// change leave to TF from OTHERS
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || $hrleave->leave_type_id == 2 || $hrleave->leave_type_id == 7 || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 9 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12))
			{
				if ( $ts->gte($te) ) { // time start less than time end
					Session::flash('flash_danger', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
					$hrleave->update(['leave_status_id' => $b4leavestatus]);
					$request->session()->forget('hrleave');
					return redirect()->back();
				}
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
				$request->session()->forget('hrleave');
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
				$request->session()->forget('hrleave');
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
				$request->session()->forget('hrleave');
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

				// $hrleave->belongstomanyleaveannual()?->detach($r1?->id);
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
				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
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

				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
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
				if (!$request->has('id')) {
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

				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
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

				// from user input
				$timep = CarbonPeriod::create($ts, '1 minutes', $te, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);

				// convert minutes to hours and minutes
				$hour = floor($timep->count()/60);
				$minute = ($timep->count() % 60);
				$t = $hour.':'.$minute.':00';
				// echo $t;

				$data = $request->only(['leave_type_id']);
				$data += ['reason' => ucwords(Str::lower($request->reason))];
				$data += ['date_time_start' => $ts];
				$data += ['date_time_end' => $te];
				$data += ['period_time' => $t];
				if ($request->file('document')) {
					$file = $request->file('document')->getClientOriginalName();
					$currentDate = Carbon::now()->format('Y-m-d His');
					$fileName = $currentDate . '_' . $file;
					$request->document->storeAs('public/leaves', $fileName);
					$data += ['softcopy' => $fileName];
				}
				$hrleave->update($data);
			}

			// change leave to TF from OTHERS
			if (($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) || $hrleave->leave_type_id == 2 || $hrleave->leave_type_id == 7 || ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) || ($hrleave->leave_type_id == 3 || $hrleave->leave_type_id == 6 || $hrleave->leave_type_id == 11 || $hrleave->leave_type_id == 12))
			{
				// detach all entitlement especially for AL/EL-AL, MC, ML, NRL/EL-NRL
				$hrleave->belongstomanyleaveannual()?->detach($r1?->id);
				$hrleave->belongstomanyleavemc()?->detach($r2?->id);
				$hrleave->belongstomanyleavematernity()?->detach($r3?->id);
				$hrleave->belongstomanyleavereplacement()?->detach($r4?->id);

				// convert $request->time_start and $request->time_end to mysql format
				$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
				$te = Carbon::parse($request->date_time_start.' '.$request->time_end);

				// from user input
				$timep = CarbonPeriod::create($ts, '1 minutes', $te, \Carbon\CarbonPeriod::EXCLUDE_START_DATE);
				// echo $timep->count().' tempoh minit masa keluar sblm tolak recess<br />';
				// dd($timep->count());

				// convert minutes to hours and minutes
				$hour = floor($timep->count()/60);
				$minute = ($timep->count() % 60);
				$t = $hour.':'.$minute.':00';
				// echo $t;

				$data = $request->only(['leave_type_id']);
				$data += ['reason' => ucwords(Str::lower($request->reason))];
				$data += ['date_time_start' => $ts];
				$data += ['date_time_end' => $te];
				$data += ['period_time' => $t];
				$data += ['period_day' => NULL];
				$data += ['leave_cat' => NULL];
				$data += ['half_type_id' => NULL];
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

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if ($hrleave->hasmanyleaveamend()->count()) {
			foreach (HRLeaveAmend::where('leave_id', $hrleave->id)->get() as $v) {
				HRLeaveAmend::find($v->id)->update([
														'amend_note' => ucwords(Str::lower($v->amend_note)).'<br />'.ucwords(Str::lower($request->amend_note)),
														'staff_id' => \Auth::user()->belongstostaff->id,
														'date' => now()
				]);
			}
		} else {
			$hrleave->hasmanyleaveamend()->create([
												'amend_note' => ucwords(Str::lower($request->amend_note)),
												'staff_id' => \Auth::user()->belongstostaff->id,
												'date' => now()
			]);
		}

		$b = HRAttendance::where('leave_id', $hrleave->id)->get();
		if ($b->count()) {
			foreach ($b as $c) {
				HRAttendance::where('id', $c->id)->update(['leave_id' => null]);
			}
		}
		$hrleave->update(['leave_status_id' => $b4leavestatus]);
		$request->session()->forget('hrleave');
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

