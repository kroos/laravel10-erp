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
    
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create($id): View
  {
    
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
dd()
  }

  /**
   * Display the specified resource.
   */
  public function show($appraisalform): View
  {
    
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(): View
  {
    
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(): RedirectResponse
  {
    
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(): RedirectResponse
  {
    //
  }
}
