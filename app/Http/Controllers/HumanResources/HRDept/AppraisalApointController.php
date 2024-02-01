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

use Session;

class AppraisalApointController extends Controller
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

    return view('humanresources.hrdept.appraisal.apoint.index', ['departments' => $departments]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(): View
  {
    // $department = DepartmentPivot::where('id', $id)->first();
    // return view('humanresources.hrdept.appraisal.form.create', ['department' => $department]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {

    foreach ($request->evaluetee_id as $evaluateeid) {

      // HROvertime::create([
      //   'staff_id' => $v,
      //   'ot_date' => $request->ot_date,
      //   'overtime_range_id' => $request->overtime_range_id,
      //   'active' => 1,
      //   'assign_staff_id' => \Auth::user()->belongstostaff->id,
      //   'remark' => $remark,
      // ]);
      $evaluateeid = Staff::find($evaluateeid);
      $evaluateeid->belongstomanyevaluator()->attach($request->evaluator_id);

    }



    Session::flash('flash_message', 'Successfully Submit Appraisal Form.');
    return redirect()->route('appraisalapoint.index');
  }

  /**
   * Display the specified resource.
   */
  public function show(): View
  {
    // return view('humanresources.hrdept.appraisal.form.show', ['id' => $appraisalform]);
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
    // // --------------------------------- EDIT ---------------------------------
    // if ($request->update == 'section') {
    //   $section = HRAppraisalSection::find($request->id);

    //   $section->section = preg_replace('/>\s*</', '><', $request->section);
    //   $section->sort = $request->sort;

    //   $section->save();

    //   return response()->json([
    //     'message' => 'Successful Update',
    //     'status' => 'success'
    //   ]);
    // }


    // if ($request->update == 'section_sub') {
    //   $section_sub = HRAppraisalSectionSub::find($request->id);

    //   $section_sub->section_sub = preg_replace('/>\s*</', '><', $request->section_sub);
    //   $section_sub->sort = $request->sort;

    //   $section_sub->save();

    //   return response()->json([
    //     'message' => 'Successful Update',
    //     'status' => 'success'
    //   ]);
    // }


    // if ($request->update == 'main_question') {
    //   $main_question = HRAppraisalMainQuestion::find($request->id);

    //   $main_question->main_question = preg_replace('/>\s*</', '><', $request->main_question);
    //   $main_question->sort = $request->sort;
    //   $main_question->mark = $request->mark;

    //   $main_question->save();

    //   return response()->json([
    //     'message' => 'Successful Update',
    //     'status' => 'success'
    //   ]);
    // }


    // if ($request->update == 'question') {
    //   $question = HRAppraisalQuestion::find($request->id);

    //   $question->question = preg_replace('/>\s*</', '><', $request->question);
    //   $question->sort = $request->sort;
    //   $question->mark = $request->mark;

    //   $question->save();

    //   return response()->json([
    //     'message' => 'Successful Update',
    //     'status' => 'success'
    //   ]);
    // }

    // // --------------------------------- ADD ---------------------------------
    // if ($request->add == 'P1') {

    //   HRAppraisalSection::create([
    //     'sort' => $request->sort,
    //     'section' => preg_replace('/>\s*</', '><', $request->section),
    //   ]);

    //   return response()->json([
    //     'message' => 'Successful Add',
    //     'status' => 'success'
    //   ]);
    // }

    // if ($request->add == 'P2') {

    //   HRAppraisalSectionSub::create([
    //     'section_id' => $request->id,
    //     'sort' => $request->sort,
    //     'section_sub' => preg_replace('/>\s*</', '><', $request->section_sub),
    //   ]);

    //   return response()->json([
    //     'message' => 'Successful Add',
    //     'status' => 'success'
    //   ]);
    // }

    // if ($request->add == 'P3') {

    //   HRAppraisalMainQuestion::create([
    //     'section_sub_id' => $request->id,
    //     'mark' => $request->mark,
    //     'sort' => $request->sort,
    //     'main_question' => preg_replace('/>\s*</', '><', $request->main_question),
    //   ]);

    //   return response()->json([
    //     'message' => 'Successful Add',
    //     'status' => 'success'
    //   ]);
    // }

    // if ($request->add == 'P4') {

    //   HRAppraisalQuestion::create([
    //     'main_question_id' => $request->id,
    //     'mark' => $request->mark,
    //     'sort' => $request->sort,
    //     'question' => preg_replace('/>\s*</', '><', $request->question),
    //   ]);

    //   return response()->json([
    //     'message' => 'Successful Add',
    //     'status' => 'success'
    //   ]);
    // }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(): JsonResponse
  {
    // $datetime = Carbon::now();

    // $pivotappraisal = DB::table('pivot_dept_appraisals')
    //   ->where('id', $appraisalform)
    //   ->first();

    // $detachs =  DB::table('pivot_dept_appraisals')
    //   ->where('department_id', $pivotappraisal->department_id)
    //   ->where('version', $pivotappraisal->version)
    //   ->get();

    // foreach ($detachs as $detach) {
    //   DB::table('pivot_dept_appraisals')
    //     ->where('id', $detach->id)
    //     ->update(['deleted_at' => $datetime]);
    // }

    // return response()->json([
    //   'message' => 'Successful Deleted',
    //   'status' => 'success'
    // ]);
  }
}
