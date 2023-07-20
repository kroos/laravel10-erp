<?php

namespace App\Http\Controllers\HumanResources\Config;

use App\Http\Controllers\Controller;

// load model
use App\Model\HumanResources\WorkingHour;
use Illuminate\Http\Request;
use \Carbon\Carbon;
use Session;

class WorkingHourController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('humanresources.config.index');
	}

	public function create()
	{
		return view('workinghour.create');
	}

	public function store(Request $request)
	{
		$datefrom = Carbon::parse($request->effective_date_start);
		$dateto = Carbon::parse($request->effective_date_end);

		$onedaybefore = $datefrom->copy()->subDay()->format('Y-m-d');
		$onedayafter = $dateto->copy()->addDay()->format('Y-m-d');

		$startOfYear = $datefrom->copy()->startOfYear()->format('Y-m-d');
		$endOfYear   = $datefrom->copy()->endOfYear()->format('Y-m-d');

		$year = $datefrom->copy()->format('Y');

		WorkingHour::insert([ //create method only for 1 row, for multiple row, use insert method
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:30:00',
				'time_start_pm' => '13:30:00',
				'time_end_pm' => '17:45:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $onedaybefore,
				'year' => $year,
				'category' => 1,
				'maintenance' => 0,
				'remarks' => 'Normal - Normal days before ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '12:00:00',
				'time_start_pm' => '13:00:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $datefrom->format('Y-m-d'),
				'effective_date_end' => $dateto->format('Y-m-d'),
				'year' => $year,
				'category' => 2,
				'maintenance' => 0,
				'remarks' => 'Normal - During Ramadhan'
			],
			[
				'time_start_am' => '08:00:00',
				'time_end_am' => '12:45:00',
				'time_start_pm' => '14:45:00',
				'time_end_pm' => '17:00:00',
				'effective_date_start' => $datefrom->format('Y-m-d'),
				'effective_date_end' => $dateto->format('Y-m-d'),
				'year' => $year,
				'category' => 3,
				'maintenance' => 0,
				'remarks' => 'Normal - During Ramadhan - Friday'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:30:00',
				'time_start_pm' => '13:30:00',
				'time_end_pm' => '17:45:00',
				'effective_date_start' => $onedayafter,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 4,
				'maintenance' => 0,
				'remarks' => 'Normal - Normal Days After Ramadhan'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:30:00',
				'time_start_pm' => '14:45:00',
				'time_end_pm' => '17:45:00',
				'effective_date_start' => $startOfYear,
				'effective_date_end' => $onedaybefore,
				'year' => $year,
				'category' => 3,
				'maintenance' => 0,
				'remarks' => 'Normal - Friday - Before Ramadhan'
			],
			[
				'time_start_am' => '08:30:00',
				'time_end_am' => '12:30:00',
				'time_start_pm' => '14:45:00',
				'time_end_pm' => '17:45:00',
				'effective_date_start' => $onedayafter,
				'effective_date_end' => $endOfYear,
				'year' => $year,
				'category' => 3,
				'maintenance' => 0,
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
				'maintenance' => 1,
				'remarks' => 'Maintenance - Normal Days Ramadhan'
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
				'maintenance' => 1,
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
				'maintenance' => 1,
				'remarks' => 'Maintenance - Half Day Leave'
			],
		]);
		Session::flash('flash_message', 'Data successfully edited!');
		return redirect( route('workingHour.index') );
	}

	public function show(WorkingHour $workingHour)
	{
	//
	}

	public function edit(WorkingHour $workingHour)
	{
		return view('generalAndAdministrative.hr.hrsettings.workinghour.edit', compact(['workingHour']) );
	}

	public function update(Request $request, WorkingHour $workingHour)
	{
		$t = WorkingHour::where('id', $workingHour->id)->update([
			'time_start_am' => Carbon::parse($request->time_start_am)->format('H:i:s'),
			'time_end_am' => Carbon::parse($request->time_end_am)->format('H:i:s'),
			'time_start_pm' => Carbon::parse($request->time_start_pm)->format('H:i:s'),
			'time_end_pm' => Carbon::parse($request->time_end_pm)->format('H:i:s'),
			'effective_date_start' => $request->effective_date_start,
			'effective_date_end' => $request->effective_date_end,
		]);
		Session::flash('flash_message', 'Data successfully edited!');
		return redirect( route('workingHour.index') );
	}

	public function destroy(WorkingHour $workingHour)
	{

	}
}

