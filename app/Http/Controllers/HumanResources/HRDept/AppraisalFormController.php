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
// use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\HRAppraisalSection;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AppraisalFormController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		// // ini_set('max_execution_time', 60000000000);
		// if ($request->date != NULL) {
		// 	$selected_date = $request->date;
		// } else {
		// 	$current_time = now();
		// 	$selected_date = $current_time->format('Y-m-d');
		// }

		// $attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
		// 	->select('hr_attendances.id as id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'leave_id', 'hr_attendances.remarks as remarks', 'hr_attendances.hr_remarks as hr_remarks', 'exception', 'hr_attendances.created_at as created_at', 'hr_attendances.updated_at as updated_at', 'hr_attendances.deleted_at as deleted_at', 'staffs.name as name', 'staffs.restday_group_id as restday_group_id', 'staffs.active as active')
		// 	->where('staffs.active', 1)
		// 	->where('attend_date', $selected_date)
		// 	// ->where(function(Builder $query) {
		// 	// 	$query->whereDate('attend_date', '>=', '2023-01-01')
		// 	// 	->whereDate('attend_date', '<=', '2023-12-31');
		// 	// })
		// 	->get();

		$departments = DepartmentPivot::all();

		return view('humanresources.hrdept.appraisal.form.index', ['departments' => $departments]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create($id): View
	{
		$department = DepartmentPivot::where('id', $id)->first();
		return view('humanresources.hrdept.appraisal.form.create', ['department' => $department]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		// model
		if ($request->has('p1')) {
			foreach( $request->p1 as $key => $val ) {
				HRAppraisalSection::create([
					'section' => $val['section_text'],
				]);
			}
		}

		Session::flash('flash_message', 'Successfully Submit Appraisal Form.');
		return redirect()->route('appraisalform.index');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(): View
	{
		// return view('humanresources.hrdept.attendance.show', ['attendance' => $attendance]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(): View
	{
		// return view('humanresources.hrdept.attendance.edit', ['attendance' => $attendance]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(): RedirectResponse
	{
		// //dd($request->all());

		// $exception = (!request()->has('exception') == '1' ? '0' : '1');

		// $attendance->update([
		// 	'daytype_id' => $request->daytype_id,
		// 	'attendance_type_id' => $request->attendance_type_id,
		// 	'leave_id' => $request->leave_id,
		// 	'in' => $request->in,
		// 	'break' => $request->break,
		// 	'resume' => $request->resume,
		// 	'out' => $request->out,
		// 	'time_work_hour' => $request->time_work_hour,
		// 	'remarks' => ucwords(Str::of($request->remarks)->lower()),
		// 	'hr_remarks' => ucwords(Str::of($request->hr_remarks)->lower()),
		// 	'exception' => $exception,
		// ]);

		// $attendance->save();

		// Session::flash('flash_message', 'Data successfully updated!');
		// return redirect()->route('attendance.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(): RedirectResponse
	{
		//
	}
}
