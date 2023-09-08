<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// load request
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HRStaffChildren;

class ChildrenController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);                                // all high management
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);       // only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		//
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
	public function show(HRStaffChildren $child): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRStaffChildren $child): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRStaffChildren $child): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRStaffChildren $child): JsonResponse
	{
		HRStaffChildren::destroy($child->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
