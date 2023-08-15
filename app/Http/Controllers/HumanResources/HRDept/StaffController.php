<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

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
		$this->middleware(['auth', 'hraccess']);
		// $this->middleware('hraccess'/*, ['only' => ['show', 'edit', 'update']]*/);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return view('humanresources.hrdept.staff.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view('humanresources.hrdept.staff.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request, Staff $staff)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff)
	{
		return view('humanresources.hrdept.staff.show', compact(['staff']));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $staff)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Staff $staff)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff)
	{
		//
	}
}
