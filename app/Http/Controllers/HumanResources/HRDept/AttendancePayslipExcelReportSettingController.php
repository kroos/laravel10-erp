<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load laravel-Excel
// use App\Exports\PayslipExport;
// use Maatwebsite\Excel\Facades\Excel;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AttendancePayslipJob;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

use App\Models\Login;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROvertimeRange;
use App\Models\HumanResources\HRAttendancePayslipSetting;


// load helper
use App\Helpers\TimeCalculator;
use App\Helpers\UnavailableDateTime;

// load paginator
// use Illuminate\Pagination\Paginator;

// load cursor pagination
// use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

class AttendancePayslipExcelReportSettingController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|2|5,14|31', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request): View
	{
		$settings = HRAttendancePayslipSetting::all();
		return view('humanresources.hrdept.attendance.attendanceexcelreportsetting.create', ['settings' => $settings]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)/*: RedirectResponse*/
	{
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $hRAttendance): View
	{
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $hRAttendance): View
	{
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendancePayslipSetting $attendancepayslipexcelsetting): JsonResponse
	{
		$attendancepayslipexcelsetting->update();
		return response()->json(['message' => 'Success', 'status' => 'success']);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $hRAttendance): JsonResponse
	{
	}
}
