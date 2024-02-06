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
use App\Models\HumanResources\HRAppraisalSection;
use App\Models\HumanResources\HRAppraisalSectionSub;
use App\Models\HumanResources\HRAppraisalMainQuestion;
use App\Models\HumanResources\HRAppraisalQuestion;
use App\Models\HumanResources\OptAppraisalCategories;

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

class AppraisalFormController extends Controller
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
    $categorys = OptAppraisalCategories::all();

    return view('humanresources.hrdept.appraisal.form.index', ['categories' => $categorys]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create($id): View
  {
    $category = OptAppraisalCategories::where('id', $id)->first();
    return view('humanresources.hrdept.appraisal.form.create', ['category' => $category]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {

    $p1_end = $request->p1_end;
    $p2_end = $request->p2_end;
    $p3_end = $request->p3_end;
    $p4_end = $request->p4_end;

    // ---------------------------------------------- P1 ----------------------------------------------
    for ($p1_start = 1; $p1_start <= $p1_end; $p1_start++) {

      if ($request->has('p1' . $p1_start)) {
        foreach ($request->{'p1' . $p1_start} as $key => $val) {
          HRAppraisalSection::create([
            'sort' => $val['section_sort'],
            'section' => preg_replace('/>\s*</', '><', $val['section_text']),
          ]);

          $section_id = HRAppraisalSection::select('id')->orderBy('id', 'DESC')->first();

          // PIVOT DEPT APPRAISAL
          $form_version = DB::table('pivot_category_appraisals')
            ->where('category_id', $request->category_id)
            ->whereNotNull('version')
            ->orderBy('version', 'DESC')
            ->first();

          if ($form_version) {
            $form_ver = $form_version->version ?? 0;
          } else {
            $form_ver = 0;
          }

          if ($p1_start == 1) {
            $form_ver = $form_ver + 1;
          }

          $section_id->belongstomanycategorypivot()->attach($request->category_id, [
            'version' => $form_ver,
          ]);


          // ---------------------------------------------- P2 ----------------------------------------------
          for ($p2_start = 1; $p2_start <= $p2_end; $p2_start++) {

            if ($request->has('p2' . $p1_start . $p2_start)) {
              foreach ($request->{'p2' . $p1_start . $p2_start} as $key => $val) {
                HRAppraisalSectionSub::create([
                  'section_id' => $section_id->id,
                  'sort' => $val['sectionsub_sort'],
                  'section_sub' => preg_replace('/>\s*</', '><', $val['sectionsub_text']),
                ]);

                $sectionsub_id = HRAppraisalSectionSub::select('id')->orderBy('id', 'DESC')->first();


                // ---------------------------------------------- P3 ----------------------------------------------
                for ($p3_start = 1; $p3_start <= $p3_end; $p3_start++) {

                  if ($request->has('p3' . $p1_start . $p2_start . $p3_start)) {
                    foreach ($request->{'p3' . $p1_start . $p2_start . $p3_start} as $key => $val) {
                      HRAppraisalMainQuestion::create([
                        'section_sub_id' => $sectionsub_id->id,
                        'sort' => $val['mainquestion_sort'],
                        'mark' => $val['mainquestion_mark'],
                        'main_question' => preg_replace('/>\s*</', '><', $val['mainquestion_text']),
                      ]);

                      $mainquestion_id = HRAppraisalMainQuestion::select('id')->orderBy('id', 'DESC')->first();


                      // ---------------------------------------------- P4 ----------------------------------------------
                      for ($p4_start = 1; $p4_start <= $p4_end; $p4_start++) {

                        if ($request->has('p4' . $p1_start . $p2_start . $p3_start . $p4_start)) {
                          foreach ($request->{'p4' . $p1_start . $p2_start . $p3_start . $p4_start} as $key => $val) {
                            HRAppraisalQuestion::create([
                              'main_question_id' => $mainquestion_id->id,
                              'sort' => $val['question_sort'],
                              'mark' => $val['question_mark'],
                              'question' => preg_replace('/>\s*</', '><', $val['question_text']),
                            ]);
                          }
                        }
                      }
                      // ---------------------------------------------- P4 ----------------------------------------------
                    }
                  }
                }
                // ---------------------------------------------- P3 ----------------------------------------------
              }
            }
          }
          // ---------------------------------------------- P2 ----------------------------------------------
        }
      }
    }
    // ---------------------------------------------- P1 ----------------------------------------------

    Session::flash('flash_message', 'Successfully Submit Appraisal Form.');
    return redirect()->route('appraisalform.index');
  }

  /**
   * Display the specified resource.
   */
  public function show($appraisalform): View
  {
    return view('humanresources.hrdept.appraisal.form.show', ['id' => $appraisalform]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($appraisalform): View
  {
    return view('humanresources.hrdept.appraisal.form.edit', ['id' => $appraisalform]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): JsonResponse
  {
    // --------------------------------- EDIT ---------------------------------
    if ($request->update == 'section') {
      $section = HRAppraisalSection::find($request->id);

      $section->section = preg_replace('/>\s*</', '><', $request->section);
      $section->sort = $request->sort;

      $section->save();

      return response()->json([
        'message' => 'Successful Update',
        'status' => 'success'
      ]);
    }


    if ($request->update == 'section_sub') {
      $section_sub = HRAppraisalSectionSub::find($request->id);

      $section_sub->section_sub = preg_replace('/>\s*</', '><', $request->section_sub);
      $section_sub->sort = $request->sort;

      $section_sub->save();

      return response()->json([
        'message' => 'Successful Update',
        'status' => 'success'
      ]);
    }


    if ($request->update == 'main_question') {
      $main_question = HRAppraisalMainQuestion::find($request->id);

      $main_question->main_question = preg_replace('/>\s*</', '><', $request->main_question);
      $main_question->sort = $request->sort;
      $main_question->mark = $request->mark;

      $main_question->save();

      return response()->json([
        'message' => 'Successful Update',
        'status' => 'success'
      ]);
    }


    if ($request->update == 'question') {
      $question = HRAppraisalQuestion::find($request->id);

      $question->question = preg_replace('/>\s*</', '><', $request->question);
      $question->sort = $request->sort;
      $question->mark = $request->mark;

      $question->save();

      return response()->json([
        'message' => 'Successful Update',
        'status' => 'success'
      ]);
    }

    // --------------------------------- ADD ---------------------------------
    if ($request->add == 'P1') {

      HRAppraisalSection::create([
        'sort' => $request->sort,
        'section' => preg_replace('/>\s*</', '><', $request->section),
      ]);

      return response()->json([
        'message' => 'Successful Add',
        'status' => 'success'
      ]);
    }

    if ($request->add == 'P2') {

      HRAppraisalSectionSub::create([
        'section_id' => $request->id,
        'sort' => $request->sort,
        'section_sub' => preg_replace('/>\s*</', '><', $request->section_sub),
      ]);

      return response()->json([
        'message' => 'Successful Add',
        'status' => 'success'
      ]);
    }

    if ($request->add == 'P3') {

      HRAppraisalMainQuestion::create([
        'section_sub_id' => $request->id,
        'mark' => $request->mark,
        'sort' => $request->sort,
        'main_question' => preg_replace('/>\s*</', '><', $request->main_question),
      ]);

      return response()->json([
        'message' => 'Successful Add',
        'status' => 'success'
      ]);
    }

    if ($request->add == 'P4') {

      HRAppraisalQuestion::create([
        'main_question_id' => $request->id,
        'mark' => $request->mark,
        'sort' => $request->sort,
        'question' => preg_replace('/>\s*</', '><', $request->question),
      ]);

      return response()->json([
        'message' => 'Successful Add',
        'status' => 'success'
      ]);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($appraisalform): JsonResponse
  {
    $datetime = Carbon::now();

    $pivotappraisal = DB::table('pivot_category_appraisals')
      ->where('id', $appraisalform)
      ->first();

    $detachs =  DB::table('pivot_category_appraisals')
      ->where('category_id', $pivotappraisal->category_id)
      ->where('version', $pivotappraisal->version)
      ->get();

    foreach ($detachs as $detach) {
      DB::table('pivot_category_appraisals')
        ->where('id', $detach->id)
        ->update(['deleted_at' => $datetime]);
    }

    return response()->json([
      'message' => 'Successful Deleted',
      'status' => 'success'
    ]);
  }
}
