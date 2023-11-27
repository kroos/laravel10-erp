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
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRRestdayCalendar;

// load cursor pagination
// use Illuminate\Pagination\Paginator;
// use Illuminate\Pagination\CursorPaginator;

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

class AttendanceDailyReportController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
    $this->middleware('highMgmtAccess:1|2|5,NULL', ['only' => ['index', 'show', 'print']]);
    $this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
  }

  /**
   * Print PDF.
   */
  public function print(Request $request)
  {
    // $current_datetime = Carbon::now();

    // $overtimes = HROvertime::join('staffs', 'staffs.id', '=', 'hr_overtimes.staff_id')
    //   ->join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
    //   ->join('logins', 'hr_overtimes.staff_id', '=', 'logins.staff_id')
    //   ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
    //   ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
    //   ->whereBetween('hr_overtimes.ot_date', [$request->date_start, $request->date_end])
    //   ->where('hr_overtimes.active', 1)
    //   ->where('logins.active', 1)
    //   ->where('pivot_staff_pivotdepts.main', 1)
    //   ->where('pivot_dept_cate_branches.branch_id', $request->branch)
    //   ->select('logins.username', 'staffs.name', 'pivot_dept_cate_branches.department', 'hr_overtimes.staff_id')
    //   ->groupBy('hr_overtimes.staff_id')
    //   ->orderBy('logins.username', 'ASC')
    //   ->get();

    // $branch = $request->branch;
    // $date_start = $request->date_start;
    // $date_end = $request->date_end;

    // $pdf = PDF::loadView('humanresources.hrdept.overtime.overtimereport.printpdf', ['overtimes' => $overtimes, 'branch' => $branch, 'date_start' => $date_start, 'date_end' => $date_end]);
    // // return $pdf->download('overtime_report ' . $current_datetime . '.pdf');
    // return $pdf->stream();
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request): View
  {

    if ($request->date != NULL) {
      $selected_date = $request->date;
    } else {
      $current_time = now();
      $selected_date = $current_time->format('Y-m-d');
    }

    $saturday = HRRestdayCalendar::where('saturday_date', '=', $selected_date)->select('restday_group_id');

    if ($saturday->isNotEmpty()) {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->join('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
        ->where('hr_attendances.attend_date', '=', $selected_date)
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL);
        })
        ->where('hr_attendances.outstation_id', '=', NULL)
        ->where(function ($query) {
          $query->where('hr_attendances.attendance_type_id', '!=', 4)
            ->orWhere('hr_attendances.attendance_type_id', '=', NULL);
        })
        ->where('logins.active', '=', 1)
        ->where('pivot_staff_pivotdepts.main', '=', 1)
        ->select('hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks')
        ->orderBy('option_branches.code', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    } else {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->join('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
        ->where('hr_attendances.attend_date', '=', $selected_date)
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL);
        })
        ->where('hr_attendances.outstation_id', '=', NULL)
        ->where(function ($query) {
          $query->where('hr_attendances.attendance_type_id', '!=', 4)
            ->orWhere('hr_attendances.attendance_type_id', '=', NULL);
        })
        ->where('logins.active', '=', 1)
        ->where('pivot_staff_pivotdepts.main', '=', 1)
        ->select('hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks')
        ->orderBy('option_branches.code', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    }

    $dailyreport_late = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
      ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
      ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
      ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
      ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
      ->join('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
      ->where('hr_attendances.attend_date', '=', $selected_date)
      ->where(function ($query) {
        $query->where('hr_attendances.in', '=', '00:00:00')
          ->orWhere('hr_attendances.leave_id', '!=', NULL);
      })
      ->where('hr_attendances.outstation_id', '=', NULL)
      ->where(function ($query) {
        $query->where('hr_attendances.attendance_type_id', '!=', 4)
          ->orWhere('hr_attendances.attendance_type_id', '=', NULL);
      })
      ->where('logins.active', '=', 1)
      ->where('pivot_staff_pivotdepts.main', '=', 1)
      ->select('hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks', 'hr_attendances.in')
      ->orderBy('option_branches.code', 'ASC')
      ->orderBy('logins.username', 'ASC')
      ->get();

    $dailyreport_outstation = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
      ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
      ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
      ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
      ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
      ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
      ->where('hr_attendances.attend_date', '=', $selected_date)
      ->where(function ($query) {
        $query->where('hr_attendances.attendance_type_id', '=', 4)
          ->orWhere('hr_attendances.outstation_id', '!=', NULL);
      })
      ->where('logins.active', '=', 1)
      ->where('pivot_staff_pivotdepts.main', '=', 1)
      ->select('hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.outstation_id', 'hr_attendances.remarks', 'hr_attendances.attendance_type_id')
      ->orderBy('option_branches.code', 'ASC')
      ->orderBy('logins.username', 'ASC')
      ->get();



    return view('humanresources.hrdept.attendance.attendancedailyreport.index', ['dailyreport_absent' => $dailyreport_absent, 'dailyreport_late' => $dailyreport_late, 'dailyreport_outstation' => $dailyreport_outstation, 'selected_date' => $selected_date]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request): View
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
  public function show(Request $request): View
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Request $request): View
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): RedirectResponse
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
