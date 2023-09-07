<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Leave\HRLeaveRequestStore;

// load models
use App\Models\Staff;

// load validation
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
	public function store(StaffRequestStore $request): RedirectResponse
	{
		// dd($request->all());
		$data = $request->except(['_token', 'username', 'password', 'category_id', 'branch_id', 'pivot_dept_id', 'image', 'annual_leave', 'mc_leave', 'maternity_leave', 'staffspouse', 'staffchildren', 'staffemergency']);

		$data += ['active' => 1];

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

		$s = Staff::create($data);

		$s->hasmanylogin()->create(Arr::add( $request->only(['username', 'password']), 'active', 1 ));
		$s->belongstomanydepartment()->attach($request->only(['pivot_dept_id']), ['main' => 1]);
		$s->crossbackupto()->attach($request->only(['backup_staff_id'], ['active' => 1]));

		if($request->has('staffspouse')) {
			foreach($request->staffspouse as $k => $v) {
				$s->hasmanyspouse()->create([
												'spouse' => $v['spouse'],
												'phone' => $v['phone'],
												'profession' => $v['profession'],
											]);
			}
		}

		if($request->has('staffchildren')) {
			foreach($request->staffchildren as $k => $v) {
				$s->hasmanychildren()->create([
												'children' => $v['children'],
												'gender_id' => $v['gender_id'],
												'education_level_id' => $v['education_level_id'],
												'health_status_id' => $v['health_status_id'],
												'tax_exemption' => $v['tax_exemption'],
												'tax_exemption_percentage_id' => $v['tax_exemption_percentage_id'],
											]);
			}
		}

		if($request->has('staffemergency')) {
			foreach($request->staffemergency as $k => $v) {
				$s->hasmanyemergency()->create([
												'contact_person' => $v['contact_person'],
												'phone' => $v['phone'],
												'relationship_id' => $v['relationship_id'],
												'address' => $v['address'],
											]);
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
	public function update(StaffRequestUpdate $request, Staff $staff): RedirectResponse
	{
		$data = $request->only(['name', 'ic', 'gender_id', 'marital_status_id']);

		if(!is_null($request->race_id)){
			$data += ['race_id' => $request->race_id];
		}
		if(!is_null($request->religion_id)){
			$data += ['religion_id' => $request->religion_id];
		}
		if(!is_null($request->mobile)){
			$data += ['mobile' => $request->mobile];
		}
		if(!is_null($request->email)){
			$data += ['email' => $request->email];
		}
		if(!is_null($request->address)){
			$data += ['address' => Str::of(Str::of($request->address)->lower())->ucfirst()];
		}
		if(!is_null($request->phone)){
			$data += ['phone' => $request->phone];
		}
		if(!is_null($request->dob)){
			$data += ['dob' => $request->dob];
		}
		if(!is_null($request->nationality_id)){
			$data += ['nationality_id' => $request->nationality_id];
		}
		if(!is_null($request->cimb_account)){
			$data += ['cimb_account' => $request->cimb_account];
		}
		if(!is_null($request->epf_account)){
			$data += ['epf_account' => $request->epf_account];
		}
		if(!is_null($request->income_tax_no)){
			$data += ['income_tax_no' => $request->income_tax_no];
		}
		if(!is_null($request->socso_no)){
			$data += ['socso_no' => $request->socso_no];
		}
		if(!is_null($request->weight)){
			$data += ['weight' => $request->weight];
		}
		if(!is_null($request->height)){
			$data += ['height' => $request->height];
		}
		if(!is_null($request->authorise_id)) {
			$data += ['authorise_id' => $request->authorise_id];
		}
		if(!is_null($request->div_id)) {
			$data += ['div_id' => $request->div_id];
		}
		if(!is_null($request->join)) {
			$data += ['join' => $request->join];
		}
		if(!is_null($request->restday_group_id)) {
			$data += ['restday_group_id' => $request->restday_group_id];
		}
		if(!is_null($request->leave_flow_id)) {
			$data += ['leave_flow_id' => $request->leave_flow_id];
		}

// 'status_id', 'pivot_dept_id'

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

		// dd($data);

		$s = $staff->update($data);

		$login = $request->only(['username']);
		if(!is_null($request->password)) {
			$login += ['password' => $request->password];
		}
		// ensure to disable the other 1, and checking also
		$staff->status_id;
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

		if($request->has('staffspouse')) {
			foreach($request->staffspouse as $k => $v) {
				$staff->hasmanyspouse()->updateOrCreate([
												'id' => $v['id']
											],
											[
												'spouse' => $v['spouse'],
												'phone' => $v['phone'],
												'profession' => $v['profession'],
											]);
			}
		}

		if($request->has('staffchildren')) {
			foreach($request->staffchildren as $k => $v) {
				$staff->hasmanychildren()->updateOrCreate([
												'id' => $v['id']
											],
											[
												'children' => $v['children'],
												'gender_id' => $v['gender_id'],
												'education_level_id' => $v['education_level_id'],
												'health_status_id' => $v['health_status_id'],
												'tax_exemption' => $v['tax_exemption'],
												'tax_exemption_percentage_id' => $v['tax_exemption_percentage_id'],
											]);
			}
		}

		if($request->has('staffemergency')) {
			foreach($request->staffemergency as $k => $v) {
				$staff->hasmanyemergency()->updateOrCreate([
												'id' => $v['id']
											],
											[
												'contact_person' => $v['contact_person'],
												'phone' => $v['phone'],
												'relationship_id' => $v['relationship_id'],
												'address' => $v['address'],
											]);
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
