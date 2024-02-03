@extends('layouts.app')

@section('content')

<style>
  div {
    border: 1px solid black;
  }

  .scrollable-div-1 {
    /* Set the width height as needed */
    /*		width: 100%;*/
    height: 850px;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  .scrollable-div-2 {
    /* Set the width height as needed */
    /*		width: 100%;*/
    height: 800px;
    background-color: blanchedalmond;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }
</style>

<?php

use \App\Models\Staff;

$evaluator = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->orderBy('logins.username', 'ASC')
  ->pluck('display_name', 'id')
  ->toArray();

$evaluatees = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=', 'pivot_dept_cate_branches.id')
  ->select('logins.username', 'staffs.*', 'pivot_dept_cate_branches.department')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->where('pivot_staff_pivotdepts.main', 1)
  ->orderBy('pivot_dept_cate_branches.department', 'ASC')
  ->orderBy('logins.username', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form</h4>

  <div class="row">&nbsp;</div>

  <div class="row">
    <div class="col-6">

    <div class="scrollable-div-1">
            @foreach($evaluatees as $evaluatee)
            <div class="form-check mb-1 g-3">
              <input class="form-check-input" name="evaluetee_id[]" type="checkbox" value="{{ $evaluatee->id }}" id="evaluatee_id{{ $evaluatee->id }}">
              <label class="form-check-label" for="evaluatee_id{{ $evaluatee->id }}">[{{ $evaluatee->department }}]<br/>{{ $evaluatee->username }} - {{ $evaluatee->name }}</label>
            </div>
            @endforeach
          </div>

    </div>





    <div class="col-6">
      {{ Form::open(['route' => ['appraisalapoint.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

      <div class="row mb-3">
        <div class="col-2">
          Evaluator
        </div>
        <div class="col-10">
          {!! Form::select( 'evaluator_id', $evaluator, @$value, ['class' => 'form-control select-input form-select', 'id' => 'evaluator_id', 'placeholder' => 'Please Select'] ) !!}
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          Evaluatee
        </div>
        <div class="col-10">
          <div class="scrollable-div-2">
            @foreach($evaluatees as $evaluatee)
            <div class="form-check mb-1 g-3">
              <input class="form-check-input" name="evaluetee_id[]" type="checkbox" value="{{ $evaluatee->id }}" id="evaluatee_id{{ $evaluatee->id }}">
              <label class="form-check-label" for="evaluatee_id{{ $evaluatee->id }}">[{{ $evaluatee->department }}]<br/>{{ $evaluatee->username }} - {{ $evaluatee->name }}</label>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-center m-3">
        {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
      </div>

      {{ Form::close() }}
    </div>
  </div>

</div>
@endsection

@section('js')
$('.form-select').select2({
placeholder: '',
width: '100%',
allowClear: true,
closeOnSelect: true,
});
@endsection