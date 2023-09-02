<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

// load validation
use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

use Illuminate\Support\Facades\DB;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

// load paginator
use Illuminate\Pagination\Paginator;

// load array helper
use Illuminate\Support\Arr;

// for viewing
use Illuminate\View\View;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AttendanceController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)/*: View*/
	{
		// Paginator::useBootstrapFive();
		// $sa = HRAttendance::SelectRaw('COUNT(hr_attendances.staff_id) as totalactivestaff,  hr_attendances.attend_date')
		// 	->join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
		// 	->where('staffs.active', 1)
		// 	->groupBy('hr_attendances.attend_date')
		// 	->orderBy('hr_attendances.attend_date', 'DESC')
		// 	->cursorPaginate(1);

		// $attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
		// 	->select('hr_attendances.id as id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'leave_taken', 'remark', 'hr_remark', 'exception', 'hr_attendances.created_at as created_at', 'hr_attendances.updated_at as updated_at', 'hr_attendances.deleted_at as deleted_at', 'staffs.name as name', 'staffs.restday_group_id as restday_group_id', 'staffs.active as active')
		// 	->where('staffs.active', 1)
		// 	->whereDate('hr_attendances.attend_date', $sa->first()->attend_date)
		// 	->orderBy('hr_attendances.attend_date', 'DESC')/*->ddRawSql();*/
		// 	->cursorPaginate($sa->first()->totalactivestaff);

		if ($request->has('date')) {
			$date = $request->query('date');
		} else {
			$date = Carbon::now()->format('Y-m-d');;
		}

		$attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->select('hr_attendances.id as id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'leave_taken', 'remark', 'hr_remark', 'exception', 'hr_attendances.created_at as created_at', 'hr_attendances.updated_at as updated_at', 'hr_attendances.deleted_at as deleted_at', 'staffs.name as name', 'staffs.restday_group_id as restday_group_id', 'staffs.active as active')
			->where('staffs.active', 1)
			->whereDate('hr_attendances.attend_date', $date)
			->orderBy('hr_attendances.attend_date', 'DESC')
			->get();

		return view('humanresources.hrdept.attendance.index', ['attendance' => $attendance, 'date' => $date]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request, Staff $staff)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff)
	{
		return view('humanresources.hrdept.attendance.show', ['staff' => $staff]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $attendance)
	{
		return view('humanresources.hrdept.attendance.edit', compact('attendance'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(AttendanceRequestUpdate $request, HRAttendance $attendance): RedirectResponse
	{
		// dd($request->all());

		$exception = (!request()->has('exception') == '1' ? '0' : '1');

		$attendance->update([
			'daytype_id' => $request->daytype_id,
			'attendance_type_id' => $request->attendance_type_id,
			'in' => $request->in,
			'break' => $request->break,
			'resume' => $request->resume,
			'out' => $request->out,
			'remark' => $request->remark,
			'hr_remark' => $request->hr_remark,
			'exception' => $exception,
		]);

		$attendance->save();

		Session::flash('flash_message', 'Data successfully updated!');
		return Redirect::route('attendance.index', $attendance);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff)
	{
		//
	}
}
