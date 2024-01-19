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
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\HRAppraisalSection;
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

// load pdf
use Barryvdh\DomPDF\Facade\Pdf;

use Session;

class AppraisalFormMoreFunctionController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
    $this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
    $this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
  }

  /**
   * Print PDF
   */
  public function print(Request $request)
  {
    $pdf = PDF::loadView('humanresources.hrdept.appraisal.form.printpdf', ['id' => $request->id]);
    // return $pdf->download('appraisal form ' . $current_datetime . '.pdf');
    return $pdf->stream();
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
  public function create($id): View
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): JsonResponse
  {
    $pivotappraisal = DB::table('pivot_dept_appraisals')
      ->where('id', $request->id)
      ->first();

    $appraisals = DB::table('pivot_dept_appraisals')
      ->where('department_id', $pivotappraisal->department_id)
      ->where('version', $pivotappraisal->version)
      ->orderBy('sort', 'ASC')
      ->orderBy('id', 'ASC')
      ->get();

    $incremented = NULL;

    // ----------------------------- Pivot & Section -----------------------------
    foreach ($appraisals as $appraisal) {

      // Find Section
      $section_ori = HRAppraisalSection::findOrFail($appraisal->section_id);

      // Duplicate Section
      $section_new = $section_ori->replicate();
      $section_new->save();

      // Get New Section ID
      $section_new_id = HRAppraisalSection::select('id')->orderBy('id', 'DESC')->first();

      // Add New Version Number
      if (!$incremented) {
        $form_ver = $pivotappraisal->version + 1;
        $incremented = true;
      }

      // Duplicate Pivot Appraisal And Set New Version
      $section_new_id->belongstomanydepartmentpivot()->attach($pivotappraisal->department_id, [
        'version' => $form_ver,
      ]);

      // Loop Section Sub
      $section_subs = HRAppraisalSectionSub::where('section_id', $appraisal->section_id)->get();



      // ----------------------------- Section Sub -----------------------------
      foreach ($section_subs as $section_sub) {
        // Find Section Sub
        $section_sub_ori = HRAppraisalSectionSub::findOrFail($section_sub->id);

        // Duplicate Section Sub
        $section_sub_new = $section_sub_ori->replicate();
        $section_sub_new->section_id = $section_new_id->id;
        $section_sub_new->save();

        // Get New Section Sub ID
        $section_sub_new_id = HRAppraisalSectionSub::select('id')->orderBy('id', 'DESC')->first();

        // Loop Section Sub
        $main_questions = HRAppraisalMainQuestion::where('section_sub_id', $section_sub->id)->get();



        // ----------------------------- Main Question -----------------------------
        foreach ($main_questions as $main_question) {
          // Find Main Question
          $main_question_ori = HRAppraisalMainQuestion::findOrFail($main_question->id);

          // Duplicate Main Question
          $main_question_new = $main_question_ori->replicate();
          $main_question_new->section_sub_id = $section_sub_new_id->id;
          $main_question_new->save();

          // Get New Main Question ID
          $main_question_new_id = HRAppraisalMainQuestion::select('id')->orderBy('id', 'DESC')->first();

          // Loop Main Question
          $questions = HRAppraisalQuestion::where('main_question_id', $main_question->id)->get();



          // ----------------------------- Question -----------------------------
          foreach ($questions as $question) {
            // Find Main Question
            $question_ori = HRAppraisalQuestion::findOrFail($question->id);

            // Duplicate Main Question
            $question_new = $question_ori->replicate();
            $question_new->main_question_id = $main_question_new_id->id;
            $question_new->save();
          }
        }
      }
    }

    return response()->json([
      'message' => 'Successful Duplicate',
      'status' => 'success'
    ]);
  }

  /**
   * Display the specified resource.
   */
  public function show($appraisalform): View
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
  public function update(): RedirectResponse
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(): RedirectResponse
  {
    //
  }
}
