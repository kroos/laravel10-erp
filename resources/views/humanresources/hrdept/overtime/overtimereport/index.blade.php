@extends('layouts.app')

@section('content')
<style>
  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 14px;
  }

  .top-row td {
    background-color: #cccccc;
  }
</style>
<?php

use Carbon\Carbon;

use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\HROvertime;

$location = OptBranch::pluck('location', 'id')->toArray();

$no = 1;
$total_col = 0;
$total_hour = '0';

if ($date_start != NULL && $date_end != NULL) {
  $startDate = Carbon::parse($date_start);
  $endDate = Carbon::parse($date_end);
}
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Overtime Report</h4>

  {{ Form::open(['route' => ['overtimereport.index'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row g-3 mb-3">
    <div class="col-auto">
      {{ Form::text('date_start', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_start', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
    </div>
    <div class="col-auto">
      {{ Form::text('date_end', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_end', 'placeholder' => 'Date End', 'autocomplete' => 'off']) }}
    </div>
    <div class="col-auto">
      {{ Form::select('branch', $location, @$value, ['class' => 'form-control branch', 'id' => 'branch', 'placeholder' => '']) }}
    </div>
    <div class="col-auto">
      {!! Form::submit('SUBMIT', ['class' => 'form-control form-control-sm btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

  @if ($overtimes != NULL)
  <div class="row g-3 mb-3">
    <table class="table table-hover table-sm align-middle">
      <tr class="top-row">
        <td class="text-center" style="width: 30px;">
          NO
        </td>
        <td class="text-center" style="width: 55px;">
          ID
        </td>
        <td class="text-center" style="max-width: 150px;">
          NAME
        </td>
        <td class="text-center">
          DEPARTMENT
        </td>
        @for ($date = $startDate; $date->lte($endDate); $date->addDay())
        <td class="text-center" style="max-width: 48px;">
          <?php
          $total_col++;
          $rows[] = $date->format('Y-m-d');
          echo $formattedDate = $date->format('d/m');
          ?>
        </td>
        @endfor
        <td class="text-center" style="max-width: 60px;">
          TOTAL<br />HOURS
        </td>
        <td class="text-center" style="max-width: 70px;">
          SIGNATURE
        </td>
      </tr>

      @foreach ($overtimes as $overtime)
      <?php $total_hour_per_person = '0'; ?>
      <tr>
        <td class="text-truncate text-center" style="width: 30px;">
          {{ $no++ }}
        </td>
        <td class="text-truncate text-center" style="width: 55px;" title="{{ $overtime->username }}">
          {{ $overtime->username }}
        </td>
        <td class="text-truncate" style="max-width: 150px;" title="{{ $overtime->name }}">
          {{ $overtime->name }}
        </td>
        <td class="text-truncate" style="max-width: 1px;" title="{{ $overtime->department }}">
          {{ $overtime->department }}
        </td>
        @foreach ($rows as $row)
        <td class="text-truncate text-center" style="max-width: 48px;">
          <?php
          $ot = HROvertime::join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
            ->where('hr_overtimes.ot_date', '=', $row)
            ->where('hr_overtimes.staff_id', '=', $overtime->staff_id)
            ->where('hr_overtimes.active', 1)
            ->select('hr_overtime_ranges.total_time')
            ->first();

          if ($ot) {
            echo $timeString_per_person = (Carbon::parse($ot->total_time))->format('H:i');

            // Explode the time string into an array of hours, minutes, and seconds
            $timeArray_per_person = explode(':', $timeString_per_person);

            // Calculate the total minutes
            $totalMinutes_per_person = ($timeArray_per_person[0] * 60) + $timeArray_per_person[1];
            $total_hour_per_person = $total_hour_per_person + $totalMinutes_per_person;
          }
          ?>
        </td>
        @endforeach
        <td class="text-center" style="max-width: 60px;">
          <?php
          $total_hour = $total_hour + $total_hour_per_person;

          echo (sprintf('%02d', intdiv($total_hour_per_person, 60)) . ':' . sprintf('%02d', ($total_hour_per_person % 60)));
          ?>
        </td>
        <td style="max-width: 70px;"></td>
      </tr>
      @endforeach

      <tr>
        <td align="right" colspan="{{ $total_col+4 }}">
          TOTAL HOURS
        </td>
        <td class="text-center">
          <?php
          echo (sprintf('%02d', intdiv($total_hour, 60)) . ':' . sprintf('%02d', ($total_hour % 60)));
          ?>
        </td>
        <td></td>
      </tr>
    </table>

    {{ Form::open(['route' => ['overtimereport.print'], 'method' => 'GET',  'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
    <div class="row">
      <div class="text-center">
        <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}">
        <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}">
        <input type="hidden" name="branch" id="branch" value="{{ $branch }}">

        <input type="submit" class="btn btn-sm btn-outline-secondary" value="PRINT" target="_blank">
      </div>
    </div>
    {{ Form::close() }}
  </div>
  @endif

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#date_start, #date_end').datetimepicker({
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
$('#branch').select2({
placeholder: 'Location',
width: '100%',
allowClear: false,
closeOnSelect: true,
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