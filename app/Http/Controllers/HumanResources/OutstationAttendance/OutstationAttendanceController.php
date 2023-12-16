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
			$ip = file_get_contents(env('API_IP_ADDRESS'));
			// $ip = request()->ip();
			$data = \Location::get($ip);
			// dd($ip, $data);
			return view('humanresources.outstationattendance.index', ['data' => $data]);
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
		$validated = $request->validate([
				'outstation_id' => 'required',
			],
			[
				'outstation_id.required' => 'Please choose your location',
			],
			[
				'outstation_id' => 'Location',
			]
		);
		$ip = file_get_contents(env('API_IP_ADDRESS'));
		// $ip = request()->ip();
		$data = \Location::get($ip);
		if (now()->gt(Carbon::parse(now()->format('Y-m-d').' '.'13:00:00'))) {		// PM
			// dd('now greater than 1PM');

			$y = HROutstationAttendance::updateOrCreate(
					[
						'staff_id' => \Auth::user()->belongstostaff->id,
						'outstation_id' => $request->outstation_id,
						'date_attend' => now()->format('Y-m-d')
					],
					[
						'out' => now()->format('H:i:s'),
						'out_latitude' => $data->latitude,
						'out_longitude' => $data->longitude,
						'out_regionName' => $data->regionName,
						'out_cityName' => $data->cityName,
					]
			);
		} else {																	// AM
			// dd('now less than 1PM');
			$y = HROutstationAttendance::updateOrCreate(
					[
						'staff_id' => \Auth::user()->belongstostaff->id,
						'outstation_id' => $request->outstation_id,
						'date_attend' => now()->format('Y-m-d')
					],
					[
						'in' => now()->format('H:i:s'),
						'in_latitude' => $data->latitude,
						'in_longitude' => $data->longitude,
						'in_regionName' => $data->regionName,
						'in_cityName' => $data->cityName,
					]
			);
		}
		Session::flash('flash_message', 'Successfully Mark Attendance');
		return redirect()->back();
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
