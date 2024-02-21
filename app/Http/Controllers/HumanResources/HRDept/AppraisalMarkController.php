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
// use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\HRAppraisalMark;

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

class AppraisalMarkController extends Controller
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

    return view('humanresources.hrdept.appraisal.mark.index', ['departments' => $departments]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create($id): View
  {
    return view('humanresources.hrdept.appraisal.mark.create', ['id' => $id]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {

    if ($request->has('arraymark1')) {
      $marks1 = array_unique($request->input('arraymark1'));

      foreach ($marks1 as $mark1) {
        HRAppraisalMark::create([
          'pivot_apoint_id' => $request->pivot_apoint_id,
          'question_id' => $request->$mark1,
          'mark' => '2',
        ]);
      }
    }

    // if ($request->has('arraymark2')) {
    //   $marks2 = array_unique($request->input('arraymark2'));

    //   foreach ($marks2 as $mark2) {
        
    //   }
    // }

    // if ($request->has('arraymark3')) {
    //   $marks3 = array_unique($request->input('arraymark3'));

    //   foreach ($marks3 as $mark3) {
        
    //   }
    // }

    // if ($request->has('arraymark4')) {
    //   $marks4 = array_unique($request->input('arraymark4'));

    //   foreach ($marks4 as $mark4) {
        
    //   }
    // }

    Session::flash('flash_message', 'Successfully Submit Appraisal Form.');
    return redirect()->route('appraisalmark.index');
  }

  /**
   * Display the specified resource.
   */
  public function show(): View
  {
    // return view('humanresources.hrdept.appraisal.mark.show', ['id' => $appraisalform]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(): View
  {
    // return view('humanresources.hrdept.appraisal.form.edit', ['id' => $appraisalform]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): JsonResponse
  {
    // $currentDate = Carbon::now();
    // $date = $currentDate->format('Y-m-d');

    // DB::table('pivot_apoint_appraisals')
    //   ->whereNull('deleted_at')
    //   ->update(['distribute_date' => $date, 'updated_at' => $currentDate]);

    // return response()->json([
    //   'message' => 'Successful Distributed',
    //   'status' => 'success'
    // ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request): JsonResponse
  {
    //
  }
}
