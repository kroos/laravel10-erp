@extends('layouts.app')

@section('content')
<style>
  /* div {
    border: 1px solid black;
  } */
</style>

<?php
use App\Models\Staff;
use App\Models\HumanResources\OptDisciplinaryAction;
use App\Models\HumanResources\OptViolation;

$staff = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->pluck('display_name', 'id')
  ->toArray();

$disciplinary_action = OptDisciplinaryAction::pluck('disciplinary_action', 'id')->toArray();

$violation = OptViolation::select(DB::raw('CONCAT(IFNULL(violation, ""), " - ", IFNULL(remarks, "")) AS display_violation'), 'id')->pluck('display_violation', 'id')->toArray();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Add Discipline</h4>

  {{ Form::open(['route' => ['discipline.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
      {{ Form::select('staff_id', $staff, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'staff_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date', 'Warning Date')}}
    </div>
    <div class="col-md-10 {{ $errors->has('date') ? 'has-error' : '' }}">
      {{ Form::text('date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('disciplinary_action', 'Disciplinary Action')}}
    </div>
    <div class="col-md-10 {{ $errors->has('disciplinary_action_id') ? 'has-error' : '' }}">
      {{ Form::select('disciplinary_action_id', $disciplinary_action, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'disciplinary_action_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('violation', 'Violation')}}
    </div>
    <div class="col-md-10 {{ $errors->has('violation_id') ? 'has-error' : '' }}">
      {{ Form::select('violation_id', $violation, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'violation_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('reason', 'Reason')}}
    </div>
    <div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
      {!! Form::textarea('reason', @$value, ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Please Insert Reason'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('softcopy', 'Softcopy')}}
    </div>
    <div class="col-md-10">
      {!! Form::file('softcopy', ['class' => 'form-control', 'id' => 'softcopy']) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

  <div class="row mt-3">
    <div class="col-md-12 text-center">
    <a href="{{ url()->previous() }}">
        <button class="btn btn-sm btn-outline-secondary">BACK</button>
      </a>
    </div>
  </div>

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({
placeholder: '',
width: '100%',
allowClear: true,
closeOnSelect: true,
});


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
staff_id: {
validators: {
notEmpty: {
message: 'Please select a staff.'
}
}
},

date: {
validators: {
notEmpty: {
message: 'Please select a warning date.'
}
}
},

disciplinary_action_id: {
validators: {
notEmpty: {
message: 'Please select a disciplinary action.'
}
}
},

violation_id: {
validators: {
notEmpty: {
message: 'Please select a violation.'
}
}
},

reason: {
validators: {
notEmpty: {
message: 'Please insert a reason.'
}
}
},

softcopy: {
validators: {
file: {
extension: 'jpeg,jpg,png,bmp,pdf,doc,docx', // no space
type: 'image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', // no space
maxSize: 5242880, // 5120 * 1024,
message: 'The selected file is not valid. Please use jpeg, jpg, png, bmp, pdf or doc and the file is below than 5MB.'
},
}
},

}
})
});
@endsection

@section('nonjquery')

@endsection