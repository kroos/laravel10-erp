<style>
  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 9px;
    border-collapse: collapse;
    width: 100%;
    font-family: 'Arial', sans-serif;
  }

  .table td {
    height: 18px;
  }

  .top-row td {
    background-color: #cccccc;
  }

  .text-center {
    text-align: center;
  }

  .DEPARTMENT {
    white-space: nowrap;
    width: 65px;
    overflow: hidden;
  }

  .NAME {
    white-space: nowrap;
    width: 145px;
    overflow: hidden;
  }

  .REMARK {
    white-space: nowrap;
    width: 95%;
    overflow: hidden;
  }

  @page {
    margin: 0.30cm;
  }
</style>

<?php

use \Carbon\Carbon;

use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HRAttendance;
?>

<span style="font-size:18px;">DAILY ATTENDANCE</span>

@if ($dailyreport_absent->isNotEmpty() || $dailyreport_late->isNotEmpty() || $dailyreport_outstation->isNotEmpty())
<table class="table">
  <!-- ABSENT -->
  @if ($dailyreport_absent->isNotEmpty())
  <?php $no = 1; ?>
  <tr class="top-row">
    <td colspan="11">
      <b>ABSENT</b>
    </td>
  </tr>
  <tr class="top-row">
    <td class="text-center" style="width: 20px;">
      NO
    </td>
    <td class="text-center" style="width: 55px;">
      DATE
    </td>
    <td class="text-center" style="width: 70px;">
      STATUS
    </td>
    <td class="text-center" style="width: 50px;">
      LOCATION
    </td>
    <td class="text-center" style="width: 70px;">
      DEPARTMENT
    </td>
    <td class="text-center" style="width: 45px;">
      GROUP
    </td>
    <td class="text-center" style="width: 40px;">
      ID
    </td>
    <td class="text-center" style="width: 150px;">
      NAME
    </td>
    <td colspan="2" class="text-center">
      REASON / REMARK
    </td>
    <td class="text-center" style="width: 65px;">
      LEAVE ID
    </td>
  </tr>

  @foreach ($dailyreport_absent as $absent)
  <?php

  if ($absent->leave_id != NULL) {
    $leave = HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')
      ->where('hr_leaves.id', '=', $absent->leave_id)
      ->select('hr_leaves.id as leave_id', 'hr_leaves.leave_no', 'hr_leaves.leave_year', 'option_leave_types.leave_type_code', 'hr_leaves.reason')
      ->first();

    $status = $leave->leave_type_code;
    $remark = $leave->reason;
    $leave_number = 'HR9-' . str_pad($leave->leave_no, 5, "0", STR_PAD_LEFT) . '/' . $leave->leave_year;
  } else {

    if ($absent->attendance_type_id != NULL) {
      $status_code = OptTcms::where('id', '=', $absent->attendance_type_id)->first();
      $status = $status_code->leave;
    } else {
      $status = NULL;
    }

    $remark = $absent->remarks;
    $leave_number = NULL;
  }
  ?>

  <tr>
    <td class="text-center">
      {{ $no++ }}
    </td>
    <td class="text-center">
      {{ $absent->attend_date }}
    </td>
    <td class="text-center">
      {{ $status }}
    </td>
    <td class="text-center">
      {{ $absent->code }}
    </td>
    <td>
      <div class="DEPARTMENT">
        &nbsp;{{ $absent->department }}
      </div>
    </td>
    <td class="text-center">
      {{ $absent->group }}
    </td>
    <td class="text-center">
      {{ $absent->username }}
    </td>
    <td>
      <div class="NAME">
        &nbsp;{{ $absent->name }}
      </div>
    </td>
    <td colspan="2">
      <div class="REMARK">
        &nbsp;{{ $remark }}
      </div>
    </td>
    <td class="text-center">
      @if ($leave_number != NULL)
      {{ $leave_number }}
      @endif
    </td>
  </tr>
  @endforeach
  @endif


  <!-- LATE -->
  @if (!empty($dailyreport_late))
  <?php $no = 1; ?>
  <tr class="top-row">
    <td colspan="11">
      <b>LATENESS</b>
    </td>
  </tr>
  <tr class="top-row">
    <td class="text-center">
      NO
    </td>
    <td class="text-center">
      DATE
    </td>
    <td class="text-center">
      STATUS
    </td>
    <td class="text-center">
      LOCATION
    </td>
    <td class="text-center">
      DEPARTMENT
    </td>
    <td class="text-center">
      GROUP
    </td>
    <td class="text-center">
      ID
    </td>
    <td class="text-center">
      NAME
    </td>
    <td class="text-center">
      REASON / REMARK
    </td>
    <td class="text-center" style="width: 45px;">
      IN
    </td>
    <td class="text-center">
      LEAVE ID
    </td>
  </tr>

  @foreach ($dailyreport_late as $late)
  <?php
  $staff_late = HRAttendance::join('staffs', 'staffs.id', '=', 'hr_attendances.staff_id')
    ->join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
    ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
    ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=',  'pivot_dept_cate_branches.id')
    ->join('option_branches', 'pivot_dept_cate_branches.branch_id', '=', 'option_branches.id')
    ->leftjoin('option_restday_groups', 'staffs.restday_group_id', '=', 'option_restday_groups.id')
    ->where('hr_attendances.attend_date', '=', $selected_date)
    ->where('staffs.id', $late)
    ->where('pivot_staff_pivotdepts.main', 1)
    ->select('hr_attendances.attend_date', 'option_branches.code', 'pivot_dept_cate_branches.department', 'option_restday_groups.group', 'logins.username', 'staffs.name', 'hr_attendances.leave_id', 'hr_attendances.remarks', 'hr_attendances.in', 'pivot_dept_cate_branches.wh_group_id')
    ->first();

  $in = Carbon::parse($staff_late->in)->format('h:i a');

  if ($staff_late->leave_id != NULL) {
    $leave = HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')
      ->where('hr_leaves.id', '=', $staff_late->leave_id)
      ->select('hr_leaves.id as leave_id', 'hr_leaves.leave_no', 'hr_leaves.leave_year', 'option_leave_types.leave_type_code', 'hr_leaves.reason')
      ->first();

    $status = $leave->leave_type_code;
    $remark = $leave->reason;
    $leave_number = 'HR9-' . str_pad($leave->leave_no, 5, "0", STR_PAD_LEFT) . '/' . $leave->leave_year;
  } else {

    if ($staff_late->attendance_type_id != NULL) {
      $status_code = OptTcms::where('id', '=', $staff_late->attendance_type_id)->first();
      $status = $status_code->leave;
    } else {
      $status = NULL;
    }

    $remark = $staff_late->remarks;
    $leave_number = NULL;
  }
  ?>

  <tr>
    <td class="text-center">
      {{ $no++ }}
    </td>
    <td class="text-center">
      {{ $staff_late->attend_date }}
    </td>
    <td class="text-center">
      LATE
    </td>
    <td class="text-center">
      {{ $staff_late->code }}
    </td>
    <td>
      <div class="DEPARTMENT">
        &nbsp;{{ $staff_late->department }}
      </div>
    </td>
    <td class="text-center">
      {{ $staff_late->group }}
    </td>
    <td class="text-center">
      {{ $staff_late->username }}
    </td>
    <td>
      <div class="NAME">
        &nbsp;{{ $staff_late->name }}
      </div>
    </td>
    <td>
      <div class="REMARK">
        &nbsp;{{ $remark }}
      </div>
    </td>
    <td class="text-center">
      <span class="text-danger">{{ $in }}</span>
    </td>
    <td class="text-center">
      @if ($leave_number != NULL)
      {{ $leave_number }}
      @endif
    </td>
  </tr>
  @endforeach
  @endif


  <!-- OUTSTATION -->
  @if ($dailyreport_outstation->isNotEmpty())
  <?php $no = 1; ?>
  <tr class="top-row">
    <td colspan="11">
      <b>OUTSTATION</b>
    </td>
  </tr>
  <tr class="top-row">
    <td class="text-center">
      NO
    </td>
    <td class="text-center">
      DATE
    </td>
    <td class="text-center">
      STATUS
    </td>
    <td class="text-center">
      LOCATION
    </td>
    <td class="text-center">
      DEPARTMENT
    </td>
    <td class="text-center">
      GROUP
    </td>
    <td class="text-center">
      ID
    </td>
    <td class="text-center">
      NAME
    </td>
    <td colspan="2" class="text-center">
      REASON / REMARK
    </td>
    <td class="text-center">
      LEAVE ID
    </td>
  </tr>

  @foreach ($dailyreport_outstation as $outstation)
  <?php

  if ($outstation->outstation_id != NULL) {
    $out = HROutstation::leftjoin('customers', 'hr_outstations.customer_id', '=', 'customers.id')
      ->where('hr_outstations.id', '=', $outstation->outstation_id)
      ->where('hr_outstations.active', '=', 1)
      ->select('customers.customer', 'hr_outstations.remarks', 'hr_outstations.customer_id')
      ->first();

    $status = 'OUTSTATION';

    if ($out->customer_id != NULL) {
      $remark = $out->customer;
    } else {
      $remark = $out->remarks;
    }

    $contact = NULL;
  } else {

    if ($outstation->attendance_type_id != NULL) {
      $status_code = OptTcms::where('id', '=', $outstation->attendance_type_id)->first();
      $status = $status_code->leave;
    } else {
      $status = NULL;
    }

    $remark = $outstation->remarks;
    $contact = NULL;
  }
  ?>

  <tr>
    <td class="text-center">
      {{ $no++ }}
    </td>
    <td class="text-center">
      {{ $outstation->attend_date }}
    </td>
    <td class="text-center">
      {{ $status }}
    </td>
    <td class="text-center">
      {{ $outstation->code }}
    </td>
    <td>
      <div class="DEPARTMENT">
        &nbsp;{{ $outstation->department }}
      </div>
    </td>
    <td class="text-center">
      {{ $outstation->group }}
    </td>
    <td class="text-center">
      {{ $outstation->username }}
    </td>
    <td>
      <div class="NAME">
        &nbsp;{{ $outstation->name }}
      </div>
    </td>
    <td colspan="2">
      <div class="REMARK">
        &nbsp;{{ $remark }}
      </div>
    </td>
    <td class="text-center">
      {{ $contact }}
    </td>
  </tr>
  @endforeach
  @endif

</table>
@endif