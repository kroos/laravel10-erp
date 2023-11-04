<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HROutstation;

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

class OutstationController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,6|14|31', ['only' => ['index', 'show']]);                                  // all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);       // only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.outstation.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.outstation.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		// dd($request->all());
		foreach ($request->staff_id as $s) {
			HROutstation::create([
				'staff_id' => $s,
				'customer_id' => $request->customer_id,
				'date_from' => $request->date_from,
				'date_to' => $request->date_to,
				'remarks' => $request->remarks,
				'active' => 1,
			]);
		}
		Session::flash('flash_message', 'Successfully add staff for outstation');
		return redirect()->route('outstation.index');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HROutstation $outstation): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HROutstation $outstation): View
	{
		return view('humanresources.hrdept.outstation.edit', ['outstation' => $outstation]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HROutstation $outstation): RedirectResponse
	{
		// dd($request->all());
		$outstation->update($request->only(['staff_id', 'customer_id', 'date_from', 'date_to', 'remarks']));
		Session::flash('flash_message', 'Successfully edit staff for outstation');
		return redirect()->route('outstation.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROutstation $outstation): JsonResponse
	{
		$outstation->update(['active' => NULL]);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
