<?php

namespace App\Http\Controllers\HumanResources\HRDept;

// for controller output
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

// MODELS
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRTempPunchTime;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRRestdayCalendar;
use App\Models\HumanResources\OptWorkingHour;
use App\Models\Staff;
use App\Models\Login;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// TIME & DATE
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

// IMPORT FROM EXCEL INTO DATABASE
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

use Session;

class AttendanceUploadController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
    // $this->middleware('highMgmtAccess:1|2|4|5,14', ['only' => ['index', 'show']]);
    $this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(): View
  {
    return view('humanresources.hrdept.attendance.attendanceupload.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
    ini_set('max_execution_time', '0');

    HRTempPunchTime::truncate();

    if ($request->file('softcopy')) {
      // UPLOAD SOFTCOPY AND DATA EXCEL INTO DATABASE
      $fileName = $request->file('softcopy')->getClientOriginalName();
      $currentDate = Carbon::now()->format('Y-m-d His');
      $file = $currentDate . '_' . $fileName;
      $request->file('softcopy')->storeAs('public/attendance', $file);
      Excel::import(new AttendanceImport, $request->file('softcopy'));
    }


    // FETCH ACTIVE STAFF USER INFO
    $query_Recordset1 = DB::select('SELECT `logins`.username, `staffs`.id, `staffs`.`name`, `staffs`.restday_group_id FROM `logins` JOIN `staffs` ON `logins`.staff_id = `staffs`.id WHERE `staffs`.active = ? AND `logins`.active = ? AND `staffs`.id != ? AND `staffs`.id != ?', [1, 1, 61, 62]);


    // LOOP ALL ACTIVE STAFF INFO INTO ARRAY
    foreach ($query_Recordset1 as $row_Recordset1) {

      // SELECT STAFF'S WORKING HOUR CATEGORY
      $query_Recordset2 = DB::select('SELECT `pivot_dept_cate_branches`.wh_group_id FROM `pivot_staff_pivotdepts` JOIN `pivot_dept_cate_branches` ON `pivot_staff_pivotdepts`.pivot_dept_id = `pivot_dept_cate_branches`.id WHERE `pivot_staff_pivotdepts`.staff_id = ? AND `pivot_staff_pivotdepts`.main = ?', [$row_Recordset1->id, 1]);

      $staff = array(
        'username' => $row_Recordset1->username,
        'staff_id' => $row_Recordset1->id,
        'name' => $row_Recordset1->name,
        'restday_group_id' => $row_Recordset1->restday_group_id,
        'wh_group_id' => $query_Recordset2[0]->wh_group_id
      );

      $staffs[] = $staff;
    }

    // GET THE LATEST ATTENDANCE RECORD DATE IN FACESCAN
    $query_Recordset3 = DB::select('SELECT DATE(`hr_temp_punch_time`.Att_Time) AS LastDate FROM `hr_temp_punch_time` GROUP BY DATE(`hr_temp_punch_time`.Att_Time) ORDER BY LastDate ASC LIMIT 1');
    $row_Recordset3 = $query_Recordset3[0]->LastDate;

    // GET THE LATEST ATTENDANCE RECORD DATE IN FACESCAN
    $query_Recordset5 = DB::select('SELECT DATE(`hr_temp_punch_time`.Att_Time) AS CurrentDate FROM `hr_temp_punch_time` GROUP BY DATE(`hr_temp_punch_time`.Att_Time) ORDER BY CurrentDate DESC LIMIT 1');
    $row_Recordset5 = $query_Recordset5[0]->CurrentDate;

    // LOOP STAFF FROM ARRAY TO FETCH THE ATTENDANCE
    foreach ($staffs as $staff) {

      for ($a = strtotime($row_Recordset3); $a <= strtotime($row_Recordset5); $a = strtotime('+1 day', $a)) {
        $date = date('Y-m-d', $a);
        $date_name = date('l', $a);

        $totalRows_holiday = HRHolidayCalendar::select('holiday')->where('date_start', '<=', $date)->where('date_end', '>=', $date)->count();

        $totalRows_saturday = HRRestdayCalendar::select('saturday_date')->where('restday_group_id', '=', $staff['restday_group_id'])->where('saturday_date', '=', $date)->count();

        if ($totalRows_holiday > 0) {
          // HOLIDAY
          $daytype_id = '3';
        } elseif ($totalRows_saturday > 0 || $date_name == 'Sunday') {
          // RESTDAY
          $daytype_id = '2';
        } else {
          // WORKDAY
          $daytype_id = '1';
        }


        // ----------------------------------------------------------- CONFIGURE IN BREAK RESUME OUT TIME -----------------------------------------------------------//
        if ($date_name == 'Friday' && $staff['wh_group_id'] == '0') {
          $row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $staff['wh_group_id'])->where('category', '=', 3)->first();
        } elseif ($staff['wh_group_id'] == '0') {
          $row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $staff['wh_group_id'])->where('category', '!=', 3)->first();
        } else {
          $row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $staff['wh_group_id'])->where('category', '=', 8)->first();
        }


        // IN
        $in = "00:00:00";
        $row_IN = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_end_am])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
        if ($row_IN != NULL) {
          $in = $row_IN->formatted_time;

          $in_add = Carbon::parse($in)->addMinutes(1)->format('H:i:s');;
        }


        // OUT
        $out = "00:00:00";
        $row_OUT = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) >= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
        if ($row_OUT != NULL) {
          $out = $row_OUT->formatted_time;
        }


        // BREAK1 (FETCH THE LAST ROW BEFORE BREAK TIME)
        $break1 = "00:00:00";
        $row_BREAK1 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_end_am])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
        if ($row_BREAK1 != NULL) {
          $break1 = $row_BREAK1->formatted_time;
        }


        // BREAK2 (FETCH THE FIRST ROW BETWEEN BREAK AND RESUME)
        $break2 = "00:00:00";
        $break2_difference = "00:00";
        $row_BREAK2 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) >= ?', [$row_work_hour->time_end_am])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
        if ($row_BREAK2 != NULL) {
          $break2 = $row_BREAK2->formatted_time;

          // CALCULATE INTERVAL TIME BETWEEN PUNCH AND BREAK
          $break2_date1 = Carbon::parse($date . ' ' . $row_work_hour->time_end_am);
          $break2_date2 = Carbon::parse($date . ' ' . $row_BREAK2->formatted_time);
          $break2_interval = $break2_date1->diff($break2_date2);
          $break2_difference = ($break2_interval->h * 60) + $break2_interval->i;
        }


        // RESUME1 (FETCH THE FIRST ROW AFTER RESUME TIME)
        $resume1 = "00:00:00";
        $row_RESUME1 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) >= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
        if ($row_RESUME1 != NULL) {
          $resume1 = $row_RESUME1->formatted_time;
        }


        // RESUME2 (FETCH THE LAST ROW BETWEEN BREAK AND RESUME)
        $resume2 = "00:00:00";
        $resume2_difference = "00:00";
        $row_RESUME2 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $staff['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) >= ?', [$row_work_hour->time_end_am])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
        if ($row_RESUME2 != NULL) {
          $resume2 = $row_RESUME2['formatted_time'];

          // CALCULATE INTERVAL TIME BETWEEN PUNCH AND RESUME
          $resume2_date1 = Carbon::parse($date . ' ' . $row_work_hour->time_start_pm);
          $resume2_date2 = Carbon::parse($date . ' ' . $row_RESUME2->formatted_time);
          $resume2_interval = $resume2_date1->diff($resume2_date2);
          $resume2_difference = ($resume2_interval->h * 60) + $resume2_interval->i;
        }


        $break = "00:00:00";
        if ($in != $break1 && $in_add != $break1) {
          $break = $break1;
        }

        if ($break2 != $resume2) {
          $break = $break2;
        } elseif ($break2_difference < $resume2_difference) {
          $break = $break2;
        }

        $resume = "00:00:00";
        if ($out != $resume1) {
          $resume = $resume1;
        }

        if ($resume2 != $break2) {
          $resume = $resume2;
        } elseif ($break2_difference >= $resume2_difference) {
          $resume = $resume2;
        }


        // ----------------------------------------------------------- CONFIGURE WORK HOUR -----------------------------------------------------------//
        if (($in != '00:00:00' && $break != '00:00:00') || ($in != '00:00:00' && $out != '00:00:00') || ($resume != '00:00:00' && $out != '00:00:00')) {

          // BEGIN TIME
          if ($in <= $row_work_hour->time_start_am && $in != '00:00:00') {
            $begin_time = $row_work_hour->time_start_am;
          } elseif ($in >= $row_work_hour->time_start_am && $in != '00:00:00') {
            $begin_time = $in;
          } elseif ($resume <= $row_work_hour->time_start_pm && $resume != '00:00:00') {
            $begin_time = $row_work_hour->time_start_pm;
          } elseif ($resume >= $row_work_hour->time_start_pm && $resume != '00:00:00') {
            $begin_time = $resume;
          }

          // END TIME
          if ($out >= $row_work_hour->time_end_pm && $out != '00:00:00') {
            $end_time = $row_work_hour->time_end_pm;
          } elseif ($out <= $row_work_hour->time_end_pm && $out != '00:00:00') {
            $end_time = $out;
          } elseif ($break >= $row_work_hour->time_end_am && $break != '00:00:00') {
            $end_time = $row_work_hour->time_end_am;
          } elseif ($break <= $row_work_hour->time_end_am && $break != '00:00:00') {
            $end_time = $break;
          }

          // CALCULATE LUNCH TIME
          if ($begin_time <= $row_work_hour->time_end_am && $end_time >= $row_work_hour->time_start_pm) {
            $lunch_break = Carbon::parse($date . ' ' . $row_work_hour->time_end_am);
            $lunch_resume = Carbon::parse($date . ' ' . $row_work_hour->time_start_pm);
            $lunch_interval = $lunch_break->diff($lunch_resume);
            $lunch_difference = ($lunch_interval->h * 60) + $lunch_interval->i;
          } else {
            $lunch_difference = '0';
          }

          // CALCULATE WORK HOUR
          $begin = Carbon::parse($date . ' ' . $begin_time);
          $end = Carbon::parse($date . ' ' . $end_time);
          $total_interval = $begin->diff($end);
          $total_difference = ($total_interval->h * 60) + $total_interval->i;

          $total_minutes = $total_difference - $lunch_difference;
          $hours = floor($total_minutes / 60);
          $minutes = str_pad(($total_minutes % 60), 2, '0', STR_PAD_LEFT);
          $work_hour = $hours . ":" . $minutes;
        } else {
          $work_hour = '00:00:00';
        }

        // INSERT/UPDATE DATABASE
        $row_Recordset4 = HRAttendance::select('id', 'in', 'break', 'resume', 'out', 'time_work_hour')->where('attend_date', '=', $date)->where('staff_id', '=', $staff['staff_id'])->first();

        if ($row_Recordset4 != NULL) {

          $new_in = $in;
          $new_break = $break;
          $new_resume = $resume;
          $new_out = $out;
          $new_work_hour = $work_hour;


          if ($row_Recordset4->in != '00:00:00' && $in == '00:00:00') {
            $new_in = $row_Recordset4->in;
          } elseif ($row_Recordset4->in != '00:00:00' && $in != '00:00:00' && $row_Recordset4->in <= $in) {
            $new_in = $row_Recordset4->in;
          }

          if ($row_Recordset4->break != '00:00:00' && $break == '00:00:00') {
            $new_break = $row_Recordset4->break;
          } elseif ($row_Recordset4->break != '00:00:00' && $break != '00:00:00' && $row_Recordset4->break <= $break) {
            $new_break = $row_Recordset4->break;
          }

          if ($row_Recordset4->resume != '00:00:00' && $resume == '00:00:00') {
            $new_resume = $row_Recordset4->resume;
          } if ($row_Recordset4->resume != '00:00:00' && $resume != '00:00:00' && $row_Recordset4->resume >= $resume) {
            $new_resume = $row_Recordset4->resume;
          }

          if ($row_Recordset4->out != '00:00:00' && $out == '00:00:00') {
            $new_out = $row_Recordset4->out;
          } elseif ($row_Recordset4->out != '00:00:00' && $out != '00:00:00' && $row_Recordset4->out >= $out) {
            $new_out = $row_Recordset4->out;
          }

          if ($row_Recordset4->time_work_hour != '' && $row_Recordset4->time_work_hour != NULL && $row_Recordset4->time_work_hour >= $work_hour) {
            $new_work_hour = $row_Recordset4->time_work_hour;
          }

          HRAttendance::find($row_Recordset4->id)->update([
            'in' => $new_in,
            'break' => $new_break,
            'resume' => $new_resume,
            'out' => $new_out,
            'time_work_hour' => $new_work_hour,
          ]);
        } else {
          HRAttendance::create([
            'staff_id' => $staff['staff_id'],
            'daytype_id' => $daytype_id,
            'attend_date' => $date,
            'in' => $in,
            'break' => $break,
            'resume' => $resume,
            'out' => $out,
            'time_work_hour' => $work_hour,
          ]);
        }
      }
    }

    Session::flash('flash_message', 'Successfully upload excel.');
    return redirect()->route('attendanceupload.create');
  }

  /**
   * Display the specified resource.
   */
  public function show()
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit()
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update()
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy()
  {
    //
  }
}
