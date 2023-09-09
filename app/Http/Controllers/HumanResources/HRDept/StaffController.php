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

// load validation
use App\Http\Requests\HumanResources\Leave\HRLeaveRequestStore;
use App\Http\Requests\HumanResources\Staff\StaffRequestStore;
use App\Http\Requests\HumanResources\Staff\StaffRequestUpdate;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class StaffController extends Controller
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
		return view('humanresources.hrdept.staff.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.staff.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StaffRequestStore $request)/*: RedirectResponse*/
	{
		// dd($request->all());
		$data = $request->only(['ic', 'religion_id', 'gender_id', 'race_id', 'nationality_id', 'marital_status_id', 'mobile', 'phone', 'dob', 'cimb_account', 'epf_account', 'income_tax_no', 'socso_no', 'weight', 'height', 'join', 'div_id', 'restday_group_id', 'leave_flow_id', 'status_id', 'active', 'authorise_id']);
		$data += ['active' => 1];
		$data += ['name' => ucwords(Str::of($request->name)->lower())];
		$data += ['address' => ucwords(Str::of($request->address)->lower())];
		$data += ['email' => Str::of($request->email)->lower()];

		if($request->file('image')){
			$fil = $request->file('image')->getClientOriginalName();
			$file = Str::of($fil)->trim();
			$ext = $request->file('image')->getClientOriginalExtension();
			$currentDate = Carbon::now()->format('Y-m-d-H-i-s');
			$fileName = $currentDate.'_'.$request->username.'_'.$file;
			// Store File in Storage Folder
			$filePath = $request->file('image')->storeAs('public/user_profile', $fileName);
			// storage/app/public/user_profile/file.png
			// Store File in Public Folder
			// $request->image->move(public_path('uploads'), $fileName);
			// public/uploads/file.png

			if ($request->file('image')->isValid()) {
				$data += ['image' => $fileName];
			}
		}

		$signin = $request->only(['password']);
		$signin += ['username' => Str::of($request->address)->upper()];
		$signin += ['active' => 1];

		$s = Staff::create($data);

		$s->hasmanylogin()->create($signin);
		$s->belongstomanydepartment()->attach($request->only(['pivot_dept_id']), ['main' => 1]);
		$s->hasmanyleaveannual()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'annual_leave' => $request->annual_leave,
																					'annual_leave_balance' => $request->annual_leave,
																				]);
		$s->hasmanyleavemc()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'mc_leave' => $request->mc_leave,
																					'mc_leave_balance' => $request->mc_leave,
																				]);
		$s->hasmanyleavematernity()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'maternity_leave' => $request->maternity_leave,
																					'maternity_leave_balance' => $request->maternity_leave,
																				]);
		if ($request->has('crossbackup')) {
			foreach ($request->crossbackup as $k => $v) {
				$s->crossbackupto()->attach([
												$v['backup_staff_id'] => ['active' => 1]
											]);
			}
		}

		if($request->has('staffspouse')) {
			foreach($request->staffspouse as $k => $v) {
				$s->hasmanyspouse()->create($v);
			}
		}

		if($request->has('staffchildren')) {
			foreach($request->staffchildren as $k => $v) {
				$s->hasmanychildren()->create($v);
			}
		}

		if($request->has('staffemergency')) {
			foreach($request->staffemergency as $k => $v) {
				$s->hasmanyemergency()->create($v);
			}
		}

		// Session::flash('flash_danger', 'Please make sure your applied leave does not exceed your available leave balance');
		// return redirect()->back();
		Session::flash('flash_message', 'Successfully Add New Staff.');
		return redirect()->route('staff.index');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff): View
	{
		return view('humanresources.hrdept.staff.show', compact(['staff']));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $staff): View
	{
		return view('humanresources.hrdept.staff.edit', ['staff' => $staff]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(StaffRequestUpdate $request, Staff $staff)/*: RedirectResponse*/
	{
		// foreach ($request->crossbackup as $k => $v) {
		// 	dump($v['backup_staff_id']);
		// }
		// dd($request->all());
		$data = $request->only(['ic', 'gender_id', 'marital_status_id', 'race_id', 'religion_id', 'mobile', 'email', 'phone', 'dob', 'nationality_id', 'cimb_account', 'epf_account', 'income_tax_no', 'socso_no', 'weight', 'height', 'authorise_id', 'div_id', 'join', 'restday_group_id', 'leave_flow_id']);

		if($request->name){
			$data += ['name' => ucwords(Str::of($request->name)->lower())];
		}

		if($request->address){
			$data += ['address' => ucwords(Str::of($request->address)->lower())];
		}

		// $data += ['active' => 1];
		if($request->file('image')){
			// $file = $request->file('image')->getClientOriginalName();
			$fil = $request->file('image')->getClientOriginalName();
			$file = Str::of($fil)->trim();
			$ext = $request->file('image')->getClientOriginalExtension();
			$currentDate = Carbon::now()->format('Y-m-d-H-i-s');
			$fileName = $currentDate.'_'.$request->username.'_'.$file;
			// Store File in Storage Folder
			$filePath = $request->file('image')->storeAs('public/user_profile', $fileName);
			// storage/app/public/user_profile/file.png
			// Store File in Public Folder
			// $request->image->move(public_path('uploads'), $fileName);
			// public/uploads/file.png

			if ($request->file('image')->isValid()) {
				$data += ['image' => $fileName];
			}
		}

		$s = $staff->update($data);

		$login = $request->only(['username']);
		if($request->password) {
			$login += ['password' => $request->password];
		}

		// ensure to disable the other 1, and checking also
		$upgrade = $staff->hasmanylogin()->where('active', 1)->first();
		if(($request->status_id != $staff->status_id && $request->username != $upgrade->username) && ($request->status_id == 1 && $staff->status_id != 1)) {		// which means there is an upgrade
			$staff->hasmanylogin()->update(['active' => 0]);																										// disable old login
			if (!$request->password) {																																// create new login
				$staff->hasmanylogin()->create([
					'username' => $request->username,
					'password' => $staff->hasmanylogin()->where('active', 0)->first()->password,
				]);
				$staff->update(['status_id' => $request->status_id]);
			} else {
				$staff->hasmanylogin()->update($login);
				$staff->update(['status_id' => $request->status_id]);
			}
		}

		$staff->belongstomanydepartment()->sync($request->only(['pivot_dept_id']), ['main' => 1]);
		$staff->crossbackupto()->sync($request->only(['backup_staff_id'], ['active' => 1]));
		$staff->hasmanyleaveannual()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'annual_leave' => $request->annual_leave,
																					'annual_leave_balance' => $request->annual_leave,
																				]);
		$staff->hasmanyleavemc()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'mc_leave' => $request->mc_leave,
																					'mc_leave_balance' => $request->mc_leave,
																				]);
		$staff->hasmanyleavematernity()->whereYear('year', now())->updateOrCreate([
																					'year' => Carbon::now()->format('Y'),
																					'maternity_leave' => $request->maternity_leave,
																					'maternity_leave_balance' => $request->maternity_leave,
																				]);
		if ($request->has('crossbackup')) {
			// syncWithPivotValues([1, 2, 3], ['active' => true])
			foreach ($request->crossbackup as $k => $v) {
				$staff->crossbackupto()->syncWithoutDetaching([$v['backup_staff_id'] => ['active' => 1]]);
			}
		}



		if($request->has('staffspouse')) {
			foreach($request->staffspouse as $k => $v) {
				$staff->hasmanyspouse()->updateOrCreate([
						'id' => $v['id']
					], Arr::except($v, ['id']));
			}
		}

		if($request->has('staffchildren')) {
			foreach($request->staffchildren as $k => $v) {
				$staff->hasmanychildren()->updateOrCreate([
						'id' => $v['id']
					], Arr::except($v, ['id']));
			}
		}

		if($request->has('staffemergency')) {
			foreach($request->staffemergency as $k => $v) {
				$staff->hasmanyemergency()->updateOrCreate([
						'id' => $v['id']
					], Arr::except($v, ['id']));
			}
		}

		Session::flash('flash_message', 'Successfully Edit Staff.');
		return redirect()->route('staff.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff): RedirectResponse
	{
		//
	}
}
