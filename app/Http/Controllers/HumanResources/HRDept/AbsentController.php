<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// models
use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;

use Illuminate\Database\Eloquent\Builder;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Session;
use Carbon\Carbon;

class AbsentController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$absents = HRAttendance::groupByRaw('YEAR(attend_date)')
						->selectRaw('YEAR(attend_date) as ayear')
						->whereIn('attendance_type_id', [1,2])

						->orderBy('ayear', 'DESC')
						->get();
						// ->ddrawsql();
		return view('humanresources.hrdept.discipline.absent.index', ['absents' => $absents]);
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
	public function show(HRLeaveReplacement $hrreplacementleave): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeaveReplacement $hrreplacementleave): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeaveReplacement $hrreplacementleave): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, HRLeaveReplacement $hrreplacementleave): JsonResponse
	{
		//
	}

}
