@extends('layouts.app')

@section('content')
<style>
  /* div {
    border: 1px solid black;
  } */
</style>

<style>
  .scrollable-div {
    /* Set the width height as needed */
    width: 100%;
    height: 400px;
    background-color: blanchedalmond;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  p {
    margin-top: 4px;
    margin-bottom: 4px;
  }
</style>

<?php
$customer = App\Models\Customer::pluck('customer', 'id')->sortKeys()->toArray();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Edit Replacement Leave</h4>

  {{ Form::model($rleave, ['route' => ['rleave.update', $rleave->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10">
      {{Form::label('name', @$rleave->belongstostaff()->first()->name)}}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_start', 'Date Start')}}
    </div>
    <div class="col-md-10 {{ $errors->has('date_start') ? 'has-error' : '' }}">
      {{ Form::text('date_start', @$rleave->date_start, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_start', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_end', 'Date End')}}
    </div>
    <div class="col-md-10 {{ $errors->has('date_end') ? 'has-error' : '' }}">
      {{ Form::text('date_end', @$rleave->date_end, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_end', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('leave_total', 'Total Day')}} <i class="bi bi-question-circle" data-toggle="tooltip" title="Total day for replacement leave."></i>
    </div>
    <div class="col-md-10 {{ $errors->has('leave_total') ? 'has-error' : '' }}">
      {{ Form::text('leave_total', @$rleave->leave_total, ['class' => 'form-control form-control-sm col-auto', 'id' => 'leave_total', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('leave_utilize', 'Total Utilize')}} <i class="bi bi-question-circle" data-toggle="tooltip" title="Total day used in leave."></i>
    </div>
    <div class="col-md-10 {{ $errors->has('leave_utilize') ? 'has-error' : '' }}">
      {{ Form::text('leave_utilize', @$rleave->leave_utilize, ['class' => 'form-control form-control-sm col-auto', 'id' => 'leave_utilize', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('leave_balance', 'Total Balance')}} <i class="bi bi-question-circle" data-toggle="tooltip" title="Total balance left after used."></i>
    </div>
    <div class="col-md-10 {{ $errors->has('leave_balance') ? 'has-error' : '' }}">
      {{ Form::text('leave_balance', @$rleave->leave_balance, ['class' => 'form-control form-control-sm col-auto', 'id' => 'leave_balance', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('customer_id', 'Customer')}}
    </div>
    <div class="col-md-10">
      {!! Form::select( 'customer_id', $customer, @$value, ['class' => 'form-control select-input', 'id' => 'customer_id', 'placeholder' => 'Please Select'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('reason', 'Reason')}}
    </div>
    <div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
      {!! Form::text( 'reason', @$rleave->reason, ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Please Insert'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('UPDATE', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
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
// CHECK ALL STAFF
$("#checkAll").change(function () {
$(".staff").prop('checked', this.checked);
});

// CHECK ALL GROUP 1
$("#checkG1").change(function () {
$(".group1").prop('checked', this.checked);
});

// CHECK ALL GROUP 2
$("#checkG2").change(function () {
$(".group2").prop('checked', this.checked);
});


/////////////////////////////////////////////////////////////////////////////////////////
// SELECTION
$('.select-input').select2({
placeholder: 'Please Select',
width: '100%',
allowClear: true,
closeOnSelect: true,
});


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
message: 'Please select a date.'
}
}
},

date_end: {
validators: {
notEmpty: {
message: 'Please select a date.'
}
}
},

leave_total: {
validators: {
notEmpty: {
message: 'Please insert a value. 0 by default.'
},
numeric: {
message: 'The value is not numeric'
}
}
},

leave_utilize: {
validators: {
notEmpty: {
message: 'Please insert a value. 0 by default.'
},
numeric: {
message: 'The value is not numeric'
}
}
},

leave_balance: {
validators: {
notEmpty: {
message: 'Please insert a value. 0 by default.'
},
numeric: {
message: 'The value is not numeric'
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

}
})
});


/////////////////////////////////////////////////////////////////////////////////////////
// TOOLTIP
$(function () {
$('[data-toggle="tooltip"]').tooltip()
})
@endsection

@section('nonjquery')

@endsection