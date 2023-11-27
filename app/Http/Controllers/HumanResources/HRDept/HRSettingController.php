<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// load request
use Illuminate\Http\Request;

// load model
use App\Models\Setting;

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

class HRSettingController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		// $this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);									// all high management
		$this->middleware('highMgmtAccess:1,14|31')->only('index', 'show');												// all high management
		// $this->middleware('highMgmtAccess:1,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);				// only hod and asst hod HR can access
		$this->middleware('highMgmtAccess:1,14')->only('create', 'store', 'edit', 'update', 'destroy');				// only hod and asst hod HR can access
		// $this->middleware('highMgmtAccess:1,14')->except('index', 'show');				// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index():View
	{
		$setting = Setting::where('system', 'HR')->get();
		return view('humanresources.hrdept.setting.index', ['setting' => $setting]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create():View
	{
		return view('humanresources.hrdept.setting.index');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request):JsonResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Setting $setting):View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Setting $setting):View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Setting $setting):JsonResponse
	{
		// dd($request->all());

		if($request->active == 'true') {
			Setting::find($request->id)->update(['active' => 1]);
			$active = 'Enable';
			return response()->json([
				'status' => 'Success Change Setting',
				'active' => $active
			]);
		}
		if($request->active == 'false') {
			Setting::find($request->id)->update(['active' => NULL]);
			$active = 'Disable';
			return response()->json([
				'status' => 'Success Change Setting',
				'active' => $active
			]);
		}
		if(json_encode( $request->active, JSON_NUMERIC_CHECK )) {
			Setting::find($request->id)->update(['active' => $request->active]);
			$active = 'Success';
			return response()->json([
				'status' => 'Success Change Setting',
				'active' => $active
			]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Setting $setting):JsonResponse
	{
		//
	}
}
