@extends('layouts.app')

@section('content')

<!-- <style>
  div {
    border: 1px solid black;
  }
</style> -->

<?php
$day_type = App\Models\HumanResources\OptDayType::pluck('daytype', 'id')->sortKeys()->toArray();
$tcms = App\Models\HumanResources\OptTcms::pluck('leave_short', 'id')->sortKeys()->toArray();

$staff = $attendance->belongstostaff()->get()->first();
$login = $staff->hasmanylogin()->where('active', '1')->get()->first();
?>

<div class="col-12">
  <div class="d-flex justify-content-center align-items-center">
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
          {!! Form::label( 'attend_date', @$attendance->attend_date, ['class' => 'form-control'] ) !!}
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
          {!! Form::label( 'in', 'IN', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('in') ? 'has-error' : '' }}">
          {!! Form::text( 'in', @$attendance->in, ['class' => 'form-control time-input', 'id' => 'in'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'break', 'BREAK', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('break') ? 'has-error' : '' }}">
          {!! Form::text( 'break', @$attendance->break, ['class' => 'form-control time-input', 'id' => 'break'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'resume', 'RESUME', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('resume') ? 'has-error' : '' }}">
          {!! Form::text( 'resume', @$attendance->resume, ['class' => 'form-control time-input', 'id' => 'resume'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'out', 'OUT', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('out') ? 'has-error' : '' }}">
          {!! Form::text( 'out', @$attendance->out, ['class' => 'form-control time-input', 'id' => 'out'] ) !!}
        </div>
      </div>






      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'duration', 'DURATION', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('time_work_hour') ? 'has-error' : '' }}">
          <!-- {!! Form::text( 'time_work_hour', @$attendance->time_work_hour, ['class' => 'form-control time-input', 'id' => 'time_work_hour'] ) !!} -->
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'overtime', 'OVERTIME', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
        <!-- {!! Form::text( 'time_work_hour', @$attendance->time_work_hour, ['class' => 'form-control time-input', 'id' => 'time_work_hour'] ) !!} -->
        </div>
      </div>













      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'remark', 'REMARK', ['class' => 'form-control border-0'] ) !!}
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
          {!! Form::text( 'hr_remark', @$attendance->hr_remark, ['class' => 'form-control', 'id' => 'hr_remark', 'placeholder' => ''] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'exception', 'EXCEPTION', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('exception') ? 'has-error' : '' }}">
          {!! Form::checkbox( 'exception', @$attendance->exception == 1, ['class' => 'form-control', 'id' => 'exception'] ) !!}
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
// DATE PICKER
$('.time-input').datetimepicker({
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
$('.select-input').select2({
placeholder: '',
width: '100%',
allowClear: true,
closeOnSelect: true,
});
@endsection