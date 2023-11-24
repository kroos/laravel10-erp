@extends('layouts.app')

@section('content')
<style>
  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 12px;
  }

  .top-row td {
    background-color: #cccccc;
  }
</style>

<?php

use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROutstation;
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Attendance Daily Report</h4>

  {{ Form::open(['route' => ['attendancedailyreport.index'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row g-3 mb-3">
    <div class="col-auto">
      {{ Form::text('date', @$selected_date, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date', 'autocomplete' => 'off']) }}
    </div>
    <div class="col-auto">
      {!! Form::submit('SEARCH', ['class' => 'form-control form-control-sm btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

  @if ($dailyreport_absent->isNotEmpty() && $dailyreport_late->isNotEmpty() && $dailyreport_outstation->isNotEmpty())
  <div class="row g-3 mb-3">
    <table class="table table-hover table-sm align-middle">
      <?php $no = 1; ?>
      <tr class="top-row">
        <td class="text-center" style="width: 30px;">
          NO
        </td>
        <td class="text-center" style="width: 75px;">
          DATE
        </td>
        <td class="text-center" style="max-width: 100px;">
          STATUS
        </td>
        <td class="text-center" style="max-width: 50px;">
          LOCATION
        </td>
        <td class="text-center" style="max-width: 100px;">
          DEPARTMENT
        </td>
        <td class="text-center" style="width: 55px;">
          GROUP
        </td>
        <td class="text-center" style="width: 55px;">
          ID
        </td>
        <td class="text-center" style="max-width: 150px;">
          NAME
        </td>
        <td class="text-center" style="max-width: 100%;">
          REASON / REMARK
        </td>
        <td class="text-center" style="width: 120px;">
          CONTACT NO
        </td>
      </tr>

      @foreach ($dailyreport_absent as $absent)
      <?php

      if ($absent->leave_id != NULL) {
        $leave = HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')
          ->where('hr_leaves.id', '=', $absent->leave_id)
          ->select('option_leave_types.leave_type_code', 'hr_leaves.reason')
          ->first();

        $status = $leave->leave_type_code;
        $remark = $leave->reason;
        $contact = NULL;
      } else {

        if ($absent->attendance_type_id != NULL) {
          $status_code = OptTcms::where('id', '=', $absent->attendance_type_id)->first();
          $status = $status_code->leave;
        } else {
          $status = NULL;
        }

        $remark = $absent->remarks;
        $contact = NULL;
      }
      ?>

      <tr>
        <td class="text-center">
          {{ $no++ }}
        </td>
        <td class="text-center">
          {{ $absent->attend_date }}
        </td>
        <td class="text-truncate text-center" style="max-width: 100px;" title="{{ $status }}">
          {{ $status }}
        </td>
        <td class="text-truncate text-center" style="max-width: 50px;" title="{{ $absent->code }}">
          {{ $absent->code }}
        </td>
        <td class="text-truncate" style="max-width: 100px;" title="{{ $absent->department }}">
          {{ $absent->department }}
        </td>
        <td class="text-center">
          {{ $absent->group }}
        </td>
        <td class="text-center">
          {{ $absent->username }}
        </td>
        <td class="text-truncate" style="max-width: 150px;" title="{{ $absent->name }}">
          {{ $absent->name }}
        </td>
        <td class="text-truncate" style="max-width: 100%;" title="{{ $remark }}">
          {{ $remark }}
        </td>
        <td class="text-center">
          {{ $contact }}
        </td>
      </tr>
      @endforeach



































      <?php $no = 1; ?>
      <tr class="top-row">
        <td class="text-center" style="width: 30px;">
          NO
        </td>
        <td class="text-center" style="width: 75px;">
          DATE
        </td>
        <td class="text-center" style="max-width: 100px;">
          STATUS
        </td>
        <td class="text-center" style="max-width: 50px;">
          LOCATION
        </td>
        <td class="text-center" style="max-width: 100px;">
          DEPARTMENT
        </td>
        <td class="text-center" style="width: 55px;">
          GROUP
        </td>
        <td class="text-center" style="width: 55px;">
          ID
        </td>
        <td class="text-center" style="max-width: 150px;">
          NAME
        </td>
        <td class="text-center" style="max-width: 100%;">
          REASON / REMARK
        </td>
        <td class="text-center" style="width: 120px;">
          CONTACT NO
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
        <td class="text-truncate text-center" style="max-width: 100px;" title="{{ $status }}">
          {{ $status }}
        </td>
        <td class="text-truncate text-center" style="max-width: 50px;" title="{{ $outstation->code }}">
          {{ $outstation->code }}
        </td>
        <td class="text-truncate" style="max-width: 100px;" title="{{ $outstation->department }}">
          {{ $outstation->department }}
        </td>
        <td class="text-center">
          {{ $outstation->group }}
        </td>
        <td class="text-center">
          {{ $outstation->username }}
        </td>
        <td class="text-truncate" style="max-width: 150px;" title="{{ $outstation->name }}">
          {{ $outstation->name }}
        </td>
        <td class="text-truncate" style="max-width: 100%;" title="{{ $remark }}">
          {{ $remark }}
        </td>
        <td class="text-center">
          {{ $contact }}
        </td>
      </tr>
      @endforeach
    </table>
  </div>
  @endif

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#date').datetimepicker({
icons: {
time: "fas fas-regular fa-clock fa-beat",
date: "fas fas-regular fa-calendar fa-beat",
up: "fa-regular fa-circle-up fa-beat",
down: "fa-regular fa-circle-down fa-beat",
previous: 'fas fas-regular fa-arrow-left fa-beat',
next: 'fas fas-regular fa-arrow-right fa-beat',
today: 'fas fas-regular fa-calenday-day fa-beat',
clear: 'fas fas-regular fa-broom-wide fa-beat',
close: 'fas fas-regular fa-rectangle-xmark fa-beat'
},
format: 'YYYY-MM-DD',
useCurrent: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATOR
$(document).ready(function() {
$('#form').bootstrapValidator({
feedbackIcons: {
valid: '',
invalid: '',
validating: ''
},

fields: {
date_start: {
validators: {
notEmpty: {
message: 'Please select a start date.'
}
}
},

date_end: {
validators: {
notEmpty: {
message: 'Please select a end date.'
}
}
},

branch: {
validators: {
notEmpty: {
message: 'Please select a branch.'
}
}
},

}
})

});
@endsection