<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HRAttendance;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load cursor pagination
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;

// load support
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

class OvertimeController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['create', 'store', 'index', 'show']]);			// all high management
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['edit', 'update', 'destroy']]);						// only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		Paginator::useBootstrapFive();
		// $sa = HROvertime::SelectRaw('COUNT(staff_id) as totalstaff, ot_date')
		// 				->where('active', 1)
		// 				// ->groupByRaw('YEAR(ot_date)')
		// 				->groupByRaw('MONTH(ot_date)')
		// 				->orderBy('ot_date', 'DESC')
		// 				// ->get();
		// 				// ->ddRawSql();
		// 				->cursorPaginate(1);
		// // dd($sa);
		// $overtime = HROvertime::select('*')
		// 				// ->whereYear('ot_date', $sa->first()?->ot_date)
		// 				->where('active', 1)
		// 				->orderBy('ot_date', 'DESC')
		// 				->cursorPaginate($sa->first()?->totalstaff);

		$overtime = HROvertime::select('*')
						// ->whereYear('ot_date', $sa->first()?->ot_date)
						->where('active', 1)
						->orderBy('ot_date', 'DESC')
						->get();

		return view('humanresources.hrdept.overtime.index', ['overtime' => $overtime/*, 'sa' => $sa*/]);
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
		// dd($request->all());
		foreach ($request->staff_id as $v) {
			HROvertime::create([
									'staff_id' => $v,
									'ot_date' => $request->ot_date,
									'overtime_range_id' => $request->overtime_range_id,
									'active' => 1,
									'assign_staff_id' => \Auth::user()->belongstostaff->id,
									'remark' => ucwords(Str::of($request->remark)->lower()),
								]);
		}
		Session::flash('flash_message', 'Successfully Add Staff Overtime');
		return redirect()->route('overtime.index');
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
		$dat = Arr::add($request->only(['ot_date', 'overtime_range_id', 'staff_id']), 'assign_staff_id', \Auth::user()->belongstostaff->id);
		$data = Arr::add($dat, 'remarks', ucwords(Str::of($request->remark)->lower()));
		$overtime->update();
		Session::flash('flash_message', 'Successfully Update Staff Overtime');
		return redirect()->route('overtime.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROvertime $overtime): JsonResponse
	{
		// remove from attendance
		$r = HRAttendance::where('overtime_id', $overtime->id)->get();
		foreach ($r as $c) {
			HRAttendance::where('id', $c->id)->update(['overtime_id' => null]);
		}
		$overtime->update(['active' => NULL]);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
