@extends('layouts.app')

@section('content')
<style>
  .img1 {
    text-align: center;
    /* border: 1px solid black; */
  }

  /* div {
    border: 1px solid black;
  } */
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form : {{ $category->category }}</h4>

  <table height="15px"></table>

  {{ Form::open(['route' => ['appraisalform.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <input type="hidden" name="category_id" value="{{ $category->id }}">



  <div class="row">
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 1</b>
      <img src="{{ asset('images/appraisal/Bahagian1.jpg') }}" width="620px">
    </div>
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 2</b>
      <img src="{{ asset('images/appraisal/Bahagian2.jpg') }}" width="620px">
    </div>
  </div>


  <div class="row">
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 3</b>
      <img src="{{ asset('images/appraisal/Bahagian3.jpg') }}" width="620px">
    </div>
    <div class="col-sm-6 img1 mb-3">
      <b>SAMPLE BAHAGIAN 4</b>
      <img src="{{ asset('images/appraisal/Bahagian4.jpg') }}" width="620px">
    </div>
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
          '<input type="number" name="p1'+p1_num+'['+num+'][section_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
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

  CKEDITOR.replace('editor'+num, {
    toolbar: [
      { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
      { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
      { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
      { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
    ]
  });

  $('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1'+p1_num+'['+num+'][section_sort]"]'));
	$('#form').bootstrapValidator('addField',	$('.section')	.find('[name="p1'+p1_num+'['+num+'][section_text]"]'));

  $(p1_wrap).on("click",".remove_section", function(e){
    var sectionId = $(this).data('id');
    e.preventDefault();
    var $row = $(this).parent().parent().parent();
    var $option1 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_sort]"]');
    var $option2 = $row.find('[name="p1'+p1_num+'['+sectionId+'][section_text]"]');
    var $option3 = $row.find('[name="p1_end"]');
    $row.remove();

    $('#form').bootstrapValidator('removeField', $option1);
    $('#form').bootstrapValidator('removeField', $option2);
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
            '<input type="number" name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
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

    CKEDITOR.replace('editor'+num, {
      toolbar: [
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
      ]
    });

    $('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_sort]"]'));
		$('#form').bootstrapValidator('addField',	$('.sectionsub')	.find('[name="p2'+p1_end+p2_num+'['+num+'][sectionsub_text]"]'));

    $(p2_wrap).on("click",".remove_sectionsub", function(e){
      var sectionsubId = $(this).data('id');
      e.preventDefault();
      var $row = $(this).parent().parent().parent();
      var $option1 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_sort]"]');
      var $option2 = $row.find('[name="p2'+p1_end+p2_num+'['+sectionsubId+'][sectionsub_text]"]');
      var $option3 = $row.find('[name="p2_end"]');
      $row.remove();

      $('#form').bootstrapValidator('removeField', $option1);
      $('#form').bootstrapValidator('removeField', $option2);
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
              '<input type="number" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
            '</div>' +
            '<div class="col-sm-1 {{ $errors->has('p3.*.mainquestion_mark') ? 'has-error' : '' }}">' +
              '<input type="number" name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
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
      $('#form').bootstrapValidator('addField',	$('.mainquestion')	.find('[name="p3'+p1_end+p2_end+p3_num+'['+num+'][mainquestion_text]"]'));

      $(p3_wrap).on("click",".remove_mainquestion", function(e){
        var mainquestionId = $(this).data('id');
        e.preventDefault();
        var $row = $(this).parent().parent().parent();
        var $option1 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_sort]"]');
        var $option2 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_mark]"]');
        var $option3 = $row.find('[name="p3'+p1_end+p2_end+p3_num+'['+mainquestionId+'][mainquestion_text]"]');
        var $option4 = $row.find('[name="p3_end"]');
        $row.remove();

        $('#form').bootstrapValidator('removeField', $option1);
        $('#form').bootstrapValidator('removeField', $option2);
        $('#form').bootstrapValidator('removeField', $option3);
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
                '<input type="number" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_sort]" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
              '</div>' +
              '<div class="col-sm-1 {{ $errors->has('p4.*.question_mark') ? 'has-error' : '' }}">' +
                '<input type="number" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_mark]" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">' +
              '</div>' +
            '</div>' +
            '<div class="mb-1 {{ $errors->has('p4.*.question_text') ? 'has-error' : '' }}">' +
              '<textarea id="editor'+num+'" name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"></textarea>' +
            '</div>' +
          '</div>'
        );

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
        $('#form').bootstrapValidator('addField',	$('.question')	.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+num+'][question_text]"]'));

        $(p4_wrap).on("click",".remove_question", function(e){
          var questionId = $(this).data('id');
          e.preventDefault();
          var $row = $(this).parent().parent().parent();
          var $option1 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_sort]"]');
          var $option2 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_mark]"]');
          var $option3 = $row.find('[name="p4'+p1_end+p2_end+p3_end+p4_num+'['+questionId+'][question_text]"]');
          var $option4 = $row.find('[name="p4_end"]');
          $row.remove();

          $('#form').bootstrapValidator('removeField', $option1);
          $('#form').bootstrapValidator('removeField', $option2);
          $('#form').bootstrapValidator('removeField', $option3);
          console.log(num);
        });
      })
    })
  })
})
@endsection