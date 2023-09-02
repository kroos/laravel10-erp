<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Leave\HRLeaveRequestStore;

// load models
use App\Models\Staff;

// load array helper
use Illuminate\Support\Arr;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

class StaffController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);								// all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);		// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.staff.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.staff.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request, Staff $staff): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff): View
	{
		return view('humanresources.hrdept.staff.show', compact(['staff']));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $staff): View
	{
		return view('humanresources.hrdept.staff.edit', compact(['staff']));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Staff $staff): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff): RedirectResponse
	{
		//
	}
}
