<style>
  @page {
    margin-left: 1cm;
    margin-top: 0.5cm;
    margin-right: 1cm;
    margin-bottom: 0.5cm;
    size: landscape;
    font-family: Arial, sans-serif;
    font-size: 12px;
  }

  .avoid-break {
    page-break-inside: avoid;
  }

  table,
  tr,
  td {
    border-collapse: collapse;
    height: 17px;
  }

  .table-no-border table,
  .table-no-border tr,
  .table-no-border td {
    border: none;
  }

  .table-with-border table,
  .table-with-border tr,
  .table-with-border td {
    border: 1px solid black;
  }
</style>

<?php
ini_set('max_execution_time', 3000);

// use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\UnavailableDateTime;
use App\Helpers\TimeCalculator;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Models\Staff;
use App\Models\Login;
use App\Models\Customer;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;
?>

<body>

  <?php if ($sa) { ?>

    <?php
    foreach ($sa as $me) {
      $t[] = $me->staff_id;
    }

    $i = 0;
    $p = [];
    ?>

    <?php foreach ($sa as $v) { ?>

      <?php
      $n = 0;
      $ha = HRAttendance::where('staff_id', $v->staff_id)
        ->where(function (Builder $query) use ($request) {
          $query->whereDate('attend_date', '>=', $request->from)
            ->whereDate('attend_date', '<=', $request->to);
        })
        ->get();
      ?>

      <div class="avoid-break">
        <table width="100%;" class="table-no-border avoid-break">
          <tr>
            <td width="90px;">
              Staff ID / Name:
            </td>
            <td>
              {{ Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username }} {{ Staff::find($v->staff_id)->name }}
            </td>
            <td width="70px;">
              Department:
            </td>
            <td width="280px;">
              {{ Staff::find($v->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->department }}
            </td>
            <td width="40px;">
              Group:
            </td>
            <td width="100px;">
              {{ Staff::find($v->staff_id)->belongstorestdaygroup?->group }}
            </td>
          </tr>
        </table>

        <table width="100%;" class="table-with-border avoid-break">
          <tr>
            <td align="center" width="100px;">
              Date
            </td>
            <td align="center" width="70px;">
              Day Type
            </td>
            <td align="center" width="60px;">
              Leave
            </td>
            <td align="center" width="60px;">
              In
            </td>
            <td align="center" width="60px;">
              Break
            </td>
            <td align="center" width="60px;">
              Resume
            </td>
            <td align="center" width="60px;">
              Out
            </td>
            <td align="center" width="60px;">
              Duration
            </td>
            <td align="center" width="60px;">
              Overtime
            </td>
            <td align="center" width="80px;">
              Outstation
            </td>
            <td>
              &nbsp;Remark
            </td>
            <td align="center" width="60px;">
              Exception
            </td>
          </tr>

          <?php foreach ($ha as $v1) { ?>

            <?php
            /////////////////////////////
            // to determine working hour of each user
            $wh = UnavailableDateTime::workinghourtime($v1->attend_date, $v->belongstostaff->id)->first();

            // looking for leave of each staff
            // $l = $v->belongstostaff->hasmanyleave()
            $l = HRLeave::where('staff_id', $v->staff_id)
              ->where(function (Builder $query) {
                $query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
              })
              ->where(function (Builder $query) use ($v1) {
                $query->whereDate('date_time_start', '<=', $v1->attend_date)
                  ->whereDate('date_time_end', '>=', $v1->attend_date);
              })
              ->first();

            $o = HROvertime::where([['staff_id', $v->staff_id], ['ot_date', $v1->attend_date], ['active', 1]])->first();

            $os = HROutstation::where('staff_id', $v->staff_id)
              ->where('active', 1)
              ->where(function (Builder $query) use ($v1) {
                $query->whereDate('date_from', '<=', $v1->attend_date)
                  ->whereDate('date_to', '>=', $v1->attend_date);
              })
              ->get();

            $in = Carbon::parse($v1->in)->equalTo('00:00:00');
            $break = Carbon::parse($v1->break)->equalTo('00:00:00');
            $resume = Carbon::parse($v1->resume)->equalTo('00:00:00');
            $out = Carbon::parse($v1->out)->equalTo('00:00:00');

            // looking for RESTDAY, WORKDAY & HOLIDAY
            $sun = Carbon::parse($v1->attend_date)->dayOfWeek == 0;    // sunday
            $sat = Carbon::parse($v1->attend_date)->dayOfWeek == 6;    // saturday

            $hdate = HRHolidayCalendar::where(function (Builder $query) use ($v1) {
              $query->whereDate('date_start', '<=', $v1->attend_date)
                ->whereDate('date_end', '>=', $v1->attend_date);
            })
              ->get();

            if ($hdate->isNotEmpty()) {                      // date holiday
              $dayt = OptDayType::find(3)->daytype;              // show what day: HOLIDAY
              $dtype = false;
            } elseif ($hdate->isEmpty()) {                    // date not holiday
              if (Carbon::parse($v1->attend_date)->dayOfWeek == 0) {      // sunday
                $dayt = OptDayType::find(2)->daytype;
                $dtype = false;
              } elseif (Carbon::parse($v1->attend_date)->dayOfWeek == 6) {    // saturday
                $sat = $v->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $v1->attend_date)->first();
                if ($sat) {                          // determine if user belongs to sat group restday
                  $dayt = OptDayType::find(2)->daytype;          // show what day: RESTDAY
                  $dtype = false;
                } else {
                  $dayt = OptDayType::find(1)->daytype;          // show what day: WORKDAY
                  $dtype = true;
                }
              } else {                            // all other day is working day
                $dayt = OptDayType::find(1)->daytype;            // show what day: WORKDAY
                $dtype = true;
              }
            }

            // detect all
            if ($os->isNotEmpty()) {                                              // outstation |
              if ($dtype) {                                                  // outstation | working
                if ($l) {                                                  // outstation | working | leave
                  if ($in) {                                                // outstation | working | leave | no in
                    if ($break) {                                            // outstation | working | leave | no in | no break
                      if ($resume) {                                          // outstation | working | leave | no in | no break | no resume
                        if ($out) {                                          // outstation | working | leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | leave | no in | no break | resume
                        if ($out) {                                          // outstation | working | leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | working | leave | no in | break
                      if ($resume) {                                          // outstation | working | leave | no in | break | no resume
                        if ($out) {                                          // outstation | working | leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | leave | no in | break | resume
                        if ($out) {                                          // outstation | working | leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // outstation | working | leave | in
                    if ($break) {                                            // outstation | working | leave | in | no break
                      if ($resume) {                                          // outstation | working | leave | in | no break | no resume
                        if ($out) {                                          // outstation | working | leave | in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | leave | in | no break | resume
                        if ($out) {                                          // outstation | working | leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | working | leave | in | break
                      if ($resume) {                                          // outstation | working | leave | in | break | no resume
                        if ($out) {                                          // outstation | working | leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | leave | in | break | resume
                        if ($out) {                                          // outstation | working | leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | leave | in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  }
                } else {                                                  // outstation | working | no leave
                  if ($in) {                                                // outstation | working | no leave | no in
                    if ($break) {                                            // outstation | working | no leave | no in | no break
                      if ($resume) {                                          // outstation | working | no leave | no in | no break | no resume
                        if ($out) {                                          // outstation | working | no leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | no leave | no in | no break | resume
                        if ($out) {                                          // outstation | working | no leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | working | no leave | no in | break
                      if ($resume) {                                          // outstation | working | no leave | no in | break | no resume
                        if ($out) {                                          // outstation | working | no leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | no leave | no in | break | resume
                        if ($out) {                                          // outstation | working | no leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            if ($break == $resume) {                              // check for break and resume is the same value
                              $ll = null;          // outstation
                            } else {
                              $ll = null;          // pls check
                            }
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // outstation | working | no leave | in
                    if ($break) {                                            // outstation | working | no leave | in | no break
                      if ($resume) {                                          // outstation | working | no leave | in | no break | no resume
                        if ($out) {                                          // outstation | working | no leave | in | no break | no resume | no out
                          if (Carbon::parse(now())->gt($v1->attend_date)) {
                            if (is_null($v1->attendance_type_id)) {
                              $ll = null;          // outstation
                            } else {
                              $ll = OptTcms::find($v1->attendance_type_id)->leave;
                            }
                          } else {
                            if (is_null($v1->attendance_type_id)) {
                              $ll = null;          // outstation
                            } else {
                              $ll = OptTcms::find($v1->attendance_type_id)->leave;
                            }
                          }
                        } else {                                          // outstation | working | no leave | in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | no leave | in | no break | resume
                        if ($out) {                                          // outstation | working | no leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | working | no leave | in | break
                      if ($resume) {                                          // outstation | working | no leave | in | break | no resume
                        if ($out) {                                          // outstation | working | no leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | working | no leave | in | break | resume
                        if ($out) {                                          // outstation | working | no leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            if ($break == $resume) {                              // check for break and resume is the same value
                              $ll = null;          // outstation
                            } else {
                              $ll = null;          // pls check
                            }
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | working | no leave | in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  }
                }
              } else {                                                    // outstation | no working
                if ($l) {                                                  // outstation | no working | leave
                  if ($in) {                                                // outstation | no working | leave | no in
                    if ($break) {                                            // outstation | no working | leave | no in | no break
                      if ($resume) {                                          // outstation | no working | leave | no in | no break | no resume
                        if ($out) {                                          // outstation | no working | leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | leave | no in | no break | resume
                        if ($out) {                                          // outstation | no working | leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | no working | leave | no in | break
                      if ($resume) {                                          // outstation | no working | leave | no in | break | no resume
                        if ($out) {                                          // outstation | no working | leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | leave | no in | break | resume
                        if ($out) {                                          // outstation | no working | leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // outstation | no working | leave | in
                    if ($break) {                                            // outstation | no working | leave | in | no break
                      if ($resume) {                                          // outstation | no working | leave | in | no break | no resume
                        if ($out) {                                          // outstation | no working | leave | in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | leave | in | no break | resume
                        if ($out) {                                          // outstation | no working | leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | no working | leave | in | break
                      if ($resume) {                                          // outstation | no working | leave | in | break | no resume
                        if ($out) {                                          // outstation | no working | leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | leave | in | break | resume
                        if ($out) {                                          // outstation | no working | leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | leave | in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  }
                } else {                                                  // outstation | no working | no leave
                  if ($in) {                                                // outstation | no working | no leave | no in
                    if ($break) {                                            // outstation | no working | no leave | no in | no break
                      if ($resume) {                                          // outstation | no working | no leave | no in | no break | no resume
                        if ($out) {                                          // outstation | no working | no leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | no leave | no in | no break | resume
                        if ($out) {                                          // outstation | no working | no leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | no working | no leave | no in | break
                      if ($resume) {                                          // outstation | no working | no leave | no in | break | no resume
                        if ($out) {                                          // outstation | no working | no leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | no leave | no in | break | resume
                        if ($out) {                                          // outstation | no working | no leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // outstation | no working | no leave | in
                    if ($break) {                                            // outstation | no working | no leave | in | no break
                      if ($resume) {                                          // outstation | no working | no leave | in | no break | no resume
                        if ($out) {                                          // outstation | no working | no leave | in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | no leave | in | no break | resume
                        if ($out) {                                          // outstation | no working | no leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // outstation | no working | no leave | in | break
                      if ($resume) {                                          // outstation | no working | no leave | in | break | no resume
                        if ($out) {                                          // outstation | no working | no leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // outstation | no working | no leave | in | break | resume
                        if ($out) {                                          // outstation | no working | no leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // outstation | no working | no leave | in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // outstation
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  }
                }
              }
            } else {                                                      // no outstation
              if ($dtype) {                                                  // no outstation | working
                if ($l) {                                                  // no outstation | working | leave
                  if ($in) {                                                // no outstation | working | leave | no in
                    if ($break) {                                            // no outstation | working | leave | no in | no break
                      if ($resume) {                                          // no outstation | working | leave | no in | no break | no resume
                        if ($out) {                                          // no outstation | working | leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      } else {                                            // no outstation | working | leave | no in | no break | resume
                        if ($out) {                                          // no outstation | working | leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      }
                    } else {                                              // no outstation | working | leave | no in | break
                      if ($resume) {                                          // no outstation | working | leave | no in | break | no resume
                        if ($out) {                                          // no outstation | working | leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      } else {                                            // no outstation | working | leave | no in | break | resume
                        if ($out) {                                          // no outstation | working | leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // no outstation | working | leave | in
                    if ($break) {                                            // no outstation | working | leave | in | no break
                      if ($resume) {                                          // no outstation | working | leave | in | no break | no resume
                        if ($out) {                                          // working | leave | in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      } else {                                            // no outstation | working | leave | in | no break | resume
                        if ($out) {                                          // no outstation | working | leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      }
                    } else {                                              // no outstation | working | leave | in | break
                      if ($resume) {                                          // no outstation | working | leave | in | break | no resume
                        if ($out) {                                          // no outstation | working | leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      } else {                                            // no outstation | working | leave | in | break | resume
                        if ($out) {                                          // no outstation | working | leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        } else {                                          // no outstation | working | leave | in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = $l->belongstooptleavetype?->leave_type_code;
                          } else {
                            $ll = $v1->belongstoopttcms->leave;
                          }
                        }
                      }
                    }
                  }
                } else {                                                  // no outstation | working | no leave
                  if ($in) {                                                // no outstation | working | no leave | no in
                    if ($break) {                                            // no outstation | working | no leave | no in | no break
                      if ($resume) {                                          // no outstation | working | no leave | no in | no break | no resume
                        if ($out) {                                          // no outstation | working | no leave | no in | no break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // absent
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation | working | no leave | no in | no break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // half absent
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // no outstation | working | no leave | no in | no break | resume
                        if ($out) {                                          // no outstation | working | no leave | no in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          //  pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation | working | no leave | no in | no break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // half absent
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    } else {                                              // no outstation | working | no leave | no in | break
                      if ($resume) {                                          // no outstation | working | no leave | no in | break | no resume
                        if ($out) {                                          // no outstation | working | no leave | no in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation |  outstation | working | no leave | no in | break | no resume | out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      } else {                                            // no outstation |  outstation | working | no leave | no in | break | resume
                        if ($out) {                                          // no outstation |  outstation | working | no leave | no in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation |  outstation | working | no leave | no in | break | resume | out
                          if (is_null($v1->attendance_type_id)) {
                            if ($break == $resume) {                              // check for break and resume is the same value
                              $ll = null;          // half absent
                            } else {
                              $ll = null;          // pls check
                            }
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        }
                      }
                    }
                  } else {                                                // no outstation |  outstation | working | no leave | in
                    if ($break) {                                            // no outstation |  outstation | working | no leave | in | no break
                      if ($resume) {                                          // no outstation |  outstation | working | no leave | in | no break | no resume
                        if ($out) {                                          // no outstation |  outstation | working | no leave | in | no break | no resume | no out
                          if (Carbon::parse(now())->gt($v1->attend_date)) {
                            if (is_null($v1->attendance_type_id)) {
                              $ll = null;          // half absent
                            } else {
                              $ll = OptTcms::find($v1->attendance_type_id)->leave;
                            }
                          } else {
                            $ll = false;
                          }
                        } else {                                          // no outstation |  outstation | working | no leave | in | no break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation |  outstation | working | no leave | in | no break | resume
                        if ($out) {                                          // no outstation |  outstation | working | no leave | in | no break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // pls check
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation |  outstation | working | no leave | in | no break | resume | out
                          $ll = false;
                        }
                      }
                    } else {                                              // no outstation |  outstation | working | no leave | in | break
                      if ($resume) {                                          // no outstation |  outstation | working | no leave | in | break | no resume
                        if ($out) {                                          // no outstation |  outstation | working | no leave | in | break | no resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            $ll = null;          // half absent
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation | working | no leave | in | break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | working | no leave | in | break | resume
                        if ($out) {                                          // no outstation | working | no leave | in | break | resume | no out
                          if (is_null($v1->attendance_type_id)) {
                            if ($break == $resume) {                              // check for break and resume is the same value
                              $ll = null;          // half absent
                            } else {
                              $ll = null;          // pls check
                            }
                          } else {
                            $ll = OptTcms::find($v1->attendance_type_id)->leave;
                          }
                        } else {                                          // no outstation | working | no leave | in | break | resume | out
                          $ll = false;
                        }
                      }
                    }
                  }
                }
              } else {                                                    // no outstation | no working
                if ($l) {                                                  // no outstation | no working | leave
                  if ($in) {                                                // no outstation | no working | leave | no in
                    if ($break) {                                            // no outstation | no working | leave | no in | no break
                      if ($resume) {                                          // no outstation | no working | leave | no in | no break | no resume
                        if ($out) {                                          // no outstation | no working | leave | no in | no break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | no in | no break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | leave | no in | no break | resume
                        if ($out) {                                          // no outstation | no working | leave | no in | no break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | no in | no break | resume | out
                          $ll = false;
                        }
                      }
                    } else {                                              // no outstation | no working | leave | no in | break
                      if ($resume) {                                          // no outstation | no working | leave | no in | break | no resume
                        if ($out) {                                          // no outstation | no working | leave | no in | break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | no in | break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | leave | no in | break | resume
                        if ($out) {                                          // no outstation | no working | leave | no in | break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | no in | break | resume | out
                          $ll = false;
                        }
                      }
                    }
                  } else {                                                // no outstation | no working | leave | in
                    if ($break) {                                            // no outstation | no working | leave | in | no break
                      if ($resume) {                                          // no outstation | no working | leave | in | no break | no resume
                        if ($out) {                                          // no outstation | no working | leave | in | no break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | in | no break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | leave | in | no break | resume
                        if ($out) {                                          // no outstation | no working | leave | in | no break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | in | no break | resume | out
                          $ll = false;
                        }
                      }
                    } else {                                              // no outstation | no working | leave | in | break
                      if ($resume) {                                          // no outstation | no working | leave | in | break | no resume
                        if ($out) {                                          // no outstation | no working | leave | in | break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | in | break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | leave | in | break | resume
                        if ($out) {                                          // no outstation | no working | leave | in | break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | leave | in | break | resume | out
                          $ll = false;
                        }
                      }
                    }
                  }
                } else {                                                  // no outstation | no working | no leave
                  if ($in) {                                                // no outstation | no working | no leave | no in
                    if ($break) {                                            // no outstation | no working | no leave | no in | no break
                      if ($resume) {                                          // no outstation | no working | no leave | no in | no break | no resume
                        if ($out) {                                          // no outstation | no working | no leave | no in | no break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | no in | no break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | no leave | no in | no break | resume
                        if ($out) {                                          // no outstation | no working | no leave | no in | no break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | no in | no break | resume | out
                          $ll = false;
                        }
                      }
                    } else {                                              // no outstation | no working | no leave | no in | break
                      if ($resume) {                                          // no outstation | no working | no leave | no in | break | no resume
                        if ($out) {                                          // no outstation | no working | no leave | no in | break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | no in | break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | no leave | no in | break | resume
                        if ($out) {                                          // no outstation | no working | no leave | no in | break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | no in | break | resume | out
                          $ll = false;
                        }
                      }
                    }
                  } else {                                                // no outstation | no working | no leave | in
                    if ($break) {                                            // no outstation | no working | no leave | in | no break
                      if ($resume) {                                          // no outstation | no working | no leave | in | no break | no resume
                        if ($out) {                                          // no outstation | no working | no leave | in | no break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | in | no break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | no leave | in | no break | resume
                        if ($out) {                                          // no outstation | no working | no leave | in | no break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | in | no break | resume | out
                          $ll = false;
                        }
                      }
                    } else {                                              // no outstation | no working | no leave | in | break
                      if ($resume) {                                          // no outstation | no working | no leave | in | break | no resume
                        if ($out) {                                          // no outstation | no working | no leave | in | break | no resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | in | break | no resume | out
                          $ll = false;
                        }
                      } else {                                            // no outstation | no working | no leave | in | break | resume
                        if ($out) {                                          // no outstation | no working | no leave | in | break | resume | no out
                          $ll = false;
                        } else {                                          // no outstation | no working | no leave | in | break | resume | out
                          $ll = false;
                        }
                      }
                    }
                  }
                }
              }
            }

            if ($l) {
              $lea = 'HR9-' . str_pad($l->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $l->leave_year;
            } else {
              $lea = NULL;
            }

            if ($in == '00:00:00') {
              $in1 = null;
            } else {
              if (Carbon::parse($v1->in)->gt($wh?->time_start_am)) {
                $in1 = Carbon::parse($v1->in)->format('g:i a');
              } else {
                $in1 = Carbon::parse($v1->in)->format('g:i a');
              }
            }

            if ($break == '00:00:00') {
              $break1 = null;
            } else {
              $break1 = Carbon::parse($v1->break)->format('g:i a');
            }

            if ($resume == '00:00:00') {
              $resume1 = null;
            } else {
              $resume1 = Carbon::parse($v1->resume)->format('g:i a');
            }

            if ($out == '00:00:00') {
              $out1 = null;
            } else {
              $out1 = Carbon::parse($v1->out)->format('g:i a');
            }

            if ($v1->time_work_hour == '00:00:00') {
              $workhour = null;
            } else {
              $workhour = Carbon::parse($v1->time_work_hour)->format('H:i');
            }

            if (!is_null($v1->time_work_hour)) {
              $m[$i][$n] = Carbon::parse($v1->time_work_hour)->format('H:i');
            } else {
              $m[$i][$n] = Carbon::parse('00:00:00')->format('H:i');
            }

            if (!is_null($os)) {
              // $cust = $os?->belongstocustomer?->customer;
            } else {
              $cust = null;
            }

            $ort_temp = $o?->belongstoovertimerange?->where('active', 1)->first()?->total_time;

            if (!is_null($ort_temp)) {
              $ort = Carbon::parse($ort_temp)->format('H:i');
              $p[$i][$n] = Carbon::parse($o?->belongstoovertimerange?->where('active', 1)->first()?->total_time)->format('H:i');
            } else {
              $ort = NULL;
              $p[$i][$n] = Carbon::parse('00:00:00')->format('H:i');
            }
            ?>

            <tr>
              <td>
                &nbsp;{{ Carbon::parse($v1->attend_date)->format('Y-m-d D') }}
              </td>
              <td align="center">
                {{ $dayt }}
              </td>
              <td align="center">
                {{ $ll }}
              </td>
              <td align="center">
                {{ $in1 }}
              </td>
              <td align="center">
                {{ $break1 }}
              </td>
              <td align="center">
                {{ $resume1 }}
              </td>
              <td align="center">
                {{ $out1 }}
              </td>
              <td align="right">
                {{ $workhour }}&nbsp;
              </td>
              <td align="right">
                {{ $ort }}&nbsp;
              </td>
              <td>
                &nbsp;{{ ($os)?Str::limit(ucwords(Str::lower($os->first()?->belongstocustomer?->customer)), 10, '...'):null }}
              </td>
              <td>
                &nbsp;{{ Str::limit(ucwords(Str::lower($v1->remarks. (($v1->hr_remarks)?' | ':''). $v1->hr_remarks)), 40, '...') }}
              </td>
              <td align="center">
                {{ $v1->exception }}
              </td>
            </tr>
            <?php $n++; ?>
          <?php } ?>

          <tr>
            <td colspan="7" align="right">
              <b>TOTAL&nbsp;&nbsp;&nbsp;</b>
            </td>
            <td align="right">
              <?php if (TimeCalculator::total_time($m[$i]) != '00:00:00') { ?>
                <?php
                list($hour, $minute, $second) = explode(":", TimeCalculator::total_time($m[$i]));
                echo $hour . ':' . $minute;
                ?>
                &nbsp;
              <?php } ?>
            </td>
            <td align="right">
              <?php if (TimeCalculator::total_time($p[$i]) != '00:00:00') { ?>
                {{ Carbon::parse(TimeCalculator::total_time($p[$i]))->format('H:i') }}&nbsp;
              <?php } ?>
            </td>
            <td colspan="3"></td>
          </tr>
        </table>

        <?php $i++; ?>
        <br /><br />
      </div>
    <?php } ?>
  <?php } ?>
</body>