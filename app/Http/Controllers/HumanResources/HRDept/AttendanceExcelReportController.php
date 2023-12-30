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
use App\Exports\PayslipExport;
use Maatwebsite\Excel\Facades\Excel;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AttendanceJob;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

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

class AttendanceExcelReportController extends Controller
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
		//
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request)/*: View*/
	{
		return view('humanresources.hrdept.attendance.attendanceexcelreport.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)/*: RedirectResponse*/
	{
		$validated = $request->validate(
			[
				'from' => 'required|date_format:Y-m-d|before_or_equal:to',
				'to' => 'required|date_format:Y-m-d|after_or_equal:from',
			],
			[
				'from.required' => 'Please insert date from',
				'to.required' => 'Please insert date to',
			],
			[
				'from' => 'From Date',
				'to' => 'To Date',
			]
		);
		// dd($request->all());
		// $staff = HRAttendance::where(function (Builder $query) use ($request){
		// 							$query->whereDate('attend_date', '<=', $s->attend_date)
		// 								->whereDate('attend_date', '>=', $s->attend_date);
		// 						})
		$from = Carbon::parse($request->from)->format('j F Y');
		$to = Carbon::parse($request->to)->format('j F Y');
		return Excel::download(new PayslipExport($request->only(['from', 'to'])), $from.' - '.$to.' AttendancePayRoll.xlsx');
		// return redirect()->route('excelreport.create', ['id' => $batch->id, 'request' => $request]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendance $hRAttendance): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $hRAttendance): JsonResponse
	{
		//
	}
}
