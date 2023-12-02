<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load model
use App\Models\Setting;

use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeaveAmend;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeaveApprovalDirector;
use App\Models\HumanResources\HRLeaveApprovalHOD;
use App\Models\HumanResources\HRLeaveApprovalHR;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;


use App\Models\HumanResources\OptAuthorise;
use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\OptCategory;
use App\Models\HumanResources\OptCountry;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptDepartment;
use App\Models\HumanResources\OptDivision;
use App\Models\HumanResources\OptEducationLevel;
use App\Models\HumanResources\OptGender;
use App\Models\HumanResources\OptHealthStatus;
use App\Models\HumanResources\OptLeaveStatus;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\OptMaritalStatus;
use App\Models\HumanResources\OptRace;
use App\Models\HumanResources\OptRelationship;
use App\Models\HumanResources\OptReligion;
use App\Models\HumanResources\OptRestdayGroup;
use App\Models\HumanResources\OptTaxExemptionPercentage;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\OptWorkingHour;

// load helper
use App\Helpers\UnavailableDateTime;
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Session;

class AjaxController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['deactivatestaff', 'deletecrossbackup', 'staffactivate', 'generateannualleave', 'generatemcleave', 'generatematernityleave', 'uploaddoc']]);	// HOD n asst HOD HR only
		// $this->middleware('highMgmtAccess:1|5,14', ['only' => ['uploaddoc']]);	// HOD HR only
	}

	// cancel leave
	public function leavecancel(Request $request, HRLeave $hrleave): JsonResponse
	{
		if($request->cancel == 3)
		{
			// all of the debugging echo need to be commented out if using ajax.
			// cari leave type dulu
			$n = HRLeave::find($request->id);
			// echo $n.' staff Leave model<br />';
			//////////////////////////////////////////////////////////////////////////////////////////////
			// jom cari leave type, jenis yg boleh tolak shj : al, mc, el-al, el-mc, nrl, ml
			// echo $n->leave_type_id.' leave type<br />';

			$dts = \Carbon\Carbon::parse( $n->date_time_start );
			$now = \Carbon\Carbon::now();

			// find the pivot table
			$p1 = $n->belongstomanyleaveannual()->first();
			$p2 = $n->belongstomanyleavemc()->first();
			$p3 = $n->belongstomanyleavematernity()->first();
			$p4 = $n->belongstomanyleavereplacement()->first();

			// leave deduct from AL or EL-AL
			// make sure to cancel at the approver also #####################################################################
			if ( $n->leave_type_id == 1 || $n->leave_type_id == 5 ) {
				// check pivot table
				if (!$p1) {
					return response()->json([
						'status' => 'error',
						'message' => 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."',
					]);
				}
				// cari al dari staffleave dan tambah balik masuk dalam hasmanyleaveannual

				// cari period cuti
				// echo $n->period_day.' period cuti<br />';

				// cari al dari staff, year yg sama dgn date apply cuti.
				// echo $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_balance.' applicant annual leave balance<br />';

				$addl = $n->period_day + $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_balance;
				$addu = $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_utilize - $n->period_day;
				// echo $addl.' masukkan dalam annual balance<br />';

				// update the al balance
				$n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->update([
					'annual_leave_balance' => $addl,
					'annual_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name.' reference hr_leaves.id'.$request->id
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				$n->belongstomanyleaveannual()->detach($p1->id);
			}

			if( $n->leave_type_id == 2 ) { // leave deduct from MC
				// check pivot table
				if (!$p2) {
					return response()->json([
						'status' => 'error',
						'message' => 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."',
					]);
				}
				// sama lebih kurang AL mcm kat atas. so....
				$addl = $n->period_day + $n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->first()->mc_leave_balance;
				$addu = $n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->first()->mc_leave_utilize - $n->period_day;
				// update the mc balance
				$n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->update([
					'mc_leave_balance' => $addl,
					'mc_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				$n->belongstomanyleavemc()->detach($p2->id);
			}

			if( $n->leave_type_id == 3 || $n->leave_type_id == 6 || $n->leave_type_id == 11  || $n->leave_type_id == 12 ) { // leave deduct from UPL, EL-UPL, MC-UPL & S-UPL
				// echo 'leave deduct from UPL<br />';

				// process a bit different from al and mc
				// we can ignore all the data in hasmanyleaveentitlement mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				// update status for all approval
			}

			if( $n->leave_type_id == 4 || $n->leave_type_id == 10 ) { // leave deduct from NRL & EL-NRL
				if (!$p4) {
					return response()->json([
						'status' => 'error',
						'message' => 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."',
					]);
				}
				// echo 'leave deduct from NRL<br />';

				// cari period cuti
				// echo $n->period_day.' period cuti<br />';

				// echo $n->hasmanyleavereplacement()->first().' staffleavereplacement model<br />';
				// hati2 pasai ada 2 kes dgn period, full and half day
				// kena update balik di staffleavereplacement model utk return back period.
				// period campur balik dgn leave utilize (2 table berbeza)
				// echo $n->hasmanyleavereplacement()->first()->leave_utilize.' leave utilize<br />';
				// echo $n->hasmanyleavereplacement()->first()->leave_total.' leave total<br />';

				// untuk update di column leave_balance
				$addr = $n->belongstomanyleavereplacement()->first()->leave_balance + $n->period_day;
				$addru = $n->belongstomanyleavereplacement()->first()->leave_utilize - $n->period_day;
				// echo $addr.' untuk update kat column staff_leave_replacement.leave_utilize<br />';

				// update di table staffleavereplacement. remarks kata sapa reject
				$n->belongstomanyleavereplacement()->first()->update([
					// 'leave_type_id' => NULL,
					'leave_balance' => $addr,
					'leave_utilize' => $addru,
					'remarks' => 'Cancelled by '.\Auth::user()->belongstostaff->name,
				]);
				// update di table staff leave pulokk staffleave
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				$n->belongstomanyleavereplacement()->detach($p4->id);
			}

			if( $n->leave_type_id == 7 ) { // leave deduct from ML
				if (!$p3) {
					return response()->json([
						'status' => 'error',
						'message' => 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."',
					]);
				}

				// echo 'leave deduct from ML<br />';

				// lebih kurang sama dengan al atau mc, maka..... :) copy paste
				// cari period cuti
				// echo $n->period.' period cuti<br />';

				// cari al dari applicant, year yg sama dgn date apply cuti.
				// echo $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_balance.' applicant maternity leave balance<br />';

				$addl = $n->period_day + $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_balance;
				$addu = $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_utilize - $n->period_day;

				// echo $addl.' masukkan dalam annual balance<br />';

				// find all approval
				// echo $n->hasmanystaffapproval()->get().'find all approval<br />';

				// echo \Auth::user()->belongstostaff->belongtomanyposition()->wherePivot('main', 1)->first()->position.' position <br />';
				// echo \Auth::user()->belongstostaff->name.' position <br />';

				// update the al balance
				$n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->update([
					'maternity_leave_balance' => $addl,
					'maternity_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name,
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				$n->belongstomanyleavematernity()->detach($p3->id);
			}

			if( $n->leave_type_id == 9 ) { // leave deduct from Time Off
				// echo 'leave deduct from TF<br />';

				// dekat dekat nak sama dgn UPL, maka... :P copy paste

				// process a bit different from al and mc
				// we can ignore all the data in staffannualmcmaternity mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_time' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}
			// finally update at all the approver according to his/her leave flow
			if($n->belongstostaff->belongstoleaveapprovalflow?->backup_approval == 1) {
				$n->hasmanyleaveapprovalbackup()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow?->supervisor_approval == 1) {
				$n->hasoneleaveapprovalsupervisor()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow?->hod_approval == 1) {
				$n->hasoneleaveapprovalhod()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow?->director_approval == 1) {
				$n->hasoneleaveapprovaldir()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow?->hr_approval == 1) {
				$n->hasoneleaveapprovalhr()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			// remove leave_id from attendance
			$z = HRAttendance::where('leave_id', $request->id)->get();
			foreach ($z as $s) {
				HRAttendance::where('id', $s->id)->update(['leave_id' => null]);
			}
			//////////////////////////////////////////////////////////////////////////////////////////////
			// done processing the data
			return response()->json([
				'status' => 'success',
				'message' => 'Your leave has been cancelled.',
			]);
		}
	}

	public function leaverapprove(HRLeaveApprovalBackup $hrleaveapprovalbackup)
	{
		$hrleaveapprovalbackup->update(['leave_status_id' => 5]);
			return response()->json([
				'status' => 'success',
				'message' => 'Your colleague leave has been approved... and he/she says thank you.',
			]);
	}

	public function leavesapprove(Request $request, HRLeaveApprovalSupervisor $hrleaveapprovalsupervisor)
	{
		$hrleaveapprovalsupervisor->update(['leave_status_id' => $request->id]);
			return response()->json([
				'status' => 'success',
				'message' => 'Leave approved.',
			]);
	}

	public function supervisorstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave_status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalSupervisor::find($request->id);
		$sal = $sa->belongstostaffleave;										// this supervisor approval belongs to leave
		$sauser = $sal->belongstostaff;											// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);

		// find the pivot table
		$p1 = $sal->belongstomanyleaveannual()->first();
		$p2 = $sal->belongstomanyleavemc()->first();
		$p3 = $sal->belongstomanyleavematernity()->first();
		$p4 = $sal->belongstomanyleavereplacement()->first();

		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->back()->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				if (!$p1) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleaveannual()->detach($p1->id);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				if (!$p4) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavereplacement()->detach($p4->id);
			} elseif($saly == 2) {												// mc leave
				if (!$p2) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavemc()->detach($p2->id);
			} elseif($saly == 7) {
				if (!$p3) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavematernity()->detach($p3->id);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			// remove leave_id from attendance
			$z = HRAttendance::where('leave_id', $sal->id)->get();
			foreach ($z as $s) {
				HRAttendance::where('id', $s->id)->update(['leave_id' => null]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval.');
		return redirect()->back();
	}

	public function hodstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave_status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalHOD::find($request->id);
		$sal = $sa->belongstostaffleave;										// this hod approval belongs to leave
		$sauser = $sal->belongstostaff;											// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);

		// find the pivot table
		$p1 = $sal->belongstomanyleaveannual()->first();
		$p2 = $sal->belongstomanyleavemc()->first();
		$p3 = $sal->belongstomanyleavematernity()->first();
		$p4 = $sal->belongstomanyleavereplacement()->first();

		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->back()->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				if (!$p1) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleaveannual()->detach($p1->id);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				if (!$p4) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavereplacement()->detach($p4->id);
			} elseif($saly == 2) {												// mc leave
				if (!$p2) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavemc()->detach($p2->id);
			} elseif($saly == 7) {
				if (!$p3) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavematernity()->detach($p3->id);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			// remove leave_id from attendance
			$z = HRAttendance::where('leave_id', $sal->id)->get();
			foreach ($z as $s) {
				HRAttendance::where('id', $s->id)->update(['leave_id' => null]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->back();
	}

	public function dirstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave_status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalDirector::find($request->id);
		$sal = $sa->belongstostaffleave;													// this hod approval belongs to leave
		$sauser = $sal->belongstostaff;												// leave belongs to user, not authuser anymore
		// dd($sauser);

		// find the pivot table
		$p1 = $sal->belongstomanyleaveannual()->first();
		$p2 = $sal->belongstomanyleavemc()->first();
		$p3 = $sal->belongstomanyleavematernity()->first();
		$p4 = $sal->belongstomanyleavereplacement()->first();

		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->back()->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				if (!$p1) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleaveannual()->detach($p1->id);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				if (!$p4) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavereplacement()->detach($p4->id);
			} elseif($saly == 2) {												// mc leave
				if (!$p2) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavemc()->detach($p2->id);
			} elseif($saly == 7) {
				if (!$p3) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavematernity()->detach($p3->id);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			// remove leave_id from attendance
			$z = HRAttendance::where('leave_id', $sal->id)->get();
			foreach ($z as $s) {
				HRAttendance::where('id', $s->id)->update(['leave_id' => null]);
			}
		} elseif($request->leave_status_id == 6) {								// leave waived, so need to put back all leave period.
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->back();
	}

	public function hrstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave_status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalHR::find($request->id);
		$sal = $sa->belongstostaffleave;											// this hr approval belongs to leave
		$sauser = $sal->belongstostaff;												// leave belongs to user, not authuser anymore
		// dd($sauser);

		// find the pivot table
		$p1 = $sal->belongstomanyleaveannual()->first();
		$p2 = $sal->belongstomanyleavemc()->first();
		$p3 = $sal->belongstomanyleavematernity()->first();
		$p4 = $sal->belongstomanyleavereplacement()->first();

		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->back()->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				if (!$p1) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and annual leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleaveannual()->detach($p1->id);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				if (!$p4) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and replacement leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavereplacement()->detach($p4->id);
			} elseif($saly == 2) {												// mc leave
				if (!$p2) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and MC leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavemc()->detach($p2->id);
			} elseif($saly == 7) {
				if (!$p3) {
					Session::flash('flash_danger', 'Please inform IT Department with this message: "No link between leave and maternity leave table (database). This is old leave created from old system."');
					return redirect()->back()->withInput();
				}
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
				// $sal->belongstomanyleavematernity()->detach($p3->id);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			// remove leave_id from attendance
			$z = HRAttendance::where('leave_id', $sal->id)->get();
			foreach ($z as $s) {
				HRAttendance::where('id', $s->id)->update(['leave_id' => null]);
			}
		} elseif($request->leave_status_id == 6) {								// leave waived, so need to put back all leave period.

		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->back();
	}

	public function deactivatestaff(Request $request, Staff $staff): JsonResponse
	{
		// dd($request->all());
		$staff->update(['active' => 0]);
		$staff->hasmanylogin()->where('active', 1)->update(['active' => 0]);
		return response()->json([
			'status' => 'success',
			'message' => 'Staff '.$staff->name.' successfully deactivated',
		]);
	}

	public function deletecrossbackup(Request $request, Staff $staff): JsonResponse
	{
		$staff->crossbackupto()->detach($request->id);
		return response()->json([
			'status' => 'success',
			'message' => 'Cross backup for '.$staff->name.' been deactivated.',
		]);
	}

	public function staffactivate(Request $request, Staff $staff): RedirectResponse
	{
		$staff->update(['active' => 1]);
		$staff->hasmanylogin()->create([
											'username' => $request->username,
											'password' => $request->password,
											'active' => 1,
										]);
		Session::flash('flash_message', 'Successfully activate ex-staff '.$staff->name);
		return redirect()->route('staff.show', $staff->id);
	}

	public function generateannualleave(Request $request)
	{
		// checking to make sure there is no duplicate year for 1 person
		$r = HRLeaveAnnual::where('year', now()->addYear()->format('Y'))->get()->isEmpty();
		if ($r) {
			$s = Staff::where('active', 1)->get();
			foreach ($s as $st) {
				$al = HRLeaveAnnual::where('year', now()->year)->where('staff_id', $st->id)->first();
				$st->hasmanyleaveannual()->create([
													'year' => now()->addYear()->format('Y'),
													'annual_leave' => $al?->annual_leave + $al?->annual_leave_adjustment,
													'annual_leave_adjustment' => 0,
													'annual_leave_utilize' => 0,
													'annual_leave_balance' => $al?->annual_leave + $al?->annual_leave_adjustment,
												]);
			}
		} else {
			return response()->json([
				'status' => 'error',
				'message' => 'You have generate annual leave for next year. System couldn\'t generate anymore annual leave entitlement for all users in next year ('.now()->addYear()->format('Y').')',
			]);
		}

		return response()->json([
			'status' => 'success',
			'message' => 'Success generate annual leave for next year',
		]);
	}

	public function generatemcleave(Request $request)
	{
		// checking to make sure there is no duplicate year for 1 person
		$r = HRLeaveMC::where('year', now()->addYear()->format('Y'))->get()->isEmpty();
		if ($r) {
			$s = Staff::where('active', 1)->get();
			foreach ($s as $st) {
				$al = HRLeaveMC::where('year', now()->year)->where('staff_id', $st->id)->first();
				$st->hasmanyleavemc()->create([
													'year' => now()->addYear()->format('Y'),
													'mc_leave' => $al?->mc_leave + $al?->mc_leave_adjustment,
													'mc_leave_adjustment' => 0,
													'mc_leave_utilize' => 0,
													'mc_leave_balance' => $al?->mc_leave + $al?->mc_leave_adjustment,
												]);
			}
		} else {
			return response()->json([
				'status' => 'error',
				'message' => 'You have generate medical certificate leave for next year. System couldn\'t generate anymore medical certificate leave entitlement for all users in next year ('.now()->addYear()->format('Y').')',
			]);
		}

		return response()->json([
			'status' => 'success',
			'message' => 'Success generate medical certificate leave for next year',
		]);
	}

	public function generatematernityleave(Request $request)
	{
		// checking to make sure there is no duplicate year for 1 person
		$r = HRLeaveMaternity::where('year', now()->addYear()->format('Y'))->get()->isEmpty();
		if ($r) {
			$s = Staff::where('active', 1)->get();
			foreach ($s as $st) {
				$al = HRLeaveMaternity::where('year', now()->year)->where('staff_id', $st->id)->first();
				$st->hasmanyleavematernity()->create([
													'year' => now()->addYear()->format('Y'),
													'maternity_leave' => $al?->maternity_leave + $al?->maternity_leave_adjustment,
													'maternity_leave_adjustment' => 0,
													'maternity_leave_utilize' => 0,
													'maternity_leave_balance' => $al?->maternity_leave + $al?->maternity_leave_adjustment,
												]);
			}
		} else {
			return response()->json([
				'status' => 'error',
				'message' => 'You have generate medical certificate leave for next year. System couldn\'t generate anymore medical certificate leave entitlement for all users in next year ('.now()->addYear()->format('Y').')',
			]);
		}

		return response()->json([
			'status' => 'success',
			'message' => 'Success generate medical certificate leave for next year',
		]);
	}

	public function uploaddoc(Request $request, HRLeave $hrleave)
	{
		// dd($request->all());

		$validated = $request->validate([
				'document' => 'required|file|max:5120|mimes:jpeg,jpg,png,bmp,pdf,doc,docx',
				'amend_note' => 'required',
			],
			[
				// 'document.required' => 'Please choose supporting document',
				// 'amend_note.required' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'document' => 'Supporting Document',
				'amend_note' => 'Remarks'
			]
		);

		if($request->file('document')){
			$file = $request->file('document')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d His');
			$fileName = $currentDate . '_' . $file;
			// Store File in Storage Folder
			$request->document->storeAs('public/leaves', $fileName);
			// storage/app/uploads/file.png
			// Store File in Public Folder
			// $request->document->move(public_path('uploads'), $fileName);
			// public/uploads/file.png
			// $data += ['softcopy' => $fileName];
		}
		$t = $hrleave->update(['softcopy' => $fileName]);
		if (!$hrleave->hasmanyleaveamend()->count()) {
			$hrleave->hasmanyleaveamend()->create( Arr::add(Arr::add($request->only(['amend_note']), 'staff_id', \Auth::user()->belongstostaff->id), 'date', now()) );
		} else {
			foreach (HRLeaveAmend::where('leave_id', $hrleave->id)->get() as $v) {
				HRLeaveAmend::find($v->id)->update([
					'amend_note' => ucwords(Str::lower($v->amend_note)).'<br />'.ucwords(Str::lower($request->amend_note)),
					'staff_id' => \Auth::user()->belongstostaff->id,
					'date' => now()
				]);
			}
		}
		return redirect()->back();
	}
}
