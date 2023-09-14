<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;

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
	public function index(): View
	{
		Paginator::useBootstrapFive();
		$sa = HRAttendance::SelectRaw('COUNT(hr_attendances.staff_id) as totalactivestaff,  hr_attendances.attend_date')
			->join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->where('staffs.active', 1)
			->groupBy('hr_attendances.attend_date')
			->orderBy('hr_attendances.attend_date', 'DESC')
			->cursorPaginate(1);

		$attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->select('hr_attendances.id as id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'leave_taken', 'remark', 'hr_remark', 'exception', 'hr_attendances.created_at as created_at', 'hr_attendances.updated_at as updated_at', 'hr_attendances.deleted_at as deleted_at', 'staffs.name as name', 'staffs.restday_group_id as restday_group_id', 'staffs.active as active')
			->where('staffs.active', 1)
			->whereDate('hr_attendances.attend_date', $sa->first()->attend_date)
			->orderBy('hr_attendances.attend_date', 'DESC')
			/*->ddRawSql();*/
			->cursorPaginate($sa->first()->totalactivestaff);

		return view('humanresources.hrdept.attendance.index', ['attendance' => $attendance, 'sa' => $sa]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request, HRAttendance $attendance): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $attendance): View
	{
		return view('humanresources.hrdept.attendance.show', ['attendance' => $attendance]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $attendance): View
	{
		return view('humanresources.hrdept.attendance.edit', ['attendance' => $attendance]);
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
	public function destroy(HRAttendance $attendance): RedirectResponse
	{
		//
	}
}
