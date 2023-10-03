<?php
namespace App\Http\Controllers\HumanResources\HRDept;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HRDisciplinary;

// load paginator
use Illuminate\Pagination\Paginator;

// load validation
//use App\Http\Requests\HumanResources\Leave\HRLeaveRequestStore;

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
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StaffRequestStore $request)/*: RedirectResponse*/
	{
		//
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
	public function edit(Staff $staff): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(StaffRequestUpdate $request, Staff $staff)/*: RedirectResponse*/
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
