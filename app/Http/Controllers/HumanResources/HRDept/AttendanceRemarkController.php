<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRAttendanceRemark;
use App\Models\HumanResources\HRAttendance;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class AttendanceRemarkController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$attendanceremark = HRAttendanceRemark::whereYear('date_from', now())->get();
		return view('humanresources.hrdept.attendance.attendanceremark.index', ['attendanceremark' => $attendanceremark]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.attendance.attendanceremark.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		// dd($request->all());
		$validated = $request->validate([
				'staff_id' => 'required',
				'date_from' => 'required|date',		// required if only leave_status_id is 5 (Approved)
				'date_to' => 'required|date',		// required if only leave_status_id is 5 (Approved)
				'attendance_remarks' => 'required',
				'hr_attendance_remarks' => 'required',
				'remarks' => 'nullable',
			],
			[],
			[
				'staff_id' => 'Staff',
				'date_from' => 'Date From',
				'date_to' => 'Date To',
				'attendance_remarks' => 'Attendance Remarks',
				'hr_attendance_remarks' => 'HR Attendance Remarks',
				'remarks' => 'Remarks',
			]
		);
		HRAttendance::where('staff_id', $request->staff_id)
					->where(function (Builder $query) use ($request) {
						$query->whereDate('attend_date', '>=', $request->date_from)
						->whereDate('attend_date', '<=', $request->date_from);
					})
					->update([
						'remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
						'hr_remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
					]);

		HRAttendanceRemark::create([
			'staff_id' => $request->staff_id,
			'date_from' => $request->date_from,
			'date_to' => $request->date_to,
			'attendance_remarks' => ucwords(Str::lower($request->attendance_remarks)),
			'hr_attendance_remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
			'remarks' => ucwords(Str::lower($request->remarks)),
		]);
		return redirect()->route('attendanceremark.index')->with('flash_message', 'Success add remarks attendance');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendanceRemark $attendanceremark): View
	{
		return view('humanresources.hrdept.attendance.attendanceremark.show', ['attendanceremark' => $attendanceremark]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendanceRemark $attendanceremark): View
	{
		return view('humanresources.hrdept.attendance.attendanceremark.edit', ['attendanceremark' => $attendanceremark]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendanceRemark $attendanceremark): RedirectResponse
	{
		$validated = $request->validate([
				'staff_id' => 'required',
				'date_from' => 'required|date',		// required if only leave_status_id is 5 (Approved)
				'date_to' => 'required|date',		// required if only leave_status_id is 5 (Approved)
				'attendance_remarks' => 'required',
				'hr_attendance_remarks' => 'required',
				'remarks' => 'nullable',
			],
			[],
			[
				'staff_id' => 'Staff',
				'date_from' => 'Date From',
				'date_to' => 'Date To',
				'attendance_remarks' => 'Attendance Remarks',
				'hr_attendance_remarks' => 'HR Attendance Remarks',
				'remarks' => 'Remarks',
			]
		);

		// delete all old remarks
		HRAttendance::where('staff_id', $attendanceremark->staff_id)
					->where(function (Builder $query) use ($attendanceremark) {
						$query->whereDate('attend_date', '>=', $attendanceremark->date_from)
						->whereDate('attend_date', '<=', $attendanceremark->date_from);
					})
					->update([
						'remarks' => null,
						'hr_remarks' => null,
					]);

		// update all new remarks
		HRAttendance::where('staff_id', $request->staff_id)
					->where(function (Builder $query) use ($request) {
						$query->whereDate('attend_date', '>=', $request->date_from)
						->whereDate('attend_date', '<=', $request->date_from);
					})
					->update([
						'remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
						'hr_remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
					]);

		$attendanceremark->update([
			'staff_id' => $request->staff_id,
			'date_from' => $request->date_from,
			'date_to' => $request->date_to,
			'attendance_remarks' => ucwords(Str::lower($request->attendance_remarks)),
			'hr_attendance_remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
			'hr_attendance_remarks' => ucwords(Str::lower($request->hr_attendance_remarks)),
			'remarks' => ucwords(Str::lower($request->remarks)),
		]);
		return redirect()->route('attendanceremark.index')->with('flash_message', 'Success edit remarks attendance');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendanceRemark $attendanceremark): JsonResponse
	{
		HRAttendance::where('staff_id', $attendanceremark->id)
					->where(function (Builder $query) use ($attendanceremark) {
						$query->whereDate('attend_date', '>=', $attendanceremark->date_from)
						->whereDate('attend_date', '<=', $attendanceremark->date_from);
					})
					->update([
						'remarks' => null,
						'hr_remarks' => null,
					]);
		$attendanceremark->delete();
		return response()->json([
			'status' => 'success',
			'message' => 'Done!'
		]);
	}
}
