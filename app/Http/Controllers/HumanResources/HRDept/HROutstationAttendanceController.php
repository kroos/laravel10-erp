<?php

namespace App\Http\Controllers\HumanResources\HRDept;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// load models
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;
use App\Models\HumanResources\HRAttendance;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class HROutstationAttendanceController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,6|14', ['only' => ['create', 'store', 'index', 'show']]);								// all high management
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['edit', 'update', 'destroy']]);       								// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$hroa = HROutstationAttendance::where(function (Builder $query) {
											$query->whereDate('date_attend', '>=', now()->startofYear())
											->whereDate('date_attend', '<=', now()->endofYear());
										})
										->orderBy('date_attend', 'DESC')
										->orderBy('in', 'ASC')
										->get();
										// ->ddrawSql();
		return view('humanresources.hrdept.outstation.outstationattendance.index', ['hroa' => $hroa]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.outstation.outstationattendance.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{

	}

	/**
	 * Display the specified resource.
	 */
	public function show(HROutstationAttendance $hroutstationattendance): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HROutstationAttendance $hroutstationattendance): View
	{

	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HROutstationAttendance $hroutstationattendance): RedirectResponse
	{

	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROutstationAttendance $hroutstationattendance): JsonResponse
	{

	}
}
