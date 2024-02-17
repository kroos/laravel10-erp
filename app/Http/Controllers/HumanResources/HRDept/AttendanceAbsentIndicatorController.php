<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AttendanceAbsentIndicatorController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,NULL', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request): View
	{
		return view('humanresources.hrdept.attendance.attendanceabsentindicator.index');
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
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $attendance): View
	{
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $attendance): View
	{
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(AttendanceRequestUpdate $request, HRAttendance $attendance): RedirectResponse
	{
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $attendance): RedirectResponse
	{
	}
}
