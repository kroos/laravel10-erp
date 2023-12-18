@extends('layouts.app')

@section('content')

<style>
  /* div {
		border: 1px solid red;
	} */
</style>

<style>
  .img1 {
    text-align: center;
  }
</style>

<?php
use App\Models\HumanResources\HRAppraisalSection;
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form : {{ $department->department }}</h4>

  <table height="15px"></table>

  {{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

    <div class="img1">
      <img src="{{ asset('images/appraisal/Bahagian1.jpg') }}" width="800px">
    </div>

    {{ Form::select('section', HRAppraisalSection::pluck('section', 'id')->toArray(), @$value, ['class' => 'form-control form-select', 'id' => 'section', 'placeholder' => 'section', 'autocomplete' => 'off']) }}

    <textarea id="editor1"></textarea>

    <textarea id="editor2"></textarea>

  {{ Form::close() }}

</div>

@endsection

@section('js')
CKEDITOR.replace('editor1', {
toolbar: [
{ name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
{ name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
{ name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
]
});

CKEDITOR.replace('editor2', {
toolbar: [
{ name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
{ name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
{ name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
]
});

@endsection