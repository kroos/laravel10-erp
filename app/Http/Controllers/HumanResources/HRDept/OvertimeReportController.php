<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HROvertime;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load cursor pagination
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

// load pdf
use Barryvdh\DomPDF\Facade\Pdf;

use Session;

class OvertimeReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,NULL', ['only' => ['create', 'store', 'index', 'show']]);      // all high management
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['edit', 'update', 'destroy']]);            // only hod and asst hod HR can access
	}

	/**
	 * Print PDF.
	 */
	public function print(Request $request)
	{
		$current_datetime = Carbon::now();

		$overtimes = HROvertime::join('staffs', 'staffs.id', '=', 'hr_overtimes.staff_id')
		->join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
		->join('logins', 'hr_overtimes.staff_id', '=', 'logins.staff_id')
		->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
		->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
		->whereBetween('hr_overtimes.ot_date', [$request->date_start, $request->date_end])
		->where('hr_overtimes.active', 1)
		->where('logins.active', 1)
		->where('pivot_staff_pivotdepts.main', 1)
		->where('pivot_dept_cate_branches.branch_id', $request->branch)
		->select('logins.username', 'staffs.name', 'pivot_dept_cate_branches.department', 'hr_overtimes.staff_id')
		->groupBy('hr_overtimes.staff_id')
		->orderBy('logins.username', 'ASC')
		->get();

		$branch = $request->branch;
		$title = $request->title;
		$month = $request->month;
		$year = $request->year;
		$date_start = $request->date_start;
		$date_end = $request->date_end;

		$pdf = PDF::loadView('humanresources.hrdept.overtime.overtimereport.printpdf', ['overtimes' => $overtimes, 'branch' => $branch, 'title' => $title, 'month' => $month, 'year' => $year, 'date_start' => $date_start, 'date_end' => $date_end]);
		// return $pdf->download('overtime_report ' . $current_datetime . '.pdf');
		return $pdf->stream();
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request): View
	{
		$overtimes = NULL;
		$branch = NULL;
		$title = NULL;
		$month = NULL;
		$year = NULL;
		$date_start = NULL;
		$date_end = NULL;

		if ($request->date_start != NULL && $request->date_end != NULL && $request->branch != NULL) {
			$overtimes = HROvertime::join('staffs', 'staffs.id', '=', 'hr_overtimes.staff_id')
			->join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
			->join('logins', 'hr_overtimes.staff_id', '=', 'logins.staff_id')
			->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
			->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
			->whereBetween('hr_overtimes.ot_date', [$request->date_start, $request->date_end])
			->where('hr_overtimes.active', 1)
			->where('logins.active', 1)
			->where('pivot_staff_pivotdepts.main', 1)
			->where('pivot_dept_cate_branches.branch_id', $request->branch)
			->select('logins.username', 'staffs.name', 'pivot_dept_cate_branches.department', 'hr_overtimes.staff_id')
			->groupBy('hr_overtimes.staff_id')
			->orderBy('logins.username', 'ASC')
			->get();

			$branch = $request->branch;
			$title = $request->title;
			$month = $request->month;
			$year = $request->year;
			$date_start = $request->date_start;
			$date_end = $request->date_end;
		}
		return view('humanresources.hrdept.overtime.overtimereport.index', ['overtimes' => $overtimes, 'branch' => $branch, 'title' => $title, 'month' => $month, 'year' => $year, 'date_start' => $date_start, 'date_end' => $date_end]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request): View
	{

	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Request $request): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request): JsonResponse
	{
		//
	}
}
