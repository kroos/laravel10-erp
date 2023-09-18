@extends('layouts.app')

@section('content')
<script>
  function printPage() {
    window.print();
  }
</script>

<style>
  @media print {
    body {
      visibility: hidden;
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
    padding: 4px;
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
</style>

<?php
$staff = $leave->belongstostaff()->get()->first();
$login = $staff->hasmanylogin()->get()->first();

$count = 0;
$supervisor_no = 0;
$hod_no = 0;
$director_no = 0;
$hr_no = 0;

$backup = $leave->hasmanyleaveapprovalbackup->first();
$supervisor = $leave->hasmanyleaveapprovalsupervisor->first();
$hod = $leave->hasmanyleaveapprovalhod->first();
$director = $leave->hasmanyleaveapprovaldir->first();
$hr = $leave->hasmanyleaveapprovalhr->first();


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

$width = 100 / $count;

if ((\Carbon\Carbon::parse($leave->date_time_start)->format('H:i')) == '00:00') {
  $date_start = \Carbon\Carbon::parse($leave->date_time_start)->format('d F Y');
} else {
  $date_start = \Carbon\Carbon::parse($leave->date_time_start)->format('d F Y h:i a');
}

if ((\Carbon\Carbon::parse($leave->date_time_end)->format('H:i')) == '00:00') {
  $date_end = \Carbon\Carbon::parse($leave->date_time_end)->format('d F Y');
} else {
  $date_end = \Carbon\Carbon::parse($leave->date_time_end)->format('d F Y h:i a');
}

if ($leave->period_day !== 0.0 && $leave->period_time == NULL) {
  $total_leave = $leave->period_day . ' Days';
} else {
  $total_leave = $leave->period_time;
}

if (($backup->belongstostaff->name) != NULL) {
  $backup_name = $backup->belongstostaff->name;
} else {
  $backup_name = '-';
}

if ($backup->created_at == $backup->updated_at) {
  $approved_date = '-';
} else {
  $approved_date = \Carbon\Carbon::parse($backup->updated_at)->format('d F Y h:i a');
}

// if ($supervisor->) {

// }
?>

<div class="table-container">
  <div class="table">
    <div class="table-row header">
      <div class="table-cell" style="width: 40%; background-color: #f2f2f2;">IPMA INDUSTRY SDN.BHD.</div>
      <div class="table-cell" style="width: 60%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
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
      <div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($leave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ @$leave->leave_year }}</div>
      <div class="table-cell-top" style="width: 60%;">DATE : {{ @$date_start }} - {{ @$date_end }} </div>
      <div class="table-cell-top" style="width: 25%;">TOTAL : {{ @$total_leave }} </div>
    </div>
  </div>

  <div class="table">
    <div class="table-row">
      <div class="table-cell-top" style="width: 45%;">LEAVE TYPE : {{ @$leave->belongstooptleavetype->leave_type_code }} ({{ @$leave->belongstooptleavetype->leave_type }})</div>
      <div class="table-cell-top" style="width: 55%;">REASON : {{ @$leave->reason }} </div>
    </div>
  </div>

  <div class="table">
    <div class="table-row">
      <div class="table-cell-top" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
      <div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
    </div>
  </div>

  <div class="table">
    <div class="table-row">
      <div class="table-cell-top" style="width: 100%; background-color: #e6e6e6; text-align: center; font-size: 18px;">SIGNATURE / APPROVALS</div>
    </div>
  </div>

  <div class="table">
    <div class="table-row">
    @for ($a = 1; $a <= $count; $a++) 
    @if ($supervisor_no==$a) 
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; background-color: #f2f2f2; font-size: 18px;">SUPERVISOR</div>
    @elseif ($hod_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; background-color: #f2f2f2; font-size: 18px;">HOD</div>
    @elseif ($director_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; background-color: #f2f2f2; font-size: 18px;">DIRECTOR</div>
    @elseif ($hr_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; background-color: #f2f2f2; font-size: 18px;">HR</div>
    @endif
    @endfor
  </div>
</div>

  <div class="table" style="height: 40px;">
    <div class="table-row">
    @for ($a = 1; $a <= $count; $a++) 
    @if ($supervisor_no==$a) 
    <div class="table-cell-top" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$supervisor->staff_id }}</div>
    @elseif ($hod_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hod->staff_id }}</div>
    @elseif ($director_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$director->staff_id }}</div>
    @elseif ($hr_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hr->staff_id }}</div>
    @endif
    @endfor
  </div>
</div>


  <div class="table" style="height: 2px;">
    <div class="table-row">
    @for ($a = 1; $a <= $count; $a++) 
    @if ($supervisor_no==$a) 
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; padding:0px;">____________________________________</div>
    @elseif ($hod_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; padding:0px;">____________________________________</div>
    @elseif ($director_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; padding:0px;">____________________________________</div>
    @elseif ($hr_no == $a)
    <div class="table-cell-top" style="width: {{ $width }}%; text-align: center; padding:0px;">____________________________________</div>
    @endif
    @endfor
  </div>
</div>






<div class="table" style="height: 10px;">
  <div class="table-row"></div>
</div>

<div class="table">
  <div class="table-row">
    <div class="table-cell-hidden" style="width: 100%; text-align: center;"><button onclick="printPage()" class="btn btn-sm btn-outline-secondary">Print</button></div>
  </div>
</div>

</div>

@endsection

@section('js')

@endsection