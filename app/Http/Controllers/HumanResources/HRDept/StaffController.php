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
		// $this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);								// all high management
		// $this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);		// only hod and asst hod HR can access
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
	public function update(Request $request, Staff $staff): RedirectResponse
	{

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
