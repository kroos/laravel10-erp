@extends('layouts.app')

@section('content')
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

  <input type="hidden" name="department_id" value="{{ $department->id }}">

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
var p1_num = 0;
var p2_num = 0;
var p3_num = 0;
var p4_num = 0;
var p1_add	= $(".p1_add");
var p1_wrap	= $(".p1_wrap");

$(p1_add).click(function(){
  num++;
  p1_num++;

  p1_wrap.append(
    '<div class="section">' +
      '<input type="hidden" name="p1_end" value="'+p1_num+'">' +
      '<div class="row mb-1">' +
        '<div class="col-sm-1">' +
          '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_section" data-id="'+num+'">' +
            '<i class="fas fa-trash" aria-hidden="true"></i>' +
          '</button>' +
        '</div>' +
        '<div class="col-sm-1 {{ $errors->has('p1.*.section_sort') ? 'has-error' : '' }}">' +
          '<input type="text" name="p1'+p1_num+'['+num+'][section_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value|=0">' +
        '</div>' +
        '<div class="col-sm-10 {{ $errors->has('p1.*.section') ? 'has-error' : '' }}">' +
          '<select id="section'+num+'" name="p1'+p1_num+'['+num+'][section]" autocomplete="off">' +
            '<option value=""></option>' +
            @foreach ($Sections as $Section)
            '<option value="{!! $Section->section !!}">{!! $Section->section !!}</option>' +
            @endforeach
          '</select>' +
        '</div>' +
      '</div>' +
      '<div class="mb-1 {{ $errors->has('p1.*.section_text') ? 'has-error' : '' }}">' +
        '<textarea id="editor'+num+'" name="p1'+p1_num+'['+num+'][section_text]"></textarea>' +
      '</div>' +
      '<div class="row mb-5">' +
        '<div style="width: 4%">' +
          '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p2_add'+num+'" data-id="'+p1_num+'">' +
            '<i class="fas fa-plus" aria-hidden="true"></i><br />P2' +
          '</button>' +
        '</div>' +
        '<div class="p2_wrap'+num+'" style="width: 96%">' +
        '</div>' +
      '</div>' +
    '</div>'
  ); 

  // p1
  $('#section'+num).select2({
    placeholder: 'Part 1 : Please Select',
    width: '100%',
    allowClear: true,
    closeOnSelect: true,
  });

  CKEDITOR.replace('editor'+num, {
    toolbar: [
      { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
      { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
      { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
      { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
    ]
  });

  $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1'+p1_num+'['+num+'][section_sort]"]'));
  $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1'+p1_num+'['+num+'][section]"]'));
	$('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1'+p1_num+'['+num+'][section_text]"]'));

  $(p1_wrap).on("click",".remove_section", function(e){
    var sectionId = $(this).data('id');
    e.preventDefault();
    var $row = $(this).parent().parent().parent();
    var $option1 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_sort]"]');
    var $option2 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section]"]');
    var $option3 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_text]"]');
    var $option4 = $row.find('[name="p1_end"]');
    $row.remove();

    $('#form').bootstrapValidator('removeField', $option1);
    $('#form').bootstrapValidator('removeField', $option2);
    $('#form').bootstrapValidator('removeField', $option3);
    console.log(num);
  });


  /////////////////////////////////////////////////////////////////////////////////////
  // p2
  var p2_add	= $(".p2_add"+num);
  var p2_wrap	= $(".p2_wrap"+num);

  $(p2_add).click(function(){
    num++;
    p2_num++;
    var p1_end = $(this).data('id');

    p2_wrap.append(
      '<div class="sectionsub">' +
        '<input type="hidden" name="p2_end" value="'+p2_num+'">' +
        '<div class="row mb-1">' +
          '<div class="col-sm-1">' +
            '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_sectionsub" data-id="'+num+'">' +
              '<i class="fas fa-trash" aria-hidden="true"></i>' +
            '</button>' +
          '</div>' +
          '<div class="col-sm-1 {{ $errors->has('p2.*.sectionsub_sort') ? 'has-error' : '' }}">' +
            '<input type="text" name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value|=0">' +
          '</div>' +
          '<div class="col-sm-10 {{ $errors->has('p2.*.sectionsub') ? 'has-error' : '' }}">' +
            '<select id="sectionsub'+num+'" name="p2'+p1_end+p2_num+'['+num+'][sectionsub]" autocomplete="off">' +
              '<option value=""></option>' +
              @foreach ($SectionSubs as $SectionSub)
              '<option value="{!! $SectionSub->section_sub !!}">{!! $SectionSub->section_sub !!}</option>' +
              @endforeach
            '</select>' +
          '</div>' +
        '</div>' +
        '<div class="mb-1 {{ $errors->has('p2.*.sectionsub_text') ? 'has-error' : '' }}">' +
          '<textarea id="editor'+num+'" name="p2'+p1_end+p2_num+'['+num+'][sectionsub_text]"></textarea>' +
        '</div>' +
        '<div class="row mb-1">' +
          '<div style="width: 4%">' +
            '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p3_add'+num+'" data-id="'+p2_num+'">' +
              '<i class="fas fa-plus" aria-hidden="true"></i><br />P3' +
            '</button>' +
          '</div>' +
          '<div class="p3_wrap'+num+'" style="width: 96%">' +
          '</div>' +
        '</div>' +
      '</div>'
    ); 

    // p2
    $('#sectionsub'+num).select2({
      placeholder: 'Part 2 : Please Select',
      width: '100%',
      allowClear: true,
      closeOnSelect: true,
    });

    CKEDITOR.replace('editor'+num, {
      toolbar: [
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
      ]
    });

    $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]"]'));
    $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub]"]'));
		$('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_text]"]'));

    $(p2_wrap).on("click",".remove_sectionsub", function(e){
      var sectionsubId = $(this).data('id');
      e.preventDefault();
      var $row = $(this).parent().parent().parent();
      var $option1 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_sort]"]');
      var $option2 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub]"]');
      var $option3 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_text]"]');
      var $option4 = $row.find('[name="p2_end"]');
      $row.remove();

      $('#form').bootstrapValidator('removeField', $option1);
      $('#form').bootstrapValidator('removeField', $option2);
      $('#form').bootstrapValidator('removeField', $option3);
      console.log(num);
    });

    
    /////////////////////////////////////////////////////////////////////////////////////
    // p3
    var p3_add	= $(".p3_add"+num);
    var p3_wrap	= $(".p3_wrap"+num);

    $(p3_add).click(function(){
      num++;
      p3_num++;
      var p2_end = $(this).data('id');

      p3_wrap.append(
        '<div class="mainquestion">' +
          '<input type="hidden" name="p3_end" value="'+p3_num+'">' +
          '<div class="row mb-1">' +
            '<div class="col-sm-1">' +
              '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_mainquestion" data-id="'+num+'">' +
                '<i class="fas fa-trash" aria-hidden="true"></i>' +
              '</button>' +
            '</div>' +
            '<div class="col-sm-1 {{ $errors->has('p3.*.mainquestion_sort') ? 'has-error' : '' }}">' +
              '<input type="text" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value|=0">' +
            '</div>' +
            '<div class="col-sm-1 {{ $errors->has('p3.*.mainquestion_mark') ? 'has-error' : '' }}">' +
              '<input type="text" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value|=0">' +
            '</div>' +
            '<div class="col-sm-9 {{ $errors->has('p3.*.mainquestion') ? 'has-error' : '' }}">' +
              '<select id="mainquestion'+num+'" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion]" autocomplete="off">' +
                '<option value=""></option>' +
                @foreach ($MainQuestions as $MainQuestion)
                '<option value="{!! $MainQuestion->main_question !!}">{!! $MainQuestion->main_question !!}</option>' +
                @endforeach
              '</select>' +
            '</div>' +
          '</div>' +
          '<div class="mb-1 {{ $errors->has('p3.*.mainquestion_text') ? 'has-error' : '' }}">' +
            '<textarea id="editor'+num+'" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_text]"></textarea>' +
          '</div>' +
          '<div class="row mb-1">' +
            '<div style="width: 4%">' +
              '<button type="button" class="col-auto btn btn-sm btn-outline-secondary p4_add'+num+'" data-id="'+p3_num+'">' +
                '<i class="fas fa-plus" aria-hidden="true"></i><br />P4' +
              '</button>' +
            '</div>' +
            '<div class="p4_wrap'+num+'" style="width: 96%">' +
            '</div>' +
          '</div>' +
        '</div>'
      );

      // p3
      $('#mainquestion'+num).select2({
        placeholder: 'Part 3 : Please Select',
        width: '100%',
        allowClear: true,
        closeOnSelect: true,
      });

      CKEDITOR.replace('editor'+num, {
        toolbar: [
          { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
          { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
          { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
          { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
        ]
      });

      $('#form').bootstrapValidator('addField',	$('.mainquestion')	.find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_sort]"]'));
      $('#form').bootstrapValidator('addField',	$('.mainquestion')	.find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_mark]"]'));
      $('#form').bootstrapValidator('addField',	$('.mainquestion')	.find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion]"]'));
      $('#form').bootstrapValidator('addField',	$('.mainquestion')	.find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_text]"]'));

      $(p3_wrap).on("click",".remove_mainquestion", function(e){
        var mainquestionId = $(this).data('id');
        e.preventDefault();
        var $row = $(this).parent().parent().parent();
        var $option1 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_sort]"]');
        var $option2 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_mark]"]');
        var $option3 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion]"]');
        var $option4 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_text]"]');
        var $option5 = $row.find('[name="p3_end"]');
        $row.remove();

        $('#form').bootstrapValidator('removeField', $option1);
        $('#form').bootstrapValidator('removeField', $option2);
        $('#form').bootstrapValidator('removeField', $option3);
        $('#form').bootstrapValidator('removeField', $option4);
        console.log(num);
      });


      /////////////////////////////////////////////////////////////////////////////////////
      // p4
      var p4_add	= $(".p4_add"+num);
      var p4_wrap	= $(".p4_wrap"+num);

      $(p4_add).click(function(){
        num++;
        p4_num++;
        var p3_end = $(this).data('id');

        p4_wrap.append(
          '<div class="question">' +
            '<input type="hidden" name="p4_end" value="'+p4_num+'">' +
            '<div class="row mb-1">' +
              '<div class="col-sm-1">' +
                '<button type="button" class="col-sm-12 text-danger btn btn-sm btn-outline-secondary remove_question" data-id="'+num+'">' +
                  '<i class="fas fa-trash" aria-hidden="true"></i>' +
                '</button>' +
              '</div>' +
              '<div class="col-sm-1 {{ $errors->has('p4.*.question_sort') ? 'has-error' : '' }}">' +
                '<input type="text" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value|=0">' +
              '</div>' +
              '<div class="col-sm-1 {{ $errors->has('p4.*.question_mark') ? 'has-error' : '' }}">' +
                '<input type="text" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value|=0">' +
              '</div>' +
              '<div class="col-sm-9 {{ $errors->has('p4.*.question') ? 'has-error' : '' }}">' +
                '<select id="question'+num+'" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question]" autocomplete="off">' +
                  '<option value=""></option>' +
                  @foreach ($Questions as $Question)
                  '<option value="{!! $Question->question !!}">{!! $Question->question !!}</option>' +
                  @endforeach
                '</select>' +
              '</div>' +
            '</div>' +
            '<div class="mb-1 {{ $errors->has('p4.*.question_text') ? 'has-error' : '' }}">' +
              '<textarea id="editor'+num+'" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"></textarea>' +
            '</div>' +
          '</div>'
        );

        // p4
        $('#question'+num).select2({
          placeholder: 'Part 4 : Please Select',
          width: '100%',
          allowClear: true,
          closeOnSelect: true,
        });

        CKEDITOR.replace('editor'+num, {
          toolbar: [
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
          ]
        });

        $('#form').bootstrapValidator('addField',	$('.question')	.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_sort]"]'));
        $('#form').bootstrapValidator('addField',	$('.question')	.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_mark]"]'));
        $('#form').bootstrapValidator('addField',	$('.question')	.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question]"]'));
        $('#form').bootstrapValidator('addField',	$('.question')	.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"]'));

        $(p4_wrap).on("click",".remove_question", function(e){
          var questionId = $(this).data('id');
          e.preventDefault();
          var $row = $(this).parent().parent().parent();
          var $option1 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_sort]"]');
          var $option2 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_mark]"]');
          var $option3 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question]"]');
          var $option4 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_text]"]');
          var $option5 = $row.find('[name="p4_end"]');
          $row.remove();

          $('#form').bootstrapValidator('removeField', $option1);
          $('#form').bootstrapValidator('removeField', $option2);
          $('#form').bootstrapValidator('removeField', $option3);
          $('#form').bootstrapValidator('removeField', $option4);
          console.log(num);
        });
      })
    })
  })
})
@endsection