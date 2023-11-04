<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRLeaveMaternity;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class MaternityLeaveController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.hrdept.setting.maternityleave.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.setting.maternityleave.create');
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
	public function show(HRLeaveMaternity $maternityleave): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeaveMaternity $maternityleave): View
	{
		return view('humanresources.hrdept.setting.maternityleave.edit', ['maternityleave' => $maternityleave]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeaveMaternity $maternityleave): RedirectResponse
	{
		$maternityleave->update($request->except(['_method', '_token']));
		Session::flash('flash_message', 'Data successfully updated!');
		return Redirect::route('maternityleave.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeaveMaternity $maternityleave)
	{
		//
	}
}
