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

  {{ Form::open(['route' => ['appraisalform.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

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

  <div class="d-flex justify-content-center m-3">
		{!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
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
    '<div class="section">' +
      '<div class="row mb-1">' +
        '<div class="col-sm-1">' +
          '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_section" id="delete_section' + num + '" data-id="' + num + '">' +
            '<i class="fas fa-trash" aria-hidden="true"></i>' +
          '</button>' +
        '</div>' +
        '<div class="col-sm-1 {{ $errors->has('p1.*.section_sort') ? 'has-error' : '' }}">' +
          '<input type="text" name="p1[' + num + '][section_sort]" class="form-control form-control-sm" placeholder="Sort">' +
        '</div>' +
        '<div class="col-sm-10 {{ $errors->has('p1.*.section') ? 'has-error' : '' }}">' +
          '<select id="section'+ num +'" name="p1[' + num + '][section]" autocomplete="off">' +
            '<option value=""></option>' +
            @foreach ($Sections as $Section)
            '<option value="{!! $Section->section !!}">{!! $Section->section !!}</option>' +
            @endforeach
          '</select>' +
        '</div>' +
      '</div>' +
      '<div class="mb-1 {{ $errors->has('p1.*.section_text') ? 'has-error' : '' }}">' +
        '<textarea id="editor'+ num +'" name="p1[' + num + '][section_text]"></textarea>' +
      '</div>' +
      '<div class="row mb-5">' +
        '<div style="width: 4%">' +
          '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p2_add'+ num +'">' +
            '<i class="fas fa-plus" aria-hidden="true"></i><br />P2' +
          '</button>' +
        '</div>' +
        '<div class="p2_wrap'+ num +'" style="width: 96%">' +
        '</div>' +
      '</div>' +
    '</div>'
  ); 

  // p1
  $('#section' + num).select2({
    placeholder: 'Part 1 : Please Select',
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

  $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1[' + num + '][section_sort]"]'));
  $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1[' + num + '][section]"]'));
	$('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1[' + num + '][section_text]"]'));

  $(p1_wrap).on("click",".remove_section", function(e){
    var sectionId = $(this).data('id');
    e.preventDefault();
    var $row = $(this).parent().parent().parent();
    var $option1 = $row.find('[name="p1[' + sectionId + '][section_sort]"]');
    var $option2 = $row.find('[name="p1[' + sectionId + '][section]"]');
    var $option3 = $row.find('[name="p1[' + sectionId + '][section_text]"]');
    $row.remove();

    $('#form').bootstrapValidator('removeField', $option1);
    $('#form').bootstrapValidator('removeField', $option2);
    $('#form').bootstrapValidator('removeField', $option3);
    console.log(num);
  });


  /////////////////////////////////////////////////////////////////////////////////////
  // p2
  var p2_add	= $(".p2_add" + num);
  var p2_wrap	= $(".p2_wrap" + num);

  $(p2_add).click(function(){
    num++;

    p2_wrap.append(
      '<div class="sectionsub">' +
        '<div class="row mb-1">' +
          '<div class="col-sm-1">' +
            '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_sectionsub" id="delete_sectionsub' + num + '" data-id="' + num + '">' +
              '<i class="fas fa-trash" aria-hidden="true"></i>' +
            '</button>' +
          '</div>' +
          '<div class="col-sm-1 {{ $errors->has('p2.*.sectionsub_sort') ? 'has-error' : '' }}">' +
            '<input type="text" name="p2[' + num + '][sectionsub_sort]" class="form-control form-control-sm" placeholder="Sort">' +
          '</div>' +
          '<div class="col-sm-10 {{ $errors->has('p2.*.sectionsub') ? 'has-error' : '' }}">' +
            '<select id="sectionsub'+ num +'" name="p2[' + num + '][sectionsub]" autocomplete="off">' +
              '<option value=""></option>' +
              @foreach ($SectionSubs as $SectionSub)
              '<option value="{!! $SectionSub->section_sub !!}">{!! $SectionSub->section_sub !!}</option>' +
              @endforeach
            '</select>' +
          '</div>' +
        '</div>' +
        '<div class="mb-1 {{ $errors->has('p2.*.sectionsub_text') ? 'has-error' : '' }}">' +
          '<textarea id="editor'+ num +'" name="p2[' + num + '][sectionsub_text]"></textarea>' +
        '</div>' +
        '<div class="row mb-1">' +
          '<div style="width: 4%">' +
            '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p3_add">' +
              '<i class="fas fa-plus" aria-hidden="true"></i><br />P3' +
            '</button>' +
          '</div>' +
          '<div class="p3_wrap" style="width: 96%">' +
          '</div>' +
        '</div>' +
      '</div>'
    ); 

    // p2
    $('#sectionsub' + num).select2({
      placeholder: 'Part 2 : Please Select',
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

    $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p2[' + num + '][sectionsub_sort]"]'));
    $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2[' + num + '][sectionsub]"]'));
		$('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2[' + num + '][sectionsub_text]"]'));

    $(p2_wrap).on("click",".remove_sectionsub", function(e){
      var sectionsubId = $(this).data('id');
      e.preventDefault();
      var $row = $(this).parent().parent().parent();
      var $option1 = $row.find('[name="p2[' + sectionsubId + '][sectionsub_sort]"]');
      var $option2 = $row.find('[name="p2[' + sectionsubId + '][sectionsub]"]');
      var $option3 = $row.find('[name="p2[' + sectionsubId + '][sectionsub_text]"]');
      $row.remove();

      $('#form').bootstrapValidator('removeField', $option1);
      $('#form').bootstrapValidator('removeField', $option2);
      $('#form').bootstrapValidator('removeField', $option3);
      console.log(num);
    });


    /////////////////////////////////////////////////////////////////////////////////////
    // p3
    var p3_add	= $(".p3_add" + num);
    var p3_wrap	= $(".p3_wrap" + num);

    $(p3_add).click(function(){
      num++;

      p3_wrap.append(
        '<div class="mainquestion">' +
          '<div class="row mb-1">' +
            '<div class="col-sm-1">' +
              '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_mainquestion" id="delete_mainquestion' + num + '" data-id="' + num + '">' +
                '<i class="fas fa-trash" aria-hidden="true"></i>' +
              '</button>' +
            '</div>' +
            '<div class="col-sm-1 {{ $errors->has('p3.*.mainquestion_sort') ? 'has-error' : '' }}">' +
              '<input type="text" name="p3[' + num + '][mainquestion_sort]" class="form-control form-control-sm" placeholder="Sort">' +
            '</div>' +
            '<div class="col-sm-10 {{ $errors->has('p3.*.mainquestion') ? 'has-error' : '' }}">' +
              '<select id="mainquestion'+ num +'" name="p3[' + num + '][mainquestion]" autocomplete="off">' +
                '<option value=""></option>' +
                @foreach ($MainQuestions as $MainQuestion)
                '<option value="{!! $MainQuestion->main_question !!}">{!! $MainQuestion->main_question !!}</option>' +
                @endforeach
              '</select>' +
            '</div>' +
          '</div>' +
          '<div class="mb-1 {{ $errors->has('p2.*.sectionsub_text') ? 'has-error' : '' }}">' +
            '<textarea id="editor'+ num +'" name="p2[' + num + '][sectionsub_text]"></textarea>' +
          '</div>' +
          '<div class="row mb-1">' +
            '<div style="width: 4%">' +
              '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p3_add">' +
                '<i class="fas fa-plus" aria-hidden="true"></i><br />P3' +
              '</button>' +
            '</div>' +
            '<div class="p3_wrap" style="width: 96%">' +
            '</div>' +
          '</div>' +
        '</div>'
      ); 

      // p3
      $('#sectionsub' + num).select2({
        placeholder: 'Part 2 : Please Select',
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

      $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p2[' + num + '][sectionsub_sort]"]'));
      $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2[' + num + '][sectionsub]"]'));
      $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2[' + num + '][sectionsub_text]"]'));

      $(p2_wrap).on("click",".remove_sectionsub", function(e){
        var sectionsubId = $(this).data('id');
        e.preventDefault();
        var $row = $(this).parent().parent().parent();
        var $option1 = $row.find('[name="p2[' + sectionsubId + '][sectionsub_sort]"]');
        var $option2 = $row.find('[name="p2[' + sectionsubId + '][sectionsub]"]');
        var $option3 = $row.find('[name="p2[' + sectionsubId + '][sectionsub_text]"]');
        $row.remove();

        $('#form').bootstrapValidator('removeField', $option1);
        $('#form').bootstrapValidator('removeField', $option2);
        $('#form').bootstrapValidator('removeField', $option3);
        console.log(num);
      });
    })
  })
})
@endsection