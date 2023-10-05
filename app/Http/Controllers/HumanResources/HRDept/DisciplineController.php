<?php

namespace App\Http\Controllers\HumanResources\HRDept;

// for controller output
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// load models
use App\Models\HumanResources\HRDisciplinary;

// load paginator
use Illuminate\Pagination\Paginator;

// load validation
use App\Http\Requests\HumanResources\Disciplinary\DisciplinaryRequestStore;
use App\Http\Requests\HumanResources\Disciplinary\DisciplinaryRequestUpdate;

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
	public function store(DisciplinaryRequestStore $request): RedirectResponse
	{
		if ($request->file('softcopy')) {
			// UPLOAD SOFTCOPY
			$fileName = $request->file('softcopy')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d His');
			$file = $currentDate . '_' . $fileName;
			$request->file('softcopy')->storeAs('public/disciplinary', $file);

			// INSERT NEW DATABASE
			HRDisciplinary::create([
				'staff_id' => $request->staff_id,
				'disciplinary_action_id' => $request->disciplinary_action_id,
				'violation_id' => $request->violation_id,
				'date' => $request->date,
				'reason' => $request->reason,
				'softcopy' => $file,
			]);
		} else {
			// INSERT NEW DATABASE
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
	public function show(HRDisciplinary $discipline): View
	{
		return view('humanresources.hrdept.discipline.show', ['discipline' => $discipline]);
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
	public function update(DisciplinaryRequestUpdate $request, HRDisciplinary $discipline): RedirectResponse
	{
		if ($request->file('softcopy')) {
			// DELETE OLD SOFTCOPY
			Storage::delete("public/disciplinary/" . $request->old_softcopy);

			// UPLOAD NEW SOFTCOPY
			$fileName = $request->file('softcopy')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d His');
			$file = $currentDate . '_' . $fileName;
			$request->file('softcopy')->storeAs('public/disciplinary', $file);

			// UPDATE DATABASE
			$discipline->update([
				'disciplinary_action_id' => $request->disciplinary_action_id,
				'violation_id' => $request->violation_id,
				'date' => $request->date,
				'reason' => $request->reason,
				'softcopy' => $file,
			]);
		} else {
			// UPDATE DATABASE
			$discipline->update([
				'disciplinary_action_id' => $request->disciplinary_action_id,
				'violation_id' => $request->violation_id,
				'date' => $request->date,
				'reason' => $request->reason,
			]);
		}

		Session::flash('flash_message', 'Data successfully updated!');
		return Redirect::route('discipline.index', $discipline);
	}


	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, HRDisciplinary $discipline): JsonResponse
	{
		if ($request->table == 'discipline') {
			// DELETE SOFTCOPY
			Storage::delete("public/disciplinary/" . $request->softcopy);

			// DELETE DATABASE
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
