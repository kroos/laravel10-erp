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
		$user = $hrleave->belongstostaff;																				// for specific user
		$daStart = Carbon::parse($request->date_time_start);															// date start : for manipulation

		// start to give back the AL, MC, Maternity & Replacement Leave
		$t = $daStart->copy()->format('Y');

		$r1 = $hrleave->belongstomanyleaveannual()->first();
		$r2 = $hrleave->belongstomanyleavemc()->first();
		$r3 = $hrleave->belongstomanyleavematernity()->first();
		$r4 = $hrleave->belongstomanyleavereplacement()->first();

		// dd($r4, $request->all());

		if( empty( $request->date_time_end ) ) {																		// in time off, there only date_time_start so...
			$request->date_time_end = $request->date_time_start;
		}

		$ye = $daStart->copy()->format('y');																			// strip down to 2 digits

		// $hrleave->update(['leave_status_id' => 3, 'remarks' => 'Edit leave']);

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
		if (!$noOverlap && !($hrleave->period_day == $totalday)) {
			Session::flash('flash_danger', 'Edit leave has duration not equal to the previous leave and/or overlapped with other leave, public holiday and restday');
			return redirect()->back()->withInput();
		}

		if ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) {						// give back all to AL & EL-AL
			if (!$r1) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
				return redirect()->back()->withInput();
			}
			// $r1 = HRLeaveAnnual::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r1->annual_leave_utilize;
			$balance = $r1->annual_leave_balance;
			$total = $r1->annual_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r1->update([																								// update entitlements
							'annual_leave_utilize' => $newutilize,
							'annual_leave_balance' => $newbalance,
						]);
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 2) {																				// give back all to MC
			if (!$r2) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
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
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 7) {																				// give back all to ML
			if (!$r3) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
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
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {											// give back all to NRL & EL-NRL
			if (!$r4) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
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
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}


		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// AL & EL-AL
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

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleaveannual()->attach($entitlement->id);				// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleaveannual()->detach($entitlement->id);
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);// update leave_balance by substarct

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
						$l->belongstomanyleaveannual()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$hrleave->belongstomanyleaveannual()->detach($entitlement->id);
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);// update leave_balance by substarct
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
						$data += ['verify_code' => $hrleave->leave_verify_code];
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

		if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
			if($request->staff_id) {																								// backup only valid for non EL leave
				$bid = $hrleave->hasmanyleaveapprovalbackup()->first()->id;
				HRLeaveApprovalBackup::find($bid)->update(['leave_id' => $l->id]);
			}
		}
		if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
			$sid = $hrleave->hasmanyleaveapprovalsupervisor()->first()->id;
			HRLeaveApprovalSupervisor::find($sid)->update(['leave_id' => $l->id]);
		}
		if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
			$hid = $hrleave->hasmanyleaveapprovalhod()->first()->id;
			HRLeaveApprovalHOD::find($hid)->update(['leave_id' => $l->id]);
		}
		if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
			$did = $hrleave->hasmanyleaveapprovaldir()->first()->id;
			HRLeaveApprovalDirector::find($did)->update(['leave_id' => $l->id]);
		}
		if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
			$rid = $hrleave->hasmanyleaveapprovalhr()->first()->id;
			HRLeaveApprovalHR::find($rid)->update(['leave_id' => $l->id]);
		}
		// finally, we cancelled the leave
		$hrleave->update(['leave_status_id' => 3, 'remarks' => 'Edit leave. period_day = '.$hrleave->period_day.' | period_time = '.$hrleave->period_time, 'period_day' => 0, 'period_time' => '00:00:00']);
		$b = HRAttendance::where('leave_id', $hrleave->id)->get();
		foreach ($b as $c) {
			HRAttendance::where('id', $c->id)->update(['leave_id' => null]);
		}
		Session::flash('flash_message', 'Successfully edit leave. Please check the date of leave at the attendance section for a verification');
		return redirect()->route('hrleave.show', $l->id);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hrleave): JsonResponse
	{
		//
	}
}
