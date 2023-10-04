<?php

namespace App\Http\Controllers\HumanResources\HRDept;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

// load models
use App\Models\HumanResources\HRDisciplinary;

// load paginator
use Illuminate\Pagination\Paginator;

// load validation
//use App\Http\Requests\HumanResources\Disciplinary\DisciplinaryRequestStore;
//use App\Http\Requests\HumanResources\Disciplinary\DisciplinaryRequestUpdate;

use Session;

use Carbon\Carbon;

class DisciplineController extends Controller
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
		Paginator::useBootstrap();
		$disciplinary = HRDisciplinary::orderBy('date', 'desc')->paginate(30);
		return view('humanresources.hrdept.discipline.index', compact('disciplinary'));
	}


	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.discipline.create');
	}


	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		if ($request->file('softcopy')) {
			$fileName = $request->file('softcopy')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d H:i:s');
			$file = $currentDate . '_' . $fileName;



			$request->file('softcopy')->storeAs('uploads',$file);





			HRDisciplinary::create([
				'staff_id' => $request->staff_id,
				'disciplinary_action_id' => $request->disciplinary_action_id,
				'violation_id' => $request->violation_id,
				'date' => $request->date,
				'reason' => $request->reason,
				'softcopy' => $file,
			]);
		} else {
			HRDisciplinary::create([
				'staff_id' => $request->staff_id,
				'disciplinary_action_id' => $request->disciplinary_action_id,
				'violation_id' => $request->violation_id,
				'date' => $request->date,
				'reason' => $request->reason,
			]);
		}

		Session::flash('flash_message', 'Successfully Add Discipline.');
		return redirect()->route('discipline.index');
	}


	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff): View
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRDisciplinary $discipline): View
	{
		return view('humanresources.hrdept.discipline.edit', ['discipline' => $discipline]);
	}


	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRDisciplinary $discipline): RedirectResponse
	{
		// $rleave->update($request->only(['date_start', 'date_end', 'customer_id', 'reason', 'leave_total', 'leave_utilize', 'leave_balance']));

		// Session::flash('flash_message', 'Data successfully updated!');
		// return Redirect::route('rleave.index', $rleave);
	}


	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, HRDisciplinary $discipline): JsonResponse
	{
		if ($request->table == 'discipline') {
			$HRDisciplinary = HRDisciplinary::destroy(
				[
					'id' => $discipline['id']
				]
			);

			return response()->json([
				'status' => 'success',
				'message' => 'Your discipline has been deleted.',
			]);
		}
	}
}
