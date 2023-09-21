<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;


class LeaveController extends Controller
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
		return view('humanresources.hrdept.leave.index');
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
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeave $hrleave): View
	{
		return view('humanresources.hrdept.leave.show', ['hrleave' => $hrleave]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeave $hrleave): View
	{
		return view('humanresources.hrdept.leave.edit', ['hrleave' => $hrleave]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeave $hrleave): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hrleave): RedirectResponse
	{
		//
	}
}
