@extends('layouts.app')

@section('content')

<style>
  div {
    border: 1px solid red;
  }
</style>

<style>
  .img1 {
    text-align: center;
  }
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form : {{ $department->department }}</h4>

  <table height="15px"></table>

  {{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="img1 mb-3">
    <img src="{{ asset('images/appraisal/Bahagian1.jpg') }}" width="800px">
  </div>

  <div class="row mb-3">
    <div style="width: 4%">
      <button type="button" class="col-auto btn btn-sm btn-outline-secondary p1_add">
        <i class="fas fa-plus" aria-hidden="true"></i><br />P1
      </button>
    </div>
    <div class="p1_wrap" style="width: 96%">
      <!-- JAVASCRIPT -->
    </div>
  </div>

  {{ Form::close() }}

</div>
@endsection

@section('js')
<?php
$Sections = App\Models\HumanResources\HRAppraisalSection::select('section', 'id')->get();
$SectionSubs = App\Models\HumanResources\HRAppraisalSectionSub::select('section_sub', 'id')->get();
$MainQuestions = App\Models\HumanResources\HRAppraisalMainQuestion::select('main_question', 'id')->get();
$Questions = App\Models\HumanResources\HRAppraisalQuestion::select('question', 'id')->get();
?>
/////////////////////////////////////////////////////////////////////////////////////
// p1
var num = 0;
var p1_add	= $(".p1_add");
var p1_wrap	= $(".p1_wrap");

$(p1_add).click(function(){
  num++;

  p1_wrap.append(
    '<div class="mb-1">' +
      '<select id="section'+ num +'">' +
        '<option id=""></option>' +
        @foreach ($Sections as $Section)
        '<option id="{!! $Section->id !!}">{!! $Section->section !!}</option>' +
        @endforeach
      '</select>' +
    '</div>' +
    '<div class="mb-1">' +
      '<textarea id="editor'+ num +'"></textarea>' +
    '</div>' +
    '<div class="row mb-5">' +
      '<div style="width: 4%">' +
        '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p2_add'+ num +'">' +
          '<i class="fas fa-plus" aria-hidden="true"></i><br />P2' +
        '</button>' +
      '</div>' +
      '<div class="p2_wrap'+ num +'" style="width: 96%">' +
      '</div>' +
    '</div>'
  ); 

  /////////////////////////////////////////////////////////////////////////////////////
  // p2
  var p2_add	= $(".p2_add" + num);
  var p2_wrap	= $(".p2_wrap" + num);

  $(p2_add).click(function(){
    num++;

    p2_wrap.append(
      '<div class="mb-1">' +
        '<select id="sectionsub'+ num +'">' +
          '<option id=""></option>' +
          @foreach ($SectionSubs as $SectionSub)
          '<option id="{!! $SectionSub->id !!}">{!! $SectionSub->section_sub !!}</option>' +
          @endforeach
        '</select>' +
      '</div>' +
      '<div class="mb-1">' +
        '<textarea id="editor'+ num +'"></textarea>' +
      '</div>' +
      '<div class="row mb-1">' +
        '<div style="width: 4%">' +
          '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p3_add">' +
            '<i class="fas fa-plus" aria-hidden="true"></i><br />P3' +
          '</button>' +
        '</div>' +
        '<div class="p3_wrap" style="width: 96%">' +
        '</div>' +
      '</div>'
    ); 

    // p2
    $('#sectionsub' + num).select2({
      placeholder: 'Please Select',
      width: '100%',
      allowClear: true,
      closeOnSelect: true,
    });

    CKEDITOR.replace('editor' + num, {
      toolbar: [
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
      ]
    });
  })

  // p1
  $('#section' + num).select2({
    placeholder: 'Please Select',
    width: '100%',
    allowClear: true,
    closeOnSelect: true,
  });

  CKEDITOR.replace('editor' + num, {
    toolbar: [
      { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
      { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
      { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
      { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
    ]
  });
})
@endsection