@extends('layouts.app')

@section('content')

<style>
  /* div {
    border: 1px solid red;
  } */
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form</h4>

  <div class="row">&nbsp;</div>

  @foreach ($departments as $department)

  <?php
  $form_versions = DB::table('pivot_dept_appraisals')
    ->where('department_id', $department->id)
    ->whereNotNull('version')
    ->groupBy('department_id')
    ->groupBy('version')
    ->orderBy('version', 'ASC')
    ->get();
  ?>

  <div class="row" style="background-color: #f0f0f0; font-size: 20px;">
    <div class="col-sm-12 ">
      <a class="btn btn-primary btn-sm" href="{{ route('appraisalform.create', ['id' => $department->id]) }}" role="button">+</a>
      {{ $department->department }}
    </div>
  </div>

  @foreach ($form_versions as $form_version)
  @if ($form_version->version != NULL)
  <div class="row">
    <div align="right" style="width: 75px;">
      <i class="bi bi-caret-right-fill"></i>
    </div>
    <div class="col-sm-9" style="font-size: 18px;">
      {{ $department->department }} Version {{ $form_version->version }}
    </div>
    <div align="center" style="width: 60px;">
      <a class="fa fa-file-text" href="{{ route('appraisalform.show', ['appraisalform' => $form_version->id]) }}" role="button"></a>
    </div>
    <div align="center" style="width: 60px;">
      <a class="fa fa-pencil" href="" role="button"></a>
    </div>
    <div align="center" style="width: 60px;">

    {{ Form::open(['route' => ['appraisalformduplicate.store'], 'method' => 'GET', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

        <input type="hidden" name="id" id="id" value="{{ $form_version->id }}">

        <input type="submit" class="fa fa-clone" target="_blank">
        
      
    {{ Form::close() }}
      
    <a class="fa fa-clone" href="" role="button"></a>
    </div>
    <div align="center" style="width: 60px;">
      <a class="fa fa-trash" href="" role="button"></a>
    </div>
  </div>
  @endif
  @endforeach
  <div class="row">&nbsp;</div>

  @endforeach

</div>
@endsection

@section('js')

@endsection