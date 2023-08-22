<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

// load validation


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
			->where('staffs.active', 1)
			// ->whereDate('attend_date', Carbon::now()->format('Y-m-d'))
			->whereDate('attend_date', $sa->first()->attend_date)
			->orderBy('hr_attendances.attend_date', 'DESC')
			->cursorPaginate($sa->first()->totalactivestaff);
		// $attendance->appends(['attend_date' => Carbon::now()->format('Y-m-d')]);;

		return view('humanresources.hrdept.attendance.index', ['attendance' => $attendance, 'sa' => $sa]);
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
	public function edit(Staff $staff)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Staff $staff)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff)
	{
		//
	}
}
