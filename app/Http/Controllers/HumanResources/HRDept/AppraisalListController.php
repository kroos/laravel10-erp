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
use App\Models\Staff;
use App\Models\HumanResources\DepartmentPivot;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AppraisalListController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
    $this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
    $this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
  }

  /**
   * Display a listing of the resource.
   */
  public function index(): View
  {
    $departments = DepartmentPivot::all();

    return view('humanresources.hrdept.appraisal.list.index', ['departments' => $departments]);
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
  public function show(): View
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(): View
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): JsonResponse
  {
    $currentDate = Carbon::now();

    DB::table('pivot_apoint_appraisals')
      ->whereNull('deleted_at')
      ->update(['active' => '1', 'updated_at' => $currentDate]);

    return response()->json([
      'message' => 'Successful Distributed',
      'status' => 'success'
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request): JsonResponse
  {
    //
  }
}
