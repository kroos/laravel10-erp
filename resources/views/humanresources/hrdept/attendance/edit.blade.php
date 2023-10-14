@extends('layouts.app')

@section('content')








<?php
use \App\Models\HumanResources\HRLeave;
use \Illuminate\Database\Eloquent\Builder;

$day_type = App\Models\HumanResources\OptDayType::pluck('daytype', 'id')->sortKeys()->toArray();


$tcms = App\Models\HumanResources\OptTcms::pluck('leave_short', 'id')->sortKeys()->toArray();

$staff = $attendance->belongstostaff;
$login = $staff->hasmanylogin()->where('active', '1')->get()->first();

if ($attendance->time_work_hour != NULL || $attendance->time_work_hour != '') {
	$time_work_hour = $attendance->time_work_hour;
} else {
	$time_work_hour = '00:00';
}

$dayName = \Carbon\Carbon::parse($attendance->attend_date)->format('l');

if ($dayName == 'Friday') {
	$working_hour = $staff->belongstomanydepartment()->wherePivot('main', 1)->first()->belongstowhgroup()->where('effective_date_start', '<=', $attendance->attend_date)->where('effective_date_end', '>=', $attendance->attend_date)->where('category', 3)->first();
} else {
	$working_hour = $staff->belongstomanydepartment()->wherePivot('main', 1)->first()->belongstowhgroup()->where('effective_date_start', '<=', $attendance->attend_date)->where('effective_date_end', '>=', $attendance->attend_date)->where('category', '!=', 3)->first();
}

$time_start_am = \Carbon\Carbon::parse($working_hour->time_start_am)->format('H:i');
$time_end_am = \Carbon\Carbon::parse($working_hour->time_end_am)->format('H:i');
$time_start_pm = \Carbon\Carbon::parse($working_hour->time_start_pm)->format('H:i');
$time_end_pm = \Carbon\Carbon::parse($working_hour->time_end_pm)->format('H:i');

// $leave = App\Models\HumanResources\HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')->where('date_time_start', '<=', $attendance->attend_date)->where('date_time_end', '>=', $attendance->attend_date)->where('staff_id', '=', $staff->id)->pluck('option_leave_types.leave_type_code', 'hr_leaves.id')->sortKeys()->toArray();
$leaves = HRLeave::where('staff_id', $attendance->belongstostaff->id)
			->whereDate('date_time_start', '<=', $attendance->attend_date)
			->whereDate('date_time_end', '>=', $attendance->attend_date)
			->where(function (Builder $query){
				$query->whereIn('leave_status_id', [5,6])
				->orWhereNull('leave_status_id');
			})
			->orderBy('date_time_start', 'DESC')
			->get();
			// ->ddRawSql();
			// ->pluck('HR9-'.str_pad('leave_no',5,'0',STR_PAD_LEFT).'/'.'leave_year', 'id');
// dd($leave);
if ($leaves->count()) {
	foreach ($leaves as $lv) {
		$leave = [$lv->id => 'HR9-'.str_pad($lv->leave_no,5,'0',STR_PAD_LEFT).'/'.$lv->leave_year];
	}
} else {
	$leave = [];
}

