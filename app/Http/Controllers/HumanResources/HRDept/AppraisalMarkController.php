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
use App\Models\HumanResources\HRAppraisalSectionSub;
use App\Models\HumanResources\HRAppraisalMainQuestion;
use App\Models\HumanResources\HRAppraisalQuestion;

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
    $currentDate = Carbon::now();

    HRAppraisalMark::where('pivot_apoint_id', '=', $request->pivot_apoint_id)->delete();

    if ($request->has('arraymark1')) {
      $marks1 = array_unique($request->input('arraymark1'));

      foreach ($marks1 as $mark1) {
        if ($request->$mark1 != NULL) {

          HRAppraisalMark::create([
            'pivot_apoint_id' => $request->pivot_apoint_id,
            'section_id' => $request->section1,
            'question_id' => $request->$mark1,
            'mark' => HRAppraisalQuestion::find($request->$mark1)->mark,
          ]);
        }
      }
    }

    if ($request->has('arraymark2')) {
      $marks2 = array_unique($request->input('arraymark2'));

      foreach ($marks2 as $mark2) {
        if ($request->$mark2 != NULL) {
          $id2 = 'id' . $mark2;

          HRAppraisalMark::create([
            'pivot_apoint_id' => $request->pivot_apoint_id,
            'section_id' => $request->section2,
            'section_sub_id' => $request->$id2,
            'mark' => $request->$mark2,
          ]);
        }
      }
    }

    if ($request->has('arraymark3')) {
      $marks3 = array_unique($request->input('arraymark3'));

      foreach ($marks3 as $mark3) {
        if ($request->$mark3 != NULL) {
          $id3 = 'id' . $mark3;

          HRAppraisalMark::create([
            'pivot_apoint_id' => $request->pivot_apoint_id,
            'section_id' => $request->section3,
            'section_sub_id' => $request->$id3,
            'remark' => $request->$mark3,
          ]);
        }
      }
    }

    if ($request->has('arraymark4')) {
      $marks4 = array_unique($request->input('arraymark4'));

      foreach ($marks4 as $mark4) {
        if ($request->$mark4 != NULL) {

          HRAppraisalMark::create([
            'pivot_apoint_id' => $request->pivot_apoint_id,
            'section_id' => $request->section4,
            'main_question_id' => $request->$mark4,
          ]);
        }
      }
    }

    $total_mark = HRAppraisalMark::where('pivot_apoint_id', $request->pivot_apoint_id)->sum('mark');

    if ($request->has('final')) {

      $full_mark = $request->total_mark1 + $request->total_mark2;

      DB::table('pivot_apoint_appraisals')
        ->where('id', $request->pivot_apoint_id)
        ->update([
          'appraisal_category_id' => $request->appraisal_category_id,
          'appraisal_category_version' => $request->appraisal_category_version,
          'full_mark' => $full_mark,
          'total_mark' => $total_mark,
          'finalise_date' => $currentDate,
        ]);
    }

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
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): JsonResponse
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request): JsonResponse
  {
    //
  }
}
