@extends('layouts.app')

@section('content')
<style>
	@media print {
		body {
			visibility: hidden;
		}

		#printPageButton, #back {
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
	$total_leave =$hrleave->period_day . ' Days';
} else {
	$total_leave =$hrleave->period_time;
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
$hr_remark = \App\Models\HumanResources\HRAttendance::where('hr_attendances.staff_id', '=', $hrleave->staff_id)
->whereBetween('hr_attendances.attend_date', [$start, $end])
->where('hr_attendances.hr_remarks', '!=', NULL)
->select('hr_attendances.hr_remarks')
->first();
?>

<div class="col-sm-12 row">
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
				<div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($hrleave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $hrleave->leave_year }}</div>
				<div class="table-cell-top" style="width: 60%;">DATE : {{ @$date_start }} - {{ @$date_end }} </div>
				<div class="table-cell-top" style="width: 25%;">TOTAL : {{ @$total_leave }} </div>
			</div>
		</div>

		<div class="table">
			<div class="table-row">
				<div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ $hrleave->belongstooptleavetype->leave_type_code }} ({{ $hrleave->belongstooptleavetype->leave_type }})</div>
				<div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ $hrleave->reason }} </div>
			</div>
		</div>

		<div class="table">
			<div class="table-row">
				<div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
				<div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
			</div>
		</div>

		@if ($hr_remark?->hr_remarks != NULL && $hr_remark?->hr_remarks != '')
		<div class="table">
			<div class="table-row">
				<div class="table-cell-top text-wrap" style="width: 100%;">HR REMARK : {{ @$hr_remark?->hr_remarks }}</div>
			</div>
		</div>
		@endif

		<div class="table">
			<div class="table-row">
				<div class="table-cell-top text-center" style="width: 100%; background-color: #ffcc99; font-size: 18px;">SIGNATURE / APPROVALS</div>
			</div>
		</div>

		<div class="table">
			<div class="table-row">
				@for ($a = 1; $a <= $count; $a++)
					@if ($supervisor_no==$a)
						<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">SUPERVISOR</div>
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
			<div class="table-row" style="height: 50px;">
				@for ($a = 1; $a <= $count; $a++)
					@if ($supervisor_no==$a)
						<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$supervisor->belongstostaff->name }}</div>
					@elseif ($hod_no == $a)
						<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hod->belongstostaff->name }}</div>
					@elseif ($director_no == $a)
						<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$director->belongstostaff->name }}</div>
					@elseif ($hr_no == $a)
						<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hr->belongstostaff->name }}</div>
					@endif
				@endfor
			</div>
			<div class="table-row">
				@for ($a = 1; $a <= $count; $a++)
					@if ($supervisor_no==$a)
						<div class="table-cell-top1 text-center">{{ @$supervisor->updated_at }}</div>
					@elseif ($hod_no == $a)
						<div class="table-cell-top1 text-center">{{ @$hod->updated_at }}</div>
					@elseif ($director_no == $a)
						<div class="table-cell-top1 text-center">{{ @$director->updated_at }}</div>
					@elseif ($hr_no == $a)
						<div class="table-cell-top1 text-center">{{ @$hr->updated_at }}</div>
					@endif
				@endfor
			</div>
		</div>
		<p>&nbsp;</p>
		<div class="col-sm-12 justify-content-center align-items-start">
			<div class="col-auto text-center">
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
