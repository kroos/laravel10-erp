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
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRRestdayCalendar;
use App\Models\HumanResources\OptWorkingHour;

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
    $this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
  }

  /**
   * Print PDF.
   */
  public function print(Request $request)
  {
    if ($request->date != NULL) {
      $selected_date = $request->date;
    } else {
      $current_time = now();
      $selected_date = $current_time->format('Y-m-d');
    }

    $date_name = Carbon::parse($selected_date)->format('l');

    $saturday = HRRestdayCalendar::where('saturday_date', '=', $selected_date)->select('restday_group_id')->first();

    ////////////////////////////////////////////////////////////////////////////////////
    if (isset($saturday)) {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')

        ->where('hr_attendances.attend_date', '=', $selected_date)
        // ->where('staffs.restday_group_id', '!=', $saturday->restday_group_id)
        ->where(function ($query) use ($saturday) {
          $query->where('staffs.restday_group_id', '!=', $saturday->restday_group_id)
            ->orWhereNull('staffs.restday_group_id');
        })
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL)
            ->orWhere('hr_attendances.remarks', '!=', NULL);
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
        ->orderBy('pivot_dept_cate_branches.department', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    } else {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
        ->where('hr_attendances.attend_date', '=', $selected_date)
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL)
            ->orWhere('hr_attendances.remarks', '!=', NULL);
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
        ->orderBy('pivot_dept_cate_branches.department', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    }


    ////////////////////////////////////////////////////////////////////////////////////
    $dailyreport_late = NULL;

    $staffs_late = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
      ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
      ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
      ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
      ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
      ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
      ->where('hr_attendances.attend_date', '=', $selected_date)
      ->where('hr_attendances.in', '!=', '00:00:00')
      ->where('logins.active', '=', 1)
      ->where('pivot_staff_pivotdepts.main', '=', 1)
      ->select('staffs.id as StaffID', 'hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks', 'hr_attendances.in', 'pivot_dept_cate_branches.wh_group_id')
      ->orderBy('option_branches.code', 'ASC')
      ->orderBy('pivot_dept_cate_branches.department', 'ASC')
      ->orderBy('logins.username', 'ASC')
      ->get();

    foreach ($staffs_late as $staff_late) {
      if ($staff_late->wh_group_id == '0' && $date_name == 'Friday') {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '=', 3)
          ->select('time_start_am')
          ->first();
      } elseif ($staff_late->wh_group_id == '0') {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '!=', 3)
          ->select('time_start_am')
          ->first();
      } else {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '=', 8)
          ->select('time_start_am')
          ->first();
      }

      if ($staff_late->in > $company_hour->time_start_am) {
        $dailyreport_late[] = $staff_late->StaffID;
      }
    }


    ////////////////////////////////////////////////////////////////////////////////////
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
      ->orderBy('hr_attendances.outstation_id', 'ASC')
      ->orderBy('logins.username', 'ASC')
      ->get();

    $pdf = PDF::loadView('humanresources.hrdept.attendance.attendancedailyreport.printpdf', ['dailyreport_absent' => $dailyreport_absent, 'dailyreport_late' => $dailyreport_late, 'dailyreport_outstation' => $dailyreport_outstation, 'selected_date' => $selected_date]);
    // return $pdf->download('attendance daily report ' . $selected_date . '.pdf');
    return $pdf->stream();
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

    $date_name = Carbon::parse($selected_date)->format('l');

    $saturday = HRRestdayCalendar::where('saturday_date', '=', $selected_date)->select('restday_group_id')->first();

    $update_remarks = HRAttendance::whereNotNull('leave_id')->where('attend_date', $selected_date)->get();

    foreach ($update_remarks as $loop_absent) {
      if ($loop_absent->leave_id != NULL && $loop_absent->remarks == NULL) {

        $leave = HRLeave::where('hr_leaves.id', $loop_absent->leave_id)->select('hr_leaves.reason')->first();

        HRAttendance::where('id', $loop_absent->id)
          ->update([
            'remarks' => $leave->reason,
          ]);
      }
    }


    ////////////////////////////////////////////////////////////////////////////////////
    $dailyreport_absent = NULL;

    if (isset($saturday)) {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')

        ->where('hr_attendances.attend_date', '=', $selected_date)
        // ->where('staffs.restday_group_id', '!=', $saturday->restday_group_id)
        ->where(function ($query) use ($saturday) {
          $query->where('staffs.restday_group_id', '!=', $saturday->restday_group_id)
            ->orWhereNull('staffs.restday_group_id');
        })
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL)
            ->orWhere('hr_attendances.remarks', '!=', NULL);
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
        ->orderBy('pivot_dept_cate_branches.department', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    } else {
      $dailyreport_absent = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
        ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
        ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
        ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
        ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
        ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
        ->where('hr_attendances.attend_date', '=', $selected_date)
        ->where(function ($query) {
          $query->where('hr_attendances.in', '=', '00:00:00')
            ->orWhere('hr_attendances.leave_id', '!=', NULL)
            ->orWhere('hr_attendances.remarks', '!=', NULL);
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
        ->orderBy('pivot_dept_cate_branches.department', 'ASC')
        ->orderBy('logins.username', 'ASC')
        ->get();
    }


    ////////////////////////////////////////////////////////////////////////////////////
    $dailyreport_late = NULL;

    $staffs_late = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
      ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
      ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
      ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
      ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
      ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
      ->where('hr_attendances.attend_date', '=', $selected_date)
      ->where('hr_attendances.in', '!=', '00:00:00')
      ->where('logins.active', '=', 1)
      ->where('pivot_staff_pivotdepts.main', '=', 1)
      ->select('staffs.id as StaffID', 'hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks', 'hr_attendances.in', 'pivot_dept_cate_branches.wh_group_id')
      ->orderBy('option_branches.code', 'ASC')
      ->orderBy('pivot_dept_cate_branches.department', 'ASC')
      ->orderBy('logins.username', 'ASC')
      ->get();

    foreach ($staffs_late as $staff_late) {
      if ($staff_late->wh_group_id == '0' && $date_name == 'Friday') {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '=', 3)
          ->select('time_start_am')
          ->first();
      } elseif ($staff_late->wh_group_id == '0') {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '!=', 3)
          ->select('time_start_am')
          ->first();
      } else {
        $company_hour = OptWorkingHour::where('option_working_hours.group', '=', $staff_late->wh_group_id)
          ->where('option_working_hours.effective_date_start', '<=', $selected_date)
          ->where('option_working_hours.effective_date_end', '>=', $selected_date)
          ->where('option_working_hours.category', '=', 8)
          ->select('time_start_am')
          ->first();
      }

      if ($staff_late->in > $company_hour->time_start_am) {
        $dailyreport_late[] = $staff_late->StaffID;
      }
    }


    ////////////////////////////////////////////////////////////////////////////////////
    $dailyreport_outstation = NULL;

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
      ->orderBy('hr_attendances.outstation_id', 'ASC')
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
