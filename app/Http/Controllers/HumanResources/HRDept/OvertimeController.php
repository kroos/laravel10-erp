<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HROvertime;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load support
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

class OvertimeController extends Controller
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
		$overtime = HROvertime::where('active', 1)->orderBy('ot_date', 'DESC')->get();
		return view('humanresources.hrdept.overtime.index', ['overtime' => $overtime]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.overtime.create');
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
	public function show(HROvertime $overtime): View
	{
		return view('humanresources.hrdept.overtime.show', ['overtime' => $overtime]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HROvertime $overtime): View
	{
		return view('humanresources.hrdept.overtime.edit', ['overtime' => $overtime]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HROvertime $overtime): RedirectResponse
	{
		$overtime->update(Arr::add($request->only(['ot_date', 'overtime_range_id', 'staff_id']), 'assign_staff_id', \Auth::user()->belongstostaff->id));
		Session::flash('flash_message', 'Successfully Update Staff Overtime');
		return redirect()->route('overtime.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROvertime $overtime): JsonResponse
	{
		$overtime->update(['active' => NULL]);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
