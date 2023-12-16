<?php

namespace App\Http\Controllers\HumanResources\HRDept;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// load models
use App\Models\Staff;
use App\Models\Customer;
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
	public function store(Request $request)/*: RedirectResponse*/
	{
		// dd($request->all());
		$cust = HROutstation::find($request->outstation_id)->customer_id;

		foreach ($request->staff_id as $v) {

			$query = HROutstation::where(function (Builder $query) use ($request) {
										$query->whereDate('date_from', '<=', $request->date_attend)
										->whereDate('date_to', '>=', $request->date_attend);
									})
									->where('active', 1)
									->where('staff_id', $v)
									->where('customer_id', $cust)
									->first();
			HROutstationAttendance::updateOrCreate([
					'outstation_id' => $query->id,
					'staff_id' => $v,
					'date_attend' => $request->date_attend
			],[
					'in' => Carbon::parse($request->in)->format('H:i:s'),
					'out' => Carbon::parse($request->out)->format('H:i:s'),
					'remarks' => ucwords(Str::lower($request->remarks)),
			]);
		}
		return redirect()->route('hroutstationattendance.index')->with('flash_message', 'Successfully Add Outstation Staff Attendance');
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
		return view('humanresources.hrdept.outstation.outstationattendance.edit', ['hroutstationattendance' => $hroutstationattendance]);
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
