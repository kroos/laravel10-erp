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


// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeave;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class HRLeaveApprovalSupervisorController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show', 'update']]);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.leave.supervisorleaveapproval.index');
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
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeaveApprovalSupervisor $hRLeaveApprovalSupervisor): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeaveApprovalSupervisor $hRLeaveApprovalSupervisor): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeaveApprovalSupervisor $hRLeaveApprovalSupervisor): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeaveApprovalSupervisor $hRLeaveApprovalSupervisor): JsonResponse
	{
		//
	}
}
