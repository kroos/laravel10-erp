<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// load model
use App\Models\HumanResources\OptWorkingHour;

// load request
use Illuminate\Http\Request;

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

class WorkingHourController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14,31', ['only' => ['index', 'show']]);                                  // all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);       // only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index():View
	{
		return view('humanresources.hrdept.setting.workinghour.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create():View
	{
		return view('humanresources.hrdept.setting.workinghour.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request):RedirectResponse
	{
		$datefrom = Carbon::parse($request->effective_date_start);
		$dateto = Carbon::parse($request->effective_date_end);

		$onedaybefore = $datefrom->copy()->subDay()->format('Y-m-d');
		$onedayafter = $dateto->copy()->addDay()->format('Y-m-d');

		$startOfYear = $datefrom->copy()->startOfYear()->format('Y-m-d');
		$endOfYear   = $datefrom->copy()->endOfYear()->format('Y-m-d');

		$year = $datefrom->copy()->format('Y');

		OptWorkingHour::insert([ //create method only for 1 row, for multiple row, use insert method
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '13:00:00',
				'time_start_pm' => '14:00:00',
				'time_end_pm' => '18:05:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $onedaybefore,
				'year' => $year,
				'category' => 1,
				'group' => 0,
				'remarks' => 'Normal - Normal days before Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '13:00:00',
				'time_start_pm' => '13:45:00',
				'time_end_pm' => '16:40:00',
				'effective_date_start' => $datefrom->format('Y-m-d'),
				'effective_date_end' => $dateto->format('Y-m-d'),
				'year' => $year,
				'category' => 2,
				'group' => 0,
				'remarks' => 'Normal - During Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '12:45:00',
				'time_start_pm' => '14:35:00',
				'time_end_pm' => '16:45:00',
				'effective_date_start' => $datefrom->format('Y-m-d'),
				'effective_date_end' => $dateto->format('Y-m-d'),
				'year' => $year,
				'category' => 3,
				'group' => 0,
				'remarks' => 'Normal - During Ramadhan - Friday'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '13:00:00',
				'time_start_pm' => '14:00:00',
				'time_end_pm' => '18:05:00',
				'effective_date_start' => $onedayafter,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 4,
				'group' => 0,
				'remarks' => 'Normal - Normal Days After Ramadhan'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:45:00',
				'time_start_pm' => '14:35:00',
				'time_end_pm' => '18:05:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $onedaybefore,
				'year' => $year,
				'category' => 3,
				'group' => 0,
				'remarks' => 'Normal - Friday - Before Ramadhan'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:45:00',
				'time_start_pm' => '14:35:00',
				'time_end_pm' => '18:05:00',
				'effective_date_start' => $onedayafter,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 3,
				'group' => 0,
				'remarks' => 'Normal - Friday - After Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '11:30:00',
				'time_start_pm' => '12:30:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 6,
				'group' => 1,
				'remarks' => 'Maintenance - Normal Days - Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '13:45:00',
				'time_start_pm' => '14:45:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 7,
				'group' => 1,
				'remarks' => 'Maintenance - Friday - Normal Days - Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '12:00:00',
				'time_start_pm' => '13:00:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 8,
				'group' => 1,
				'remarks' => 'Maintenance - Half Day Leave'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '12:00:00',
				'time_start_pm' => '13:00:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 9,
				'group' => 1,
				'remarks' => 'Maintenance - Friday - Ramadhan'
			],
		]);
		Session::flash('flash_message', 'Data successfully generated!');
		return redirect( route('workinghour.index') );
	}

	/**
	 * Display the specified resource.
	 */
	public function show(OptWorkingHour $workinghour):View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(OptWorkingHour $workinghour):View
	{
		return view('humanresources.hrdept.setting.workinghour.edit', ['workinghour' => $workinghour]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, OptWorkingHour $workinghour):RedirectResponse
	{
		$t = OptWorkingHour::where('id', $workinghour->id)->update([
			'time_start_am' => Carbon::parse($request->time_start_am)->format('H:i:s'),
			'time_end_am' => Carbon::parse($request->time_end_am)->format('H:i:s'),
			'time_start_pm' => Carbon::parse($request->time_start_pm)->format('H:i:s'),
			'time_end_pm' => Carbon::parse($request->time_end_pm)->format('H:i:s'),
			'effective_date_start' => $request->effective_date_start,
			'effective_date_end' => $request->effective_date_end,
		]);
		Session::flash('flash_message', 'Data successfully edited!');
		return redirect( route('workinghour.index') );
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(OptWorkingHour $workinghour):JsonResponse
	{
		//
	}
}
