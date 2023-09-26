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
$staff = $rleave->belongstostaff->first();
$customer = App\Models\Customer::pluck('customer', 'id')->sortKeys()->toArray();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Edit Replacement Leave</h4>

  {{ Form::model([$rleave, 'route' => ['rleave.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
  <input type="hidden" name="id" id="id" value="{{ $rleave->id }}">

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10">
      {{Form::label('name', @$staff->name)}}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_start', 'Date Start')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('date_start', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_start', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_end', 'Date End')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('date_end', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_end', 'placeholder' => 'Date End', 'autocomplete' => 'off']) }}
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
    <div class="col-md-10">
      {!! Form::text( 'reason', @$value, ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Please Insert'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

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
@endsection

@section('nonjquery')

@endsection