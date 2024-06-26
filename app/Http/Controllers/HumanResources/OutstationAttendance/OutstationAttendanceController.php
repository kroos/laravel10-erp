<?php
namespace App\Http\Controllers\HumanResources\OutstationAttendance;

use App\Http\Controllers\Controller;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// models
use App\Models\Staff;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon library
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class OutstationAttendanceController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$locations = HROutstation::where('staff_id', \Auth::user()->belongstostaff->id)
												->where(function (Builder $query) {
													$query->whereDate('date_from', '<=', now())
														->whereDate('date_to', '>=', now());
												})
												->where('active', 1)
												->get();
		$m = HROutstationAttendance::whereDate('date_attend', now())
												->where('staff_id', \Auth::user()->belongstostaff->id)
												->whereNotNull('outstation_id')
												// ->whereNull('out')
												// ->ddrawsql();
												->get();
												// dump($m->count());

		return view('humanresources.outstationattendance.index', [
			'locations' => $locations,
			'm' => $m
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		// dd($request->all());
		$request->validate([
				'outstation_id' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',
				'accuracy' => 'required|lt:25',
			],
			[
				'outstation_id.required' => 'Please choose your :attribute',
				'latitude.required' => 'Please make sure you click on "allow" when the system accessing your location.',
				'longitude.required' => 'Please make sure you click on "allow" when the system accessing your location.',
				'accuracy.required' => 'Please make sure you click on "allow" when the system accessing your location.',
				'accuracy.lt:25' => 'Please refresh this page untill you get :attribute below than 25.',
			],
			[
				'outstation_id' => 'Location',
				'latitude' => 'Latitude',
				'longitude' => 'Longitude',
				'accuracy' => 'Accuracy',
		]);

		if ($request->accuracy > 25) {
			return redirect()->back()->with('flash_danger', 'It Seems your accuracy was too high. Please refresh this page, click on "Allow", and try to get Accuracy below than 25');
		}

		$inouts = HROutstationAttendance::where([
																							['staff_id', \Auth::user()->belongstostaff->id],
																							['outstation_id', $request->outstation_id,],
																							['date_attend', now()->format('Y-m-d')],
																			])->get();

		if ($inouts->count()) {
			if (!is_null($inouts->first()->out)) {
				return redirect()->back()->with('flash_danger', 'You already marked your attendance');
			}
			HROutstationAttendance::updateOrCreate(
					[
						'staff_id' => \Auth::user()->belongstostaff->id,
						'outstation_id' => $request->outstation_id,
						'date_attend' => now()->format('Y-m-d')
					],
					[
						'out' => now()->format('H:i:s'),
						'out_latitude' => $request->latitude,
						'out_longitude' => $request->longitude,
					]);
				} else {
					HROutstationAttendance::create(
						[
							'staff_id' => \Auth::user()->belongstostaff->id,
							'outstation_id' => $request->outstation_id,
							'date_attend' => now()->format('Y-m-d'),
							'in' => now()->format('H:i:s'),
							'in_latitude' => $request->latitude,
							'in_longitude' => $request->longitude,
					]);
			}
		return redirect()->back()->with('flash_message', 'Successfully Mark Attendance');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HROutstationAttendance $outstationattendance): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HROutstationAttendance $outstationattendance): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HROutstationAttendance $outstationattendance): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROutstationAttendance $outstationattendance): JsonResponse
	{
		//
	}
}
