<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRHolidayCalendar;


// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class HolidayCalendarController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}


	/**
	 * Display a listing of the resource.
	 */
	public function index():View
	{
		return view('humanresources.hrdept.setting.holidaycalendar.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create():View
	{
		return view('humanresources.hrdept.setting.holidaycalendar.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request):RedirectResponse
	{
		HRHolidayCalendar::create($request->except(['_token']));
		Session::flash('flash_message', 'Data successfully added!');
		return redirect( route('holidaycalendar.index') );
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRHolidayCalendar $holidaycalendar):View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRHolidayCalendar $holidaycalendar):View
	{
		return view('humanresources.hrdept.setting.holidaycalendar.edit', ['holidaycalendar' => $holidaycalendar]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRHolidayCalendar $holidaycalendar):RedirectResponse
	{
		HRHolidayCalendar::where('id', $holidaycalendar->id)->update($request->except(['_token', '_method']));
		Session::flash('flash_message', 'Data successfully edited!');
		return redirect( route('holidaycalendar.index') );
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRHolidayCalendar $holidaycalendar):JsonResponse
	{
		HRHolidayCalendar::destroy($holidaycalendar->id);
		return response()->json([
									'message' => 'Data deleted',
									'status' => 'success'
								]);
	}
}