?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<div class="d-flex justify-content-center align-items-start">
		<div class="col-md-7">

			{!! Form::model($attendance, ['route' => ['attendance.update', $attendance->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}
			<input type="hidden" name="staff_id" value="<?php echo $staff->id; ?>">

			<h5>Attendance Edit</h5>

			<div class="row mt-3"></div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'id', 'ID', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9">
					{!! Form::label( 'id', @$login->username, ['class' => 'form-control'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'name', 'NAME', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9">
					{!! Form::label( 'name', @$staff->name, ['class' => 'form-control'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'date', 'DATE', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9">
					{!! Form::text( 'attend_date', @$attendance->attend_date, ['class' => 'form-control', 'id' => 'attend_date', 'readonly' => 'readonly']) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'day_type', 'DAY TYPE', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('daytype_id') ? 'has-error' : '' }}">
					{!! Form::select( 'daytype_id', $day_type, @$value, ['class' => 'form-control select-input', 'id' => 'daytype_id', 'placeholder' => 'Please Select'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'attendance_type', 'CAUSE', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('attendance_type_id') ? 'has-error' : '' }}">
					{!! Form::select( 'attendance_type_id', $tcms, @$value, ['class' => 'form-control select-input', 'id' => 'attendance_type_id', 'placeholder' => ''] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'leave_id', 'LEAVE', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('leave_id') ? 'has-error' : '' }}">
					{!! Form::select( 'leave_id', $leave, @$value, ['class' => 'form-control', 'id' => 'leave_id', 'placeholder' => 'Please Choose'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'in', 'IN', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('in') ? 'has-error' : '' }}">
					{!! Form::text( 'in', @$attendance->in, ['class' => 'form-control in-input', 'id' => 'in'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'break', 'BREAK', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('break') ? 'has-error' : '' }}">
					{!! Form::text( 'break', @$attendance->break, ['class' => 'form-control break-input', 'id' => 'break'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'resume', 'RESUME', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('resume') ? 'has-error' : '' }}">
					{!! Form::text( 'resume', @$attendance->resume, ['class' => 'form-control resume-input', 'id' => 'resume'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'out', 'OUT', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('out') ? 'has-error' : '' }}">
					{!! Form::text( 'out', @$attendance->out, ['class' => 'form-control out-input', 'id' => 'out'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'duration', 'DURATION', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('time_work_hour') ? 'has-error' : '' }}">
					{!! Form::text( 'time_work_hour', @$time_work_hour, ['class' => 'form-control duration-input', 'id' => 'time_work_hour'] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'remarks', 'REMARK', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('remark') ? 'has-error' : '' }}">
					{!! Form::text( 'remark', @$attendance->remark, ['class' => 'form-control', 'id' => 'remark', 'placeholder' => ''] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'hr_remark', 'HR REMARK', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('hr_remark') ? 'has-error' : '' }}">
					{!! Form::text( 'hr_remarks', @$attendance->hr_remark, ['class' => 'form-control', 'id' => 'hr_remark', 'placeholder' => ''] ) !!}
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-3">
					{!! Form::label( 'exception', 'EXCEPTION', ['class' => 'form-control border-0'] ) !!}
				</div>
				<div class="col-md-9 {{ $errors->has('exception') ? 'has-error' : '' }}">
					{{ Form::checkbox('exception', 1, null, ['id' => 'exception']) }}
				</div>
			</div>

			<div class="row mt-4">
				<div class="text-center">
					{!! Form::button('Update', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
				</div>
			</div>

			{{ Form::close() }}

			<div class="row mt-4 text-center">
				<a href="{{ url()->previous() }}">
					<button class="btn btn-sm btn-outline-secondary">Back</button>
				</a>
			</div>

		</div>
	</div>
</div>
@endsection


@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER IN
$('#in').datetimepicker({
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
	format: 'HH:mm',
	useCurrent: false,
})
.on('dp.change dp.update', function(e) {

	var breakStr = {!! json_encode($time_end_am) !!};
	var breakEnd = {!! json_encode($time_start_pm) !!};

	if ($('#in').val() > {!! json_encode($time_start_am) !!}) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = {!! json_encode($time_start_am) !!};
	}

	if ($('#break').val() < {!! json_encode($time_end_am) !!}) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = {!! json_encode($time_end_am) !!};
	}

	if ($('#resume').val() > {!! json_encode($time_start_pm) !!}) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = {!! json_encode($time_start_pm) !!};
	}

	if ($('#out').val() < {!! json_encode($time_end_pm) !!}) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = {!! json_encode($time_end_pm) !!};
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER BREAK
$('.break-input').datetimepicker({
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
	format: 'HH:mm',
	useCurrent: false,
})
.on('dp.change dp.update', function(e) {

	var breakStr = {!! json_encode($time_end_am) !!};
	var breakEnd = {!! json_encode($time_start_pm) !!};

	if ($('#in').val() > {!! json_encode($time_start_am) !!}) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = {!! json_encode($time_start_am) !!};
	}

	if ($('#break').val() < {!! json_encode($time_end_am) !!}) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = {!! json_encode($time_end_am) !!};
	}

	if ($('#resume').val() > {!! json_encode($time_start_pm) !!}) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = {!! json_encode($time_start_pm) !!};
	}

	if ($('#out').val() < {!! json_encode($time_end_pm) !!}) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = {!! json_encode($time_end_pm) !!};
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER RESUME
$('.resume-input').datetimepicker({
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
	format: 'HH:mm',
	useCurrent: false,
})
.on('dp.change dp.update', function(e) {

	var breakStr = {!! json_encode($time_end_am) !!};
	var breakEnd = {!! json_encode($time_start_pm) !!};

	if ($('#in').val() > {!! json_encode($time_start_am) !!}) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = {!! json_encode($time_start_am) !!};
	}

	if ($('#break').val() < {!! json_encode($time_end_am) !!}) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = {!! json_encode($time_end_am) !!};
	}

	if ($('#resume').val() > {!! json_encode($time_start_pm) !!}) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = {!! json_encode($time_start_pm) !!};
	}

	if ($('#out').val() < {!! json_encode($time_end_pm) !!}) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = {!! json_encode($time_end_pm) !!};
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER OUT
$('.out-input').datetimepicker({
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
	format: 'HH:mm',
	useCurrent: false,
})
.on('dp.change dp.update', function(e) {

	var breakStr = {!! json_encode($time_end_am) !!};
	var breakEnd = {!! json_encode($time_start_pm) !!};

	if ($('#in').val() > {!! json_encode($time_start_am) !!}) {
		var inTime = $('#in').val();
	} else if ($('#in').val() == '00:00') {
		var inTime = '00:00';
	} else {
		var inTime = {!! json_encode($time_start_am) !!};
	}

	if ($('#break').val() < {!! json_encode($time_end_am) !!}) {
		var breakTime = $('#break').val();
	} else if ($('#break').val() == '00:00') {
		var breakTime = '00:00';
	} else {
		var breakTime = {!! json_encode($time_end_am) !!};
	}

	if ($('#resume').val() > {!! json_encode($time_start_pm) !!}) {
		var resumeTime = $('#resume').val();
	} else if ($('#resume').val() == '00:00') {
		var resumeTime = '00:00';
	} else {
		var resumeTime = {!! json_encode($time_start_pm) !!};
	}

	if ($('#out').val() < {!! json_encode($time_end_pm) !!}) {
		var outTime = $('#out').val();
	} else if ($('#out').val() == '00:00') {
		var outTime = '00:00';
	} else {
		var outTime = {!! json_encode($time_end_pm) !!};
	}

	// Validate input format (HH:mm)
	var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

	if (timeRegex.test(inTime) && timeRegex.test(breakTime) && timeRegex.test(resumeTime) && timeRegex.test(outTime)) {

		if (inTime != '00:00' && breakTime != '00:00' && outTime == '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = breakTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';

		} else if (inTime != '00:00' && outTime != '00:00') {
			var startTimeStr = inTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			var lunchStr = moment(`${breakStr}`, 'HH:mm');
			var lunchEnd = moment(`${breakEnd}`, 'HH:mm');

			var duration_break = moment.duration(lunchEnd.diff(lunchStr));

			var hours_break = duration_break.hours();
			var minutes_break = duration_break.minutes();

			var breakTimeDuration = `${hours_break.toString().padStart(2, '0')}:${minutes_break.toString().padStart(2, '0')}`;

		} else if (inTime == '00:00' && resumeTime != '00:00' && outTime != '00:00') {
			var startTimeStr = resumeTime;
			var endTimeStr = outTime;

			// TEA BREAK
			if (startTimeStr > '10:15') {
				var teaTime = '00:00';
			} else {
				var teaTime = '00:15';
			}

			// LUNCH BREAK
			breakTimeDuration = '00:00';
		}

		var startTime = moment(`${startTimeStr}`, 'HH:mm');
		var endTime = moment(`${endTimeStr}`, 'HH:mm');

		var duration = moment.duration(endTime.diff(startTime));

		var hours = duration.hours();
		var minutes = duration.minutes();

		var formattedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

		var filter1 = moment(`${formattedDuration}`, 'HH:mm').subtract(`${teaTime}`, 'HH:mm');
		var Duration1 = filter1.format('HH:mm')

		var filter2 = moment(`${Duration1}`, 'HH:mm').subtract(`${breakTimeDuration}`, 'HH:mm');
		var Duration2 = filter2.format('HH:mm')

		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = Duration2;
	} else {
		var inputElement = document.getElementById('time_work_hour');
		inputElement.value = 'Invalid Time Format';
	}
});


// DATE PICKER DURATION
$('.duration-input').datetimepicker({
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
	format: 'HH:mm',
	useCurrent: false,
});


/////////////////////////////////////////////////////////////////////////////////////////
// SELECTION
$('#leave_id,#attendance_type_id,#daytype_id').select2({
	placeholder: 'Please Choose',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATION
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: '',
			invalid: '',
			validating: ''
		},
		fields: {

			daytype_id: {
				validators: {
					notEmpty: {
						message: 'Please select a day type.'
					},
				}
			},

			in: {
				validators: {
					notEmpty: {
						message: 'Please insert in time.'
					},
				}
			},

			break: {
				validators: {
					notEmpty: {
						message: 'Please insert break time.'
					},
				}
			},

			resume: {
				validators: {
					notEmpty: {
						message: 'Please insert resume time.'
					},
				}
			},

			out: {
				validators: {
					notEmpty: {
						message: 'Please insert out time.'
					},
				}
			},

		}
	})
});
@endsection
