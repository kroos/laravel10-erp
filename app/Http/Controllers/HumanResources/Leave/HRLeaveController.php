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
use App\Helpers\UnavailableDateTime;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class HRLeaveController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		// $this->middleware('leaveaccess', ['only' => ['show', 'edit', 'update']]);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['edit', 'update', 'destroy']]);
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
		// return $request->all();
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// initial setup for create a leave
		$user = \Auth::user()->belongstostaff;																										// for specific user
		$daStart = Carbon::parse($request->date_time_start);																						// date start : for manipulation

		if( empty( $request->date_time_end ) ) {																									// in time off, there only date_time_start so...
			$request->date_time_end = $request->date_time_start;
		}

		$row = HRLeave::whereYear('date_time_start', $request->date_time_start)->get()->count();													// count rows for particular year based on $request->date_time_start
		$row += 1;
		$ye = $daStart->format('y');																												// strip down to 2 digits

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
		$filtered = array_diff($lea, $leav);																										// get all the dates that is not overlapped
		$totaldayfiltered = count($filtered);																										// total days

		// return $filtered;
		// return $totaldayfiltered;
		// exit;

		$dateStartEnd = [];
		if($totalday == $totaldayfiltered){
			$noOverlap = true;																														// meaning we CAN take $request->date_time_end $request->date_time_start as is to be insert in database
		} else {
			$noOverlap = false;																														// meaning we CANT take $request->date_time_end $request->date_time_start as is to be insert in database, instead we need to separate it row by row to be inserted into database.
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
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
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
							$fileName = $request->file('document')->getClientOriginalName();
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
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
							$fileName = $request->file('document')->getClientOriginalName();
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
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
							$fileName = $request->file('document')->getClientOriginalName();
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} else {																															// false: date choose overlapping date with unavailable date
					if($entitlement->annual_leave_balance >= $totaldayfiltered) {																	// annual_leave_balance > $totaldayfiltered
						$entitle = $entitlement->annual_leave_balance - $totaldayfiltered;
						$utilize = $entitlement->annual_leave_utilize + $totaldayfiltered;

						foreach ($dateStartEnd as $value) {																// since date_time_start and date_time_end overlapping with block date, need to iterate date by date
							if($request->file('document')){
								$fileName = $request->file('document')->getClientOriginalName();
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// UPL & EL-UPL & MC-UPL
		if($request->leave_type_id == 3 || $request->leave_type_id == 6 || $request->leave_type_id == 11) {
			// check entitlement if configured or not
			// $entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					// if($entitlement->mc_balance >= 0.5){																							// mc_balance > 0.5

						// $entitle = $entitlement->mc_balance - 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'reason']);
						$data += ['half_type_id' => $time[0]];
						$data += ['verify_code' => $code];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// update mc_balance by substarct
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
					// } else {
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					// if($entitlement->mc_balance >= 1){																								// mc_balance >= 1
						// $entitle = $entitlement->mc_balance - 1;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
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
					// } else {
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					// if($entitlement->mc_balance >= $totalday) {																						// mc_balance > $totalday
						// $entitle = $entitlement->mc_balance - $totalday;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
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
					// } else {																														// mc_balance < $totalday, then exit
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					// if($entitlement->mc_balance >= $totaldayfiltered) {																				// mc_balance > $totaldayfiltered
						// $entitle = $entitlement->mc_balance - $totaldayfiltered;

						foreach ($dateStartEnd as $value) {
							if($request->file('document')){
								$fileName = $request->file('document')->getClientOriginalName();
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
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
					// } else {																														// mc_balance < $totalday, then exit
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// MC
		if($request->leave_type_id == 2) {
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleavemc()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->mc_leave_balance >= 0.5){																							// mc_leave_balance > 0.5

						$entitle = $entitlement->mc_leave_balance - 0.5;
						$utilize = $entitlement->mc_leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'reason']);
						$data += ['half_type_id' => $time[0]];
						$data += ['verify_code' => $code];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					if($entitlement->mc_leave_balance >= 1){																								// mc_leave_balance >= 1
						$entitle = $entitlement->mc_leave_balance - 1;
						$utilize = $entitlement->mc_leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					if($entitlement->mc_leave_balance >= $totalday) {																				// mc_leave_balance > $totalday
						$entitle = $entitlement->mc_leave_balance - $totalday;
						$utilize = $entitlement->mc_leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavemc()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct
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
					} else {																														// mc_leave_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					if($entitlement->mc_leave_balance >= $totaldayfiltered) {																				// mc_leave_balance > $totaldayfiltered
						$entitle = $entitlement->mc_leave_balance - $totaldayfiltered;
						$utilize = $entitlement->mc_leave_utilize + $totaldayfiltered;

						foreach ($dateStartEnd as $value) {
							if($request->file('document')){
								$fileName = $request->file('document')->getClientOriginalName();
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
							$l->belongstomanyleavemc()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
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
						$user->hasmanyleavemc()->where('year', $daStart->year)->update(['mc_leave_balance' => $entitle, 'mc_leave_utilize' => $utilize]);		// update leave_balance by substarct
					} else {																														// mc_leave_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// NRL & EL-NRL
		if($request->leave_type_id == 4 || $request->leave_type_id == 10) {
			// return $request->all();
			// exit;
			// check entitlement if configured or not
			$entitlement = $user->hasmanyleavereplacement()->where('id', $request->id)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->leave_balance >= 0.5){																							// leave_balance > 0.5

						$entitle = $entitlement->leave_balance - 0.5;
						$utilize = $entitlement->leave_utilize + 0.5;
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
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($request->id);					// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavereplacement()->where('id', $request->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					if($entitlement->leave_balance >= 1){																							// leave_balance >= 1
						$entitle = $entitlement->leave_balance - 1;
						$utilize = $entitlement->leave_utilize + 1;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['half_type_id' => $time[0]];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($request->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavereplacement()->where('id', $request->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct
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
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					if($entitlement->leave_balance >= $totalday) {																					// leave_balance > $totalday
						$entitle = $entitlement->leave_balance - $totalday;
						$utilize = $entitlement->leave_utilize + $totalday;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						$l->belongstomanyleavereplacement()->attach($request->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
						$user->hasmanyleavereplacement()->where('id', $request->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct
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
					} else {																														// leave_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					if($entitlement->leave_balance >= $totaldayfiltered) {																				// leave_balance > $totaldayfiltered
						$entitle = $entitlement->leave_balance - $totaldayfiltered;
						$utilize = $entitlement->leave_utilize + $totaldayfiltered;

						foreach ($dateStartEnd as $value) {
							if($request->file('document')){
								$fileName = $request->file('document')->getClientOriginalName();
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
							$l->belongstomanyleavereplacement()->attach($request->id);									// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
							$user->hasmanyleavereplacement()->where('id', $request->id)->update(['leave_balance' => $entitle, 'leave_utilize' => $utilize]);		// update leave_balance by substarct
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
						$user->hasmanyleavereplacement()->where('id', $request->leave_replacement_id)->update(['leave_balance' => $entitle]);		// substract leave_balance
					} else {																														// leave_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// ML
		if($request->leave_type_id == 7) {
			$entitlement = $user->hasmanyleavematernity()->where('year', $daStart->year)->first();										// check entitlement if configured or not
			if($entitlement->maternity_leave_balance >= $totalday) {
				// if(!$entitlement) {																									// kick him out if there is no entitlement been configured for entitlement
				// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
				// 	return redirect()->back();
				// }
				$entitle = $entitlement->maternity_leave_balance - $totalday;
				$utilize = $entitlement->maternity_leave_utilize + $totalday;
				$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
				$data += ['verify_code' => $code];
				$data += ['period_day' => $totalday];
				$data += ['leave_no' => $row];
				$data += ['leave_year' => $ye];
				if($request->file('document')){
					$fileName = $request->file('document')->getClientOriginalName();
					// Store File in Storage Folder
					$request->document->storeAs('uploads', $fileName);
					// storage/app/uploads/file.png
					// Store File in Public Folder
					// $request->document->move(public_path('uploads'), $fileName);
					// public/uploads/file.png
					$data += ['softcopy' => $fileName];
				}

				$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
				$l->belongstomanyleavematernity()->attach($entitlement->id);										// it should be leave_replacement_id but im lazy to change it at view humanresources/create.blade.php
				$user->hasmanyleavematernity()->where('year', $daStart->year)->update(['maternity_leave_balance' => $entitle, 'maternity_leave_utilize' => $utilize]);		// update leave_balance by substarct

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
				Session::flash('flash_message', 'It seems you haven taken your maternity leave. Please check with your HR');
				return redirect()->route('leave.create')->withInput();
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// TF
		if($request->leave_type_id == 9) {
			// convert $request->time_start and $request->time_end to mysql format
			$ts = Carbon::parse($request->date_time_start.' '.$request->time_start);
			$te = Carbon::parse($request->date_time_start.' '.$request->time_end);

			if ( $ts->gte($te) ) { // time start less than time end
				Session::flash('flash_message', 'Your Time Off application can\'t be processed due to your selection time ('.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_start)->format('D, j F Y h:i A').' untill '.\Carbon\Carbon::parse($request->date_time_start.' '.$request->time_end)->format('D, j F Y h:i A').') . Please choose time correctly.');
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
			$whtime = UnavailableDateTime::workinghourtime($request->date_time_start, \Auth::user()->belongstostaff->id);
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
			// return [$timeoverlap, $timeoverlapcount];

			if ( $timeoverlapcount > 125 ) { // minutes over than 2 hours with contingency
				Session::flash('flash_message', 'Your Time Off exceeded more than 2 hours. Please select time correctly.');
				return redirect()->back()->withInput();
			}

			// convert minutes to hours and minutes
			$hour = floor($timeoverlapcount/60);
			$minute = ($timeoverlapcount % 60);
			$t = $hour.':'.$minute.':00';
			// echo $t;

			$data = $request->only(['leave_type_id', 'reason']);
			$data += ['verify_code' => $code];
			$data += ['date_time_start' => $request->date_time_start];
			$data += ['date_time_end' => $request->date_time_start];
			$data += ['period_time' => $t];
			$data += ['leave_no' => $row];
			$data += ['leave_year' => $ye];
			if($request->file('document')){
				$fileName = $request->file('document')->getClientOriginalName();
				// Store File in Storage Folder
				$request->document->storeAs('uploads', $fileName);
				// storage/app/uploads/file.png
				// Store File in Public Folder
				// $request->document->move(public_path('uploads'), $fileName);
				// public/uploads/file.png
				$data += ['softcopy' => $fileName];
			}
			$l = $user->hasmanyleave()->create($data);
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
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// S-UPL
		if($request->leave_type_id == 12) {
			// check entitlement if configured or not
			// $entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					// if($entitlement->mc_balance >= 0.5){																							// mc_balance > 0.5

						// $entitle = $entitlement->mc_balance - 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'reason']);
						$data += ['half_type_id' => $time[0]];
						$data += ['verify_code' => $code];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// update mc_balance by substarct
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
					// } else {
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					// if($entitlement->mc_balance >= 1){																								// mc_balance >= 1
						// $entitle = $entitlement->mc_balance - 1;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end', 'half_type_id']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
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
					// } else {
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					// if($entitlement->mc_balance >= $totalday) {																						// mc_balance > $totalday
						// $entitle = $entitlement->mc_balance - $totalday;

						$data = $request->only(['leave_type_id', 'reason', 'date_time_start', 'date_time_end']);
						$data += ['verify_code' => $code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $row];
						$data += ['leave_year' => $ye];
						if($request->file('document')){
							$fileName = $request->file('document')->getClientOriginalName();
							// Store File in Storage Folder
							$request->document->storeAs('uploads', $fileName);
							// storage/app/uploads/file.png
							// Store File in Public Folder
							// $request->document->move(public_path('uploads'), $fileName);
							// public/uploads/file.png
							$data += ['softcopy' => $fileName];
						}

						$l = $user->hasmanyleave()->create($data);																					// insert data into HRLeave
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
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
					// } else {																														// mc_balance < $totalday, then exit
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					// if($entitlement->mc_balance >= $totaldayfiltered) {																				// mc_balance > $totaldayfiltered
						// $entitle = $entitlement->mc_balance - $totaldayfiltered;

						foreach ($dateStartEnd as $value) {
							if($request->file('document')){
								$fileName = $request->file('document')->getClientOriginalName();
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
						// $user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
					// } else {																														// mc_balance < $totalday, then exit
						// Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						// return redirect()->back();
					// }
				}
			}
		}
		//////////////////////////////////////////////////////////////////////////////
		Session::flash('flash_message', 'Successfully Applied Leave.');
		return redirect()->route('leave.index');
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
