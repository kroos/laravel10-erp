<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Login;
use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\DepartmentPivot;

// load validation
use App\Http\Requests\HumanResources\Profile\ProfileRequestUpdate;

use Session;

class ProfileController extends Controller
{

	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('profileaccess', ['only' => ['show', 'edit', 'update']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.profile.index');
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
	public function store(Request $request): View
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request, Staff $profile): View
	{
		$current_time = now();
		$current_year = $current_time->format('Y');
		$current_month = $current_time->format('m');

		if ($request->year != NULL && $request->month != NULL) {
			$year = $request->year;
			$month = $request->month;
		} else {
			$year = $current_year;
			$month = $current_month;
		}

		$attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->where('hr_attendances.staff_id', $profile->id)
			->whereYear('hr_attendances.attend_date', '=', $year)
			->whereMonth('hr_attendances.attend_date', '=', $month)
			->select('hr_attendances.remarks as attend_remark', 'hr_attendances.*', 'staffs.*')
			->get();

		$wh_group = $profile->belongstomanydepartment()->wherePivot('main', 1)->first();

		return view('humanresources.profile.show', ['profile' => $profile, 'attendance' => $attendance, 'wh_group' => $wh_group->wh_group_id, 'year' => $year, 'month' => $month]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $profile): View
	{
		return view('humanresources.profile.edit', compact('profile'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(ProfileRequestUpdate $request, Staff $profile): RedirectResponse
	{

		$login = Login::find($request->input('login_id'));

		if ($login) {
			// Update the password field
			$login->password = $request->input('password');
			$login->save(); // Save the changes to the database
		}

		Session::flash('flash_message', 'Password successfully updated!');
		return Redirect::route('profile.show', $profile);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy()
	{
		//
	}
}
