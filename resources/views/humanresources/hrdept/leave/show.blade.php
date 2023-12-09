@extends('layouts.app')

@section('content')
<style>
  @media print {
    body {
      visibility: hidden;
    }

    #printPageButton,
    #back {
      display: none;
    }

    .table-container {
      visibility: visible;
      position: absolute;
      left: 0;
      top: 0;
    }
  }

  .table-container {
    display: table;
    width: 100%;
    border-collapse: collapse;
  }

  .table {
    display: table;
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
    padding-top: 0;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  .table-row {
    display: table-row;
  }

  .table-cell {
    display: table-cell;
    border: 1px solid #b3b3b3;
    padding: 4px;
    box-sizing: border-box;
  }

  .table-cell-top {
    display: table-cell;
    border: 1px solid #b3b3b3;
    border-top: none;
    padding: 4px;
    box-sizing: border-box;
  }

  .table-cell-top-bottom {
    display: table-cell;
    border: 1px solid #b3b3b3;
    border-top: none;
    border-bottom: none;
    padding: 0px;
    box-sizing: border-box;
  }

  .table-cell-hidden {
    display: table-cell;
    border: none;
  }

  .header {
    font-size: 22px;
    text-align: center;
  }

  .theme {
    background-color: #e6e6e6;
  }

  .table-cell-top1 {
    display: table-cell;
    border: 1px solid #b3b3b3;
    border-top: none;
    padding: 0px;
    box-sizing: border-box;
  }
</style>

<?php

use \App\Models\HumanResources\HRAttendance;
use Illuminate\Database\Eloquent\Builder;
use \App\Models\HumanResources\OptLeaveStatus;

$staff = $hrleave->belongstostaff()?->first();
$login = \App\Models\Login::where([['staff_id', $hrleave->staff_id], ['active', 1]])->first();

$count = 0;
$supervisor_no = 0;
$hod_no = 0;
$director_no = 0;
$hr_no = 0;

$backup = $hrleave->hasmanyleaveapprovalbackup?->first();
$supervisor = $hrleave->hasmanyleaveapprovalsupervisor?->first();
$hod = $hrleave->hasmanyleaveapprovalhod?->first();
$director = $hrleave->hasmanyleaveapprovaldir?->first();
$hr = $hrleave->hasmanyleaveapprovalhr?->first();

if ($supervisor) {
  $count++;
  $supervisor_no = $count;
}

if ($hod) {
  $count++;
  $hod_no = $count;
}

if ($director) {
  $count++;
  $director_no = $count;
}

if ($hr) {
  $count++;
  $hr_no = $count;
}

if ($count != 0) {
  $width = 100 / $count;
} else {
  $width = 100;
}

if ((\Carbon\Carbon::parse($hrleave->date_time_start)->format('H:i')) == '00:00') {
  $date_start = \Carbon\Carbon::parse($hrleave->date_time_start)->format('d F Y');
} else {
  $date_start = \Carbon\Carbon::parse($hrleave->date_time_start)->format('d F Y h:i a');
}

if ((\Carbon\Carbon::parse($hrleave->date_time_end)->format('H:i')) == '00:00') {
  $date_end = \Carbon\Carbon::parse($hrleave->date_time_end)->format('d F Y');
} else {
  $date_end = \Carbon\Carbon::parse($hrleave->date_time_end)->format('d F Y h:i a');
}

if ($hrleave->period_day !== 0.0 && $hrleave->period_time == NULL) {
  $total_leave = $hrleave->period_day . ' Days';
} else {
  $total_leave = $hrleave->period_time;
}

if ($backup) {
  $backup_name = $backup->belongstostaff?->name;

  if ($backup->created_at == $backup->updated_at) {
    $approved_date = '-';
  } else {
    $approved_date = \Carbon\Carbon::parse($backup->updated_at)->format('d F Y h:i a');
  }
} else {
  $backup_name = '-';
  $approved_date = '-';
}

$start = \Carbon\Carbon::parse($hrleave->date_time_start)->format('Y-m-d');
$end = \Carbon\Carbon::parse($hrleave->date_time_end)->format('Y-m-d');
$hr_remark = HRAttendance::where('staff_id', '=', $hrleave->staff_id)
  ->whereBetween('attend_date', [$start, $end])
  ->where('hr_remarks', '!=', NULL)
  ->select('hr_remarks')
  ->first();

$auth = \Auth::user()->belongstostaff?->div_id; // 1/2/5
$auth_dept = \Auth::user()->belongstostaff?->belongstomanydepartment()->first()->id; // 14/31
$auth_admin = \Auth::user()->belongstostaff?->authorise_id; // 1

$hrremarksattendance = HRAttendance::where(function (Builder $query) use ($hrleave) {
  $query->whereDate('attend_date', '>=', $hrleave->date_time_start)
    ->whereDate('attend_date', '<=', $hrleave->date_time_end);
})
  ->where('staff_id', $hrleave->staff_id)
  ->where(function (Builder $query) {
    $query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
  })
  ->get();

$leave_status_temp = $hrleave?->belongstooptleavestatus?->status;

if ($leave_status_temp == 'Approved' || $leave_status_temp == 'Waived') {
  $leave_status = $leave_status_temp;
  $leave_color = "width: 20%; background-color: #e6e6e6; color: green";
} elseif ($leave_status_temp == 'Rejected' || $leave_status_temp == 'Cancelled') {
  $leave_status = $leave_status_temp;
  $leave_color = "width: 20%; background-color: #e6e6e6; color: red";
} else {
  $leave_status = "Pending";
  $leave_color = "width: 20%; background-color: #e6e6e6; color: #999900";
}
?>


<div class="col-sm-12 row align-items-start justify-content-center">
  @include('humanresources.hrdept.navhr')
  <h4>Leave Application &nbsp;
    <a href="{{ route('hrleave.edit', $hrleave->id) }}" class="btn btn-sm btn-outline-secondary">
      <i class="fa-solid fa-pen-to-square fa-beat"></i> Edit
    </a>
  </h4>

  <div class="table-container">
    <div class="table">
      <div class="table-row header">
        <div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
        <div class="table-cell" style="width: 40%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
        <div class="table-cell" style="{{ $leave_color }}">{{ $leave_status }}</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 25%;">STAFF ID : {{ @$login->username }}</div>
        <div class="table-cell-top" style="width: 75%;">NAME : {{ @$staff->name }}</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($hrleave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $hrleave->leave_year }}</div>
        <div class="table-cell-top" style="width: 60%;">DATE : {{ @$date_start }} - {{ @$date_end }} </div>
        <div class="table-cell-top" style="width: 25%;">TOTAL : {{ @$total_leave }} </div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ @$hrleave->belongstooptleavetype->leave_type_code }} ({{ @$hrleave->belongstooptleavetype->leave_type }})</div>
        <div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ @$hrleave->reason }} </div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
        <div class="table-cell-top" style="width: 40%;">BACKUP APPROVED : {{ @$approved_date }} </div>
      </div>
    </div>

    @if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
    @if($hrremarksattendance)
    <div class="table">
      @foreach($hrremarksattendance as $key => $value)
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">ATTENDANCE REMARK : {!! $value->remarks !!}<br />HR ATTENDANCE REMARK : {!! $value->hr_remarks !!}</div>
      </div>
      @endforeach
    </div>
    @endif
    @endif

    @if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
    @if($hrleave->remarks)
    <div class="table">
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">LEAVE REMARK : {!! $hrleave->remarks !!}</div>
      </div>
    </div>
    @endif
    @endif

    @if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
    @if($hrleave->hasmanyleaveamend()->count())
    <div class="table">
      @foreach($hrleave->hasmanyleaveamend()->get() as $key => $value1)
      <div class="table-row">
        <div class="table-cell-top" style="width: 100%;">EDIT REMARK : {!! $value1->amend_note !!} on {!! \Carbon\Carbon::parse($value1->created_at)->format('j M Y') !!}</div>
      </div>
      @endforeach
    </div>
    @endif
    @endif

    <div class="table">
      <div class="table-row">
        <div class="table-cell-top text-center" style="width: 100%; background-color: #ffcc99; font-size: 18px;">SIGNATURE / APPROVAL</div>
      </div>
    </div>

    <div class="table">
      <div class="table-row">
        @for ($a = 1; $a <= $count; $a++) @if ($supervisor_no==$a) <div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">SUPERVISOR</div>
      @elseif ($hod_no == $a)
      <div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HOD</div>
      @elseif ($director_no == $a)
      <div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">DIRECTOR</div>
      @elseif ($hr_no == $a)
      <div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HR</div>
      @endif
      @endfor
    </div>
  </div>

  <div class="table">
    <div class="table-row" style="height: 40px;">
      @for ($a = 1; $a <= $count; $a++)
        @if ($supervisor_no==$a)
          <div class="table-cell-top-bottom text-center text-decoration-underline text-wrap text-uppercase" style="width: {{ $width }}%; vertical-align: bottom;">
            {{ @$supervisor->belongstostaff->name }}
          </div>
          @elseif ($hod_no == $a)
          <div class="table-cell-top-bottom text-center text-decoration-underline text-wrap text-uppercase" style="width: {{ $width }}%; vertical-align: bottom;">
            {{ @$hod->belongstostaff->name }}
          </div>
          @elseif ($director_no == $a)
          <div class="table-cell-top-bottom text-center text-decoration-underline text-wrap text-uppercase" style="width: {{ $width }}%; vertical-align: bottom;">
            {{ @$director->belongstostaff->name }}
          </div>
          @elseif ($hr_no == $a)
          <div class="table-cell-top-bottom text-center text-decoration-underline text-wrap text-uppercase" style="width: {{ $width }}%; vertical-align: bottom;">
            {{ @$hr->belongstostaff->name }}
          </div>
        @endif
      @endfor
    </div>

    <div class="table-row">
      @for ($a = 1; $a <= $count; $a++)
        @if ($supervisor_no==$a)
          <?php
          $status = ($supervisor->leave_status_id)?OptLeaveStatus::find(@$supervisor->leave_status_id)->status:'Pending';

          if ($status == 'Approved' || $status == 'Waived') {
            $color = "background-color:transparent; color:green";
          } elseif ($status == 'Rejected' || $status == 'Cancelled') {
            $color = "background-color:transparent; color:red";
          } else {
            $color = "background-color:transparent; color:#999900";
          }
          ?>
          <div class="table-cell-top1 text-center">
            {{ @$supervisor->updated_at }}<br />
            <span style="{{ $color }}">{{ @$status }}</span>
          </div>
        @elseif ($hod_no == $a)
          <?php
          $status = ($hod->leave_status_id)?OptLeaveStatus::find(@$hod->leave_status_id)->status:'Pending';

          if ($status == 'Approved' || $status == 'Waived') {
            $color = "background-color:transparent; color:green";
          } elseif ($status == 'Rejected' || $status == 'Cancelled') {
            $color = "background-color:transparent; color:red";
          } else {
            $color = "background-color:transparent; color:#999900";
          }
          ?>
          <div class="table-cell-top1 text-center">
            {{ @$hod->updated_at }}<br />
            <span style="{{ $color }}">{{ @$status }}</span>
          </div>
        @elseif ($director_no == $a)
          <?php
          $status = ($director->leave_status_id)?OptLeaveStatus::find(@$director->leave_status_id)->status:'Pending';

          if ($status == 'Approved' || $status == 'Waived') {
            $color = "background-color:transparent; color:green";
          } elseif ($status == 'Rejected' || $status == 'Cancelled') {
            $color = "background-color:transparent; color:red";
          } else {
            $color = "background-color:transparent; color:#999900";
          }
          ?>
          <div class="table-cell-top1 text-center">
            {{ @$director->updated_at }}<br />
            <span style="{{ $color }}">{{ @$status }}</span>
          </div>
        @elseif ($hr_no == $a)
          <?php
          $status = ($hr->leave_status_id)?OptLeaveStatus::find(@$hr->leave_status_id)->status:'Pending';

          if ($status == 'Approved' || $status == 'Waived') {
            $color = "background-color:transparent; color:green";
          } elseif ($status == 'Rejected' || $status == 'Cancelled') {
            $color = "background-color:transparent; color:red";
          } else {
            $color = "background-color:transparent; color:#999900";
          }
          ?>
          <div class="table-cell-top1 text-center">
            {{ @$hr->updated_at }}<br />
            <span style="{{ $color }}">{{ @$status }}</span>
          </div>
        @endif
      @endfor
    </div>
  </div>

  <div class="table">
    <div class="table-row">
      Supporting Document : {!! ($hrleave->softcopy)?'<a href="'.asset('storage/leaves/'.$hrleave->softcopy).'" target="_blank">Link</a>':null !!}
    </div>
  </div>

  <div class="table" style="height: 10px;">
    <div class="table-row"></div>
  </div>


  <div class="table">
    <div class="table-row">
      <div class="table-cell-hidden text-center" style="width: 100%;">
        <a href="{{ url()->previous() }}"><button class="btn btn-sm btn-outline-secondary" id="back">Back</button></a>
        <a href=""><button onclick="printPage()" class="btn btn-sm btn-outline-secondary" id="printPageButton">Print</button></a>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
@endsection

@section('nonjquery')
function printPage() {
window.print();
}

function back() {
window.history.back();
}
@endsection
