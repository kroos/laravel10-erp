<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;


class LeaveController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);								// all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);		// only hod and asst hod HR can access
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
		dd($request->all());
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// initial setup for create a leave
		$user = \Auth::user()->belongstostaff;																			// for specific user
		$daStart = Carbon::parse($request->date_time_start);															// date start : for manipulation

		if( empty( $request->date_time_end ) ) {																		// in time off, there only date_time_start so...
			$request->date_time_end = $request->date_time_start;
		}

		$row = HRLeave::whereYear('date_time_start', $request->date_time_start)->get()->count();						// count rows for particular year based on $request->date_time_start
		$row += 1;
		$ye = $daStart->format('y');																					// strip down to 2 digits

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// if a user select more than 1 day and setting double date is on, we need to count the remaining day that is not overlapping
		$blockdate = UnavailableDateTime::blockDate(\Auth::user()->belongstostaff->id);

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

		// return $filtered;
		// return $totaldayfiltered;
		// exit;

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
		// return $dateStartEnd;
		// exit;

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
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleaveannual()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_danger', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->annual_leave_balance >= 0.5){																							// annual_leave_balance > 0.5

						$entitle = $entitlement->annual_leave_balance - 0.5;
						$utilize = $entitlement->annual_leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'reason']);
						$data += ['verify_code' => $code];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d H:i:s');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleaveannual()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);		// update leave_balance by substarct
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																								// backup only valid for non EL leave
								$l->hasmanyleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasmanyleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasmanyleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasmanyleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasmanyleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
						return redirect()->back();
					}
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					if($entitlement->annual_leave_balance >= 1){																								// annual_leave_balance >= 1
						$entitle = $entitlement->annual_leave_balance - 1;
						$utilize = $entitlement->annual_leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d H:i:s');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleaveannual()->attach($entitlement->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);// update leave_balance by substarct

						//can make a shortcut like this also
						// shortcut to update hr_leave_annual
						// not working
						// $c = $l->belongstomanyleaveannual()->attach($entitlement->id);
						// dd($c);
						// $c->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);

						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasmanyleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasmanyleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasmanyleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasmanyleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasmanyleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					if($entitlement->annual_leave_balance >= $totalday) {																						// annual_leave_balance > $totalday
						$entitle = $entitlement->annual_leave_balance - $totalday;
						$utilize = $entitlement->annual_leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$file = $request->file('document')->getClientOriginalName();
							$currentDate = Carbon::now()->format('Y-m-d H:i:s');
							$fileName = $currentDate . '_' . $file;
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleaveannual()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);		// update leave_balance by substarct
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasmanyleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasmanyleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasmanyleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasmanyleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasmanyleaveapprovalhr()->create();
						}
					} else {																														// annual_leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
						return redirect()->back();
					}
				} else {																															// false: date choose overlapping date with unavailable date
					if($entitlement->annual_leave_balance >= $totaldayfiltered) {																	// annual_leave_balance > $totaldayfiltered
						$entitle = $entitlement->annual_leave_balance - $totaldayfiltered;
						$utilize = $entitlement->annual_leave_utilize + $totaldayfiltered;

						foreach ($dateStartEnd as $value) {																// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
							if($request->file('document')){
								$file = $request->file('document')->getClientOriginalName();
								$currentDate = Carbon::now()->format('Y-m-d H:i:s');
								$fileName = $currentDate . '_' . $file;
								// Store File in Storage Folder
								$request->document->storeAs('uploads', $fileName);
								// storage/app/uploads/file.png
								// Store File in Public Folder
								// $request->document->move(public_path('uploads'), $fileName);
								// public/uploads/file.png
								$data = [
									'date_time_start' => $value['date_time_start'],
									'date_time_end' => $value['date_time_end'],
									'verify_code' => $code,
									'period_day' => 1,
									'leave_no' => $row++,
									'leave_year' => $ye,
									'leave_type_id' => $request->leave_type_id,
									'reason' => $request->reason,
									'softcopy' => $fileName
								];
							} else {
								$data = [
									'date_time_start' => $value['date_time_start'],
									'date_time_end' => $value['date_time_end'],
									'verify_code' => $code,
									'period_day' => 1,
									'leave_no' => $row++,
									'leave_year' => $ye,
									'leave_type_id' => $request->leave_type_id,
									'reason' => $request->reason
								];
							}

							$l = $user->hasmanyleave()->create($data);																				// insert data into HRLeave
							$l->belongstomanyleaveannual()->attach($entitlement->id);									// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php

							if($user->belongstoleaveapprovalflow->backup_approval == 1){															// alert backup
								if($request->staff_id) {																							// backup only valid for non EL leave
									$l->hasmanyleaveapprovalbackup()->create($request->only(['staff_id']));
								}
							}
							if($user->belongstoleaveapprovalflow->supervisor_approval == 1){														// alert supervisor
								$l->hasmanyleaveapprovalsupervisor()->create();
							}
							if($user->belongstoleaveapprovalflow->hod_approval == 1){																// alert hod
								$l->hasmanyleaveapprovalhod()->create();
							}
							if($user->belongstoleaveapprovalflow->director_approval == 1){															// alert director
								$l->hasmanyleaveapprovaldir()->create();
							}
							if($user->belongstoleaveapprovalflow->hr_approval == 1){																// alert hr
								$l->hasmanyleaveapprovalhr()->create();
							}
						}
						$user->hasmanyleaveannual()->where('year', $daStart->year)->update(['annual_leave_balance' => $entitle, 'annual_leave_utilize' => $utilize]);		// update leave_balance by substarct
					} else {																														// annual_leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
						return redirect()->back();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// UPL & EL-UPL & MC-UPL

	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hrleave): RedirectResponse
	{
		//
	}
}
