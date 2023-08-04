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
			$entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->al_balance >= 0.5){																							// al_balance > 0.5

						$entitle = $entitlement->al_balance - 0.5;
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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['al_balance' => $entitle]);						// update al_balance by substarct
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					if($entitlement->al_balance >= 1){																								// al_balance >= 1
						$entitle = $entitlement->al_balance - 1;

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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['al_balance' => $entitle]);						// substract al_balance
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					if($entitlement->al_balance >= $totalday) {																						// al_balance > $totalday
						$entitle = $entitlement->al_balance - $totalday;

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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['al_balance' => $entitle]);						// substract al_balance
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {																														// al_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					if($entitlement->al_balance >= $totaldayfiltered) {																				// al_balance > $totaldayfiltered
						$entitle = $entitlement->al_balance - $totaldayfiltered;

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
									$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
								}
							}
							if($user->belongstoleaveapprovalflow->supervisor_approval == 1){														// alert supervisor
								$l->hasoneleaveapprovalsupervisor()->create();
							}
							if($user->belongstoleaveapprovalflow->hod_approval == 1){																// alert hod
								$l->hasoneleaveapprovalhod()->create();
							}
							if($user->belongstoleaveapprovalflow->director_approval == 1){															// alert director
								$l->hasoneleaveapprovaldir()->create();
							}
							if($user->belongstoleaveapprovalflow->hr_approval == 1){																// alert hr
								$l->hasoneleaveapprovalhr()->create();
							}
						}
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['al_balance' => $entitle]);						// substract al_balance
					} else {																														// al_balance < $totalday, then exit
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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
									$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
								}
							}
							if($user->belongstoleaveapprovalflow->supervisor_approval == 1){														// alert supervisor
								$l->hasoneleaveapprovalsupervisor()->create();
							}
							if($user->belongstoleaveapprovalflow->hod_approval == 1){																// alert hod
								$l->hasoneleaveapprovalhod()->create();
							}
							if($user->belongstoleaveapprovalflow->director_approval == 1){															// alert director
								$l->hasoneleaveapprovaldir()->create();
							}
							if($user->belongstoleaveapprovalflow->hr_approval == 1){																// alert hr
								$l->hasoneleaveapprovalhr()->create();
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
			$entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
			// if(!$entitlement) {																													// kick him out if there is no entitlement been configured for entitlement
			// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
			// 	return redirect()->back();
			// }

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->mc_balance >= 0.5){																							// mc_balance > 0.5

						$entitle = $entitlement->mc_balance - 0.5;
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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// update mc_balance by substarct
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} elseif($request->leave_type == 1) {																								// apply leace 1 whole day
					if($entitlement->mc_balance >= 1){																								// mc_balance >= 1
						$entitle = $entitlement->mc_balance - 1;

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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			} else {																																// apply leave for 2 OR more days
				if ($noOverlap) {																													// true: date choose not overlapping date with unavailable date
					if($entitlement->mc_balance >= $totalday) {																						// mc_balance > $totalday
						$entitle = $entitlement->mc_balance - $totalday;

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
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
						if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
							if($request->staff_id) {																						// backup only valid for non EL leave
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
						}
					} else {																														// mc_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				} else {
					// since date_time_start and date_time_end overlapping with block date, need to iterate date by date																															// false: date choose overlapping date with unavailable date
					if($entitlement->mc_balance >= $totaldayfiltered) {																				// mc_balance > $totaldayfiltered
						$entitle = $entitlement->mc_balance - $totaldayfiltered;

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
									$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
								}
							}
							if($user->belongstoleaveapprovalflow->supervisor_approval == 1){														// alert supervisor
								$l->hasoneleaveapprovalsupervisor()->create();
							}
							if($user->belongstoleaveapprovalflow->hod_approval == 1){																// alert hod
								$l->hasoneleaveapprovalhod()->create();
							}
							if($user->belongstoleaveapprovalflow->director_approval == 1){															// alert director
								$l->hasoneleaveapprovaldir()->create();
							}
							if($user->belongstoleaveapprovalflow->hr_approval == 1){																// alert hr
								$l->hasoneleaveapprovalhr()->create();
							}
						}
						$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['mc_balance' => $entitle]);						// substract mc_balance
					} else {																														// mc_balance < $totalday, then exit
						Session::flash('flash_message', 'Please check your entitlement based on the date leave you apply');
						return redirect()->back();
					}
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// NRL & EL-NRL
		if($request->leave_type_id == 4 || $request->leave_type_id == 10) {

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// ML
		if($request->leave_type_id == 7) {
			$period = Carbon::parse($request->date_time_start)->daysUntil($request->date_time_end);										// 
			if(count($period) == 60){
				// check entitlement if configured or not
				$entitlement = $user->hasmanyleaveentitlement()->where('year', $daStart->year)->first();
				// if(!$entitlement) {																									// kick him out if there is no entitlement been configured for entitlement
				// 	Session::flash('flash_message', 'Please contact with your Human Resources Manager. Most probably, HR havent configured yet your entitlement.');
				// 	return redirect()->back();
				// }
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
				$user->hasmanyleaveentitlement()->where('year', $daStart->year)->update(['maternity_balance' => 0]);						// substract maternity_balance
				if($user->belongstoleaveapprovalflow->backup_approval == 1){																// alert backup
					if($request->staff_id) {																								// backup only valid for non EL leave
						$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
					}
				}
				if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
					$l->hasoneleaveapprovalsupervisor()->create();
				}
				if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
					$l->hasoneleaveapprovalhod()->create();
				}
				if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
					$l->hasoneleaveapprovaldir()->create();
				}
				if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
					$l->hasoneleaveapprovalhr()->create();
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// TF
		if($request->leave_type_id == 9) {

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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
								$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$l->hasoneleaveapprovalsupervisor()->create();
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$l->hasoneleaveapprovalhod()->create();
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$l->hasoneleaveapprovaldir()->create();
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$l->hasoneleaveapprovalhr()->create();
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
									$l->hasoneleaveapprovalbackup()->create($request->only(['staff_id']));
								}
							}
							if($user->belongstoleaveapprovalflow->supervisor_approval == 1){														// alert supervisor
								$l->hasoneleaveapprovalsupervisor()->create();
							}
							if($user->belongstoleaveapprovalflow->hod_approval == 1){																// alert hod
								$l->hasoneleaveapprovalhod()->create();
							}
							if($user->belongstoleaveapprovalflow->director_approval == 1){															// alert director
								$l->hasoneleaveapprovaldir()->create();
							}
							if($user->belongstoleaveapprovalflow->hr_approval == 1){																// alert hr
								$l->hasoneleaveapprovalhr()->create();
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
