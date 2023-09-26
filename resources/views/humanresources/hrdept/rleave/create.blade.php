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
    background-color:blanchedalmond;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  p {
    margin-top: 4px;
    margin-bottom: 4px;
  }
</style>

<?php
use App\Models\Staff;
use App\Models\Customer;

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
->where('staffs.active', 1)
->where('logins.active', 1)
->where(function ($query) {
  $query->where('staffs.div_id', '!=', 2)
  ->orWhereNull('staffs.div_id');
})
->select('staffs.id as staffID', 'staffs.*', 'logins.*')
->orderBy('logins.username', 'asc')
->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Add Replacement Leave</h4>

  {{ Form::open(['route' => ['rleave.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10">
      <p>
        <input type="checkbox" id="checkAll"> <label>Check All</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="checkbox" id="checkG1"> <label>Check Group 1</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="checkbox" id="checkG2"> <label>Check Group 2</label>&nbsp;&nbsp;&nbsp;&nbsp;
      </p>
      <div class="scrollable-div">
          @foreach ($staffs as $staff)
          <p>
            <input type="checkbox" class="staff group{{ $staff->restday_group_id }}" name="staff_id[]" id="staff_id" value="{{ $staff->staffID }}">
            <label>{{ $staff->username }} - Group {{ $staff->restday_group_id }} _ {{ $staff->name }}</label>
          </p>
          @endforeach
      </div>
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
      {{Form::select('customer_id', Customer::pluck('customer', 'id')->toArray(), @$value, ['class' => 'form-control customer_id', 'id' => 'customer_id', 'placeholder' => ''])}}
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
$('#customer_id').select2({
placeholder: '',
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