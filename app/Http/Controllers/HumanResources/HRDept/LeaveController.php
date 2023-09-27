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
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

use \App\Helpers\UnavailableDateTime;


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
		// dd([$request->all(), $hrleave->leave_type_id]);
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

		if( empty( $request->date_time_end ) ) {																		// in time off, there only date_time_start so...
			$request->date_time_end = $request->date_time_start;
		}

		$ye = $daStart->copy()->format('y');																			// strip down to 2 digits

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// if a user select more than 1 day and setting double date is on, we need to count the remaining day that is not overlapping
		$blockdate = UnavailableDateTime::blockDate($user->id);

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
		if ($hrleave->leave_type_id == 1 || $hrleave->leave_type_id == 5) {												// give back all to AL & EL-AL
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
							'annual_leave_utilize' => $newbalance,
							'annual_leave_balance' => $newutilize,
						]);
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 2) {																				// give back all to MC
			if (!$r2) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
				return redirect()->back()->withInput();
			}
			// $r2 = HRLeaveMC::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r2->mc_leave_utilize;
			$balance = $r2->mc_leave_balance;
			$total = $r2->mc_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r2->update([
							'mc_leave_utilize' => $newbalance,
							'mc_leave_balance' => $newutilize,
						]);
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 7) {																				// give back all to ML
			if (!$r3) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
				return redirect()->back()->withInput();
			}
			// $r3 = HRLeaveMaternity::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r3->maternity_leave_utilize;
			$balance = $r3->maternity_leave_balance;
			$total = $r3->maternity_leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r3->update([
							'maternity_leave_utilize' => $newbalance,
							'maternity_leave_balance' => $newutilize,
						]);
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		if ($hrleave->leave_type_id == 4 || $hrleave->leave_type_id == 10) {											// give back all to NRL & EL-NRL
			if (!$r4) {
				Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
				return redirect()->back()->withInput();
			}
			// $r4 = HRLeaveReplacement::where([['staff_id', $hrleave->staff_id],['year', $t]])->first();
			$utilize = $r4->leave_utilize;
			$balance = $r4->leave_balance;
			$total = $r4->leave;
			$newutilize = $utilize - $hrleave->period_day;
			$newbalance = $balance + $hrleave->period_day;

			$r4->update([
							'leave_utilize' => $newbalance,
							'leave_balance' => $newutilize,
						]);
			// $hrleave->update(['period_day' => 0, 'period_time' => '00:00:00']);
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if($request->leave_type_id == 1 || $request->leave_type_id == 5) {


			// check entitlement if configured or not
			$entitlement = $user->hasmanyleaveannual()->where('year', $daStart->copy()->year)->first();

			if ($request->has('leave_type')) {																										// applied for 1 full day OR half day
				if($request->leave_type == 2){																										// half day
					if($entitlement->annual_leave_balance >= 0.5){																							// annual_leave_balance > 0.5

						$entitle = $entitlement->annual_leave_balance - 0.5;
						$utilize = $entitlement->annual_leave_utilize + 0.5;
						$time = explode( '/', $request->half_type_id );

						$data = $request->only(['leave_type_id', 'reason']);
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['half_type_id' => $time[0]];
						$data += ['date_time_start' => $request->date_time_start.' '.$time[1]];
						$data += ['date_time_end' => $request->date_time_end.' '.$time[2]];
						$data += ['period_day' => 0.5];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $hrleave->leave_year];
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
								$hrleave->hasmanyleaveapprovalbackup()->update(['leave_id' => $l->id]);
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$hrleave->hasmanyleaveapprovalsupervisor()->update(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$hrleave->hasmanyleaveapprovalhod()->update(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$hrleave->hasmanyleaveapprovaldir()->update(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$hrleave->hasmanyleaveapprovalhr()->update(['leave_id' => $l->id]);
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
						$data += ['verify_code' => $hrleave->verify_code];
						$data += ['period_day' => 1];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $hrleave->leave_year];
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
								$hrleave->hasmanyleaveapprovalbackup()->create(['leave_id' => $l->id]);
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$hrleave->hasmanyleaveapprovalsupervisor()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$hrleave->hasmanyleaveapprovalhod()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$hrleave->hasmanyleaveapprovaldir()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$hrleave->hasmanyleaveapprovalhr()->create(['leave_id' => $l->id]);
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
						$data += ['verify_code' => $hrleave->leave_verify_code];
						$data += ['period_day' => $totalday];
						$data += ['leave_no' => $hrleave->leave_no];
						$data += ['leave_year' => $hrleave->leave_year];
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
								$hrleave->hasmanyleaveapprovalbackup()->create(['leave_id' => $l->id]);
							}
						}
						if($user->belongstoleaveapprovalflow->supervisor_approval == 1){															// alert supervisor
							$hrleave->hasmanyleaveapprovalsupervisor()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hod_approval == 1){																	// alert hod
							$hrleave->hasmanyleaveapprovalhod()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->director_approval == 1){																// alert director
							$hrleave->hasmanyleaveapprovaldir()->create(['leave_id' => $l->id]);
						}
						if($user->belongstoleaveapprovalflow->hr_approval == 1){																	// alert hr
							$hrleave->hasmanyleaveapprovalhr()->create(['leave_id' => $l->id]);
						}
					} else {																														// annual_leave_balance < $totalday, then exit
						Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
						return redirect()->back();
					}
				}
			}
		}
































		// finally, we delete the leave
		$hrleave->delete();
		Session::flash('flash_message', 'Successfully edit leave');
		return redirect()->route('hrleave.show', $l->id);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hrleave): RedirectResponse
	{
		//
	}
}
