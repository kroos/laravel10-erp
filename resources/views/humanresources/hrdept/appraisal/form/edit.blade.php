@extends('layouts.app')

@section('content')
<script src="http://tan.com/js/app.js"></script>
<script src="http://tan.com/js/ckeditor/ckeditor.js"></script>
<script src="http://tan.com/js/ckeditor/adapters/jquery.js"></script>

<style>
  .img1 {
    text-align: center;
  }

  p {
    margin: 0;
    padding: 0;
  }

  .tr-td-border,
  .tr-td-border td {
    border: 1px solid black;
  }

  .td-border-left-right {
    border-width: 0px 1px 0px 1px;
    /* top, right, bottom, left */
  }

  .td-border-right {
    border-width: 0px 1px 0px 0px;
    /* top, right, bottom, left */
  }

  .td-border-left-right-bottom {
    border-width: 0px 1px 1px 1px;
    /* top, right, bottom, left */
  }

  .td-border-right-bottom {
    border-width: 0px 1px 1px 0px;
    /* top, right, bottom, left */
  }

  #myButton {
    background: none;
    border: none;
    color: black;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
    text-align: left;
  }

  #myButton:hover {
    color: blue;
  }
</style>

<?php
$pivotappraisal = DB::table('pivot_category_appraisals')
  ->where('id', $id)
  ->first();
$category = App\Models\HumanResources\OptAppraisalCategories::where('id', $pivotappraisal->category_id)->first();
$appraisals = DB::table('pivot_category_appraisals')
  ->where('category_id', $pivotappraisal->category_id)
  ->where('version', $pivotappraisal->version)
  ->orderBy('sort', 'ASC')
  ->orderBy('id', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form : {{ $category->category }} Version {{ $pivotappraisal->version }}</h4>

  <table height="15px"></table>

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

  <table height="15px"></table>

  @foreach ($appraisals as $appraisal)
  <?php
  $sections = App\Models\HumanResources\HRAppraisalSection::where('id', $appraisal->section_id)
    ->orderBy('sort', 'ASC')
    ->orderBy('id', 'ASC')
    ->get();
  ?>

  @foreach ($sections as $section)
  <?php
  $no = 1;
  $section_subs = App\Models\HumanResources\HRAppraisalSectionSub::where('section_id', $section->id)
    ->orderBy('section_id', 'ASC')
    ->orderBy('sort', 'ASC')
    ->orderBy('id', 'ASC')
    ->get();
  ?>



  <!--------------------------------------- 1 --------------------------------------->
  @if (strpos($section->section, '1') !== false)
  <table width="100%">
    <tr>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_text" onclick="myFunction(this)">
          {!! $section->section !!}
        </button>

        <!-- POP UP SECTION -->
        <div class="modal fade" id="section{{ $section->id }}" aria-labelledby="sectionlabel{{ $section->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section, [
            'route' => ['appraisalform.update', $section->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section',
            'data-id' => $section->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionlabel{{ $section->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sort{{ $section->id }}" id="section_sort{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_text') ? 'has-error' : '' }}">
                    <textarea name="section_text{{ $section->id }}" id="section_text{{ $section->id }}" class="form-control form-control-sm">{!! $section->section !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION -->

      </td>
    </tr>
  </table>

  <table width="100%">
    <tr class="tr-td-border">
      <td align="center" style="background-color: #e6e6e6;" width="40px">
        <b>NO</b>
      </td>
      <td align="center" colspan="3" style="background-color: #e6e6e6;">
        <b>PENERANGAN</b>
      </td>
    </tr>

    @foreach ($section_subs as $section_sub)
    <?php
    $no_sub = 'a';
    $main_questions = App\Models\HumanResources\HRAppraisalMainQuestion::where('section_sub_id', $section_sub->id)
      ->orderBy('section_sub_id', 'ASC')
      ->orderBy('mark', 'ASC')
      ->orderBy('sort', 'ASC')
      ->get();
    ?>

    <tr>
      <td align="center" class="td-border-left-right">
        {{ $no }}
      </td>
      <td colspan="3" class="td-border-right">

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section_sub{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="section_sub_text" onclick="myFunction(this)">
          {!! $section_sub->section_sub !!}
        </button>

        <!-- POP UP SECTION SUB -->
        <div class="modal fade" id="section_sub{{ $section_sub->id }}" aria-labelledby="sectionsublabel{{ $section_sub->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section_sub, [
            'route' => ['appraisalform.update', $section_sub->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section_sub',
            'data-id' => $section_sub->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionsublabel{{ $section_sub->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sub_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sub_sort{{ $section_sub->id }}" id="section_sub_sort{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section_sub->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_sub_text') ? 'has-error' : '' }}">
                    <textarea name="section_sub_text{{ $section_sub->id }}" id="section_sub_text{{ $section_sub->id }}" class="form-control form-control-sm">{!! $section_sub->section_sub !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION SUB -->

      </td>
    </tr>

    @foreach ($main_questions as $main_question)
    <?php
    $questions = App\Models\HumanResources\HRAppraisalQuestion::where('main_question_id', $main_question->id)
      ->orderBy('main_question_id', 'ASC')
      ->orderBy('mark', 'ASC')
      ->orderBy('sort', 'ASC')
      ->get();
    ?>

    <tr>
      <td align="center" class="td-border-left-right" style="vertical-align:text-top;">
        {{ $no_sub }})
      </td>
      <td colspan="3" class="td-border-right">

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#main_question{{ $main_question->id }}" data-id="{{ $main_question->id }}" data-name="main_question_text" onclick="myFunction(this)">
          {!! $main_question->main_question !!}
        </button>

        <!-- POP UP MAIN QUESTION -->
        <div class="modal fade" id="main_question{{ $main_question->id }}" aria-labelledby="mainquestionlabel{{ $main_question->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($main_question, [
            'route' => ['appraisalform.update', $main_question->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_main_question',
            'data-id' => $main_question->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="mainquestionlabel{{ $main_question->id }}">Appraisal Form
                  :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('main_question_sort') ? 'has-error' : '' }}">
                    <input type="number" name="main_question_sort{{ $main_question->id }}" id="main_question_sort{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $main_question->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                  <div class="col-sm-2 {{ $errors->has('main_question_mark') ? 'has-error' : '' }}">
                    <input type="number" name="main_question_mark{{ $main_question->id }}" id="main_question_mark{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Mark" value="{{ $main_question->mark }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('main_question_text') ? 'has-error' : '' }}">
                    <textarea name="main_question_text{{ $main_question->id }}" id="main_question_text{{ $main_question->id }}" class="form-control form-control-sm">{!! $main_question->main_question !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP MAIN QUESTION -->

      </td>
    </tr>

    @foreach ($questions as $question)
    <tr>
      <td class="td-border-left-right"></td>
      <td align="center" width="40px" style="vertical-align:text-top;">
        {!! Form::radio('1' . $no . $no_sub, @$value, @$checked, []) !!}
      </td>
      <td width="50px" style="vertical-align:text-top;">
        {!! $question->mark !!}m -
      </td>
      <td class="td-border-right">

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#question{{ $question->id }}" data-id="{{ $question->id }}" data-name="question_text" onclick="myFunction(this)">
          {!! $question->question !!}
        </button>

        <!-- POP UP QUESTION -->
        <div class="modal fade" id="question{{ $question->id }}" aria-labelledby="questionlabel{{ $question->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($question, [
            'route' => ['appraisalform.update', $question->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_question',
            'data-id' => $question->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="questionlabel{{ $question->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('question_sort') ? 'has-error' : '' }}">
                    <input type="number" name="question_sort{{ $question->id }}" id="question_sort{{ $question->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $question->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                  <div class="col-sm-2 {{ $errors->has('question_mark') ? 'has-error' : '' }}">
                    <input type="number" name="question_mark{{ $question->id }}" id="question_mark{{ $question->id }}" class="form-control form-control-sm" placeholder="Mark" value="{{ $question->mark }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('question_text') ? 'has-error' : '' }}">
                    <textarea name="question_text{{ $question->id }}" id="question_text{{ $question->id }}" class="form-control form-control-sm">{!! $question->question !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP QUESTION -->

      </td>
    </tr>
    <tr height="10px">
      <td class="td-border-left-right"></td>
      <td colspan="3" class="td-border-right"></td>
    </tr>
    @endforeach

    <tr>
      <td class="td-border-left-right"></td>
      <td></td>
      <td></td>
      <td class="td-border-right">
        <div class="row mb-3">
          <div style="width: 10%">
            <button type="button" data-bs-toggle="modal" data-bs-target="#question_add{{ $main_question->id }}" data-id="{{ $main_question->id }}" data-name="question_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
              P4 <i class="fas fa-plus" aria-hidden="true"></i>
            </button>
          </div>

          <!-- POP UP QUESTION -->
          <div class="modal fade" id="question_add{{ $main_question->id }}" aria-labelledby="questionlabeladd{{ $main_question->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              {{ Form::open(['route' => ['appraisalform.update', $main_question->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_question_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $main_question->id, 'data-toggle' => 'validator']) }}
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="questionlabeladd{{ $main_question->id }}">Appraisal
                    Form :
                    {{ $category->category }} Version {{ $pivotappraisal->version }}
                  </h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body align-items-start justify-content-center">
                  <div class="row mb-1">
                    <div class="col-sm-2 {{ $errors->has('question_sort_add') ? 'has-error' : '' }}">
                      <input type="number" name="question_sort_add{{ $main_question->id }}" id="question_sort_add{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                    <div class="col-sm-2 {{ $errors->has('question_mark_add') ? 'has-error' : '' }}">
                      <input type="number" name="question_mark_add{{ $main_question->id }}" id="question_mark_add{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                  </div>
                  <div class="row mb-1">
                    <div class="mb-1 {{ $errors->has('question_text_add') ? 'has-error' : '' }}">
                      <textarea name="question_text_add{{ $main_question->id }}" id="question_text_add{{ $main_question->id }}" class="form-control form-control-sm"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
                </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
          <!-- POP UP QUESTION -->
        </div>
      </td>
    </tr>
    <tr height="10px">
      <td class="td-border-left-right"></td>
      <td colspan="3" class="td-border-right"></td>
    </tr>
    <?php $no_sub++; ?>
    @endforeach

    <tr>
      <td class="td-border-left-right"></td>
      <td class="td-border-right" colspan="3">
        <div class="row mb-3">
          <div style="width: 10%">
            <button type="button" data-bs-toggle="modal" data-bs-target="#main_question_add{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="main_question_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
              P3 <i class="fas fa-plus" aria-hidden="true"></i>
            </button>
          </div>

          <!-- POP UP MAIN QUESTION -->
          <div class="modal fade" id="main_question_add{{ $section_sub->id }}" aria-labelledby="mainquestionlabeladd{{ $section_sub->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              {{ Form::open(['route' => ['appraisalform.update', $section_sub->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_main_question_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section_sub->id, 'data-toggle' => 'validator']) }}
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="mainquestionlabeladd{{ $section_sub->id }}">Appraisal
                    Form :
                    {{ $category->category }} Version {{ $pivotappraisal->version }}
                  </h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body align-items-start justify-content-center">
                  <div class="row mb-1">
                    <div class="col-sm-2 {{ $errors->has('main_question_sort_add') ? 'has-error' : '' }}">
                      <input type="number" name="main_question_sort_add{{ $section_sub->id }}" id="main_question_sort_add{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                    <div class="col-sm-2 {{ $errors->has('main_question_mark_add') ? 'has-error' : '' }}">
                      <input type="number" name="main_question_mark_add{{ $section_sub->id }}" id="main_question_mark_add{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                  </div>
                  <div class="row mb-1">
                    <div class="mb-1 {{ $errors->has('main_question_text_add') ? 'has-error' : '' }}">
                      <textarea name="main_question_text_add{{ $section_sub->id }}" id="main_question_text_add{{ $section_sub->id }}" class="form-control form-control-sm"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
                </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
          <!-- POP UP MAIN QUESTION -->
        </div>
      </td>
    </tr>
    <tr>
      <td class="td-border-left-right-bottom"></td>
      <td colspan="3" class="td-border-right-bottom"></td>
    </tr>
    <?php $no++; ?>
    @endforeach
  </table>

  <table height="10px"></table>

  <div class="row mb-3">
    <div style="width: 10%">
      <button type="button" data-bs-toggle="modal" data-bs-target="#section_sub_add{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_sub_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
        P2 <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <!-- POP UP SECTION SUB -->
    <div class="modal fade" id="section_sub_add{{ $section->id }}" aria-labelledby="sectionsublabeladd{{ $section->id }}" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        {{ Form::open(['route' => ['appraisalform.update', $section->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_section_sub_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section->id, 'data-toggle' => 'validator']) }}
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="sectionsublabeladd{{ $section->id }}">Appraisal Form :
              {{ $category->category }} Version {{ $pivotappraisal->version }}
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body align-items-start justify-content-center">
            <div class="row mb-1">
              <div class="col-sm-2 {{ $errors->has('section_sub_sort_add') ? 'has-error' : '' }}">
                <input type="number" name="section_sub_sort_add{{ $section->id }}" id="section_sub_sort_add{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
              </div>
            </div>
            <div class="row mb-1">
              <div class="mb-1 {{ $errors->has('section_sub_text_add') ? 'has-error' : '' }}">
                <textarea name="section_sub_text_add{{ $section->id }}" id="section_sub_text_add{{ $section->id }}" class="form-control form-control-sm"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
          </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
    <!-- POP UP SECTION SUB -->
  </div>
  @endif










  <!--------------------------------------- 2 --------------------------------------->
  @if (strpos($section->section, '2') !== false)
  <table width="100%">
    <tr>
      <td>
        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_text" onclick="myFunction(this)">
          {!! $section->section !!}
        </button>

        <!-- POP UP SECTION -->
        <div class="modal fade" id="section{{ $section->id }}" aria-labelledby="sectionlabel{{ $section->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section, [
            'route' => ['appraisalform.update', $section->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section',
            'data-id' => $section->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionlabel{{ $section->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sort{{ $section->id }}" id="section_sort{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_text') ? 'has-error' : '' }}">
                    <textarea name="section_text{{ $section->id }}" id="section_text{{ $section->id }}" class="form-control form-control-sm">{!! $section->section !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION -->

      </td>
    </tr>
  </table>

  <table width="100%">
    <tr class="tr-td-border">
      <td align="center" rowspan="2" style="background-color: #e6e6e6;" width="40px">
        <b>NO</b>
      </td>
      <td align="center" rowspan="2" style="background-color: #e6e6e6;">
        <b>PENERANGAN</b>
      </td>
      <td align="center" colspan="5" style="background-color: #e6e6e6;">
        <b>MARKAH</b>
      </td>
    </tr>
    <tr class="tr-td-border">
      <td align="center" style="background-color: #e6e6e6;" width="50px">
        <b>1</b>
      </td>
      <td align="center" style="background-color: #e6e6e6;" width="50px">
        <b>2</b>
      </td>
      <td align="center" style="background-color: #e6e6e6;" width="50px">
        <b>3</b>
      </td>
      <td align="center" style="background-color: #e6e6e6;" width="50px">
        <b>4</b>
      </td>
      <td align="center" style="background-color: #e6e6e6;" width="50px">
        <b>5</b>
      </td>
    </tr>

    @foreach ($section_subs as $section_sub)
    <tr class="tr-td-border">
      <td align="center">
        {{ $no }}
      </td>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section_sub{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="section_sub_text" onclick="myFunction(this)">
          {!! $section_sub->section_sub !!}
        </button>

        <!-- POP UP SECTION SUB -->
        <div class="modal fade" id="section_sub{{ $section_sub->id }}" aria-labelledby="sectionsublabel{{ $section_sub->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section_sub, [
            'route' => ['appraisalform.update', $section_sub->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section_sub',
            'data-id' => $section_sub->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionsublabel{{ $section_sub->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sub_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sub_sort{{ $section_sub->id }}" id="section_sub_sort{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section_sub->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_sub_text') ? 'has-error' : '' }}">
                    <textarea name="section_sub_text{{ $section_sub->id }}" id="section_sub_text{{ $section_sub->id }}" class="form-control form-control-sm">{!! $section_sub->section_sub !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION SUB -->

      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '1', @$checked, []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '2', @$checked, []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '3', @$checked, []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '4', @$checked, []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '5', @$checked, []) !!}
      </td>
    </tr>
    <?php $no++; ?>
    @endforeach
  </table>

  <table height="10px"></table>

  <div class="row mb-3">
    <div style="width: 10%">
      <button type="button" data-bs-toggle="modal" data-bs-target="#section_sub_add{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_sub_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
        P2 <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <!-- POP UP SECTION SUB -->
    <div class="modal fade" id="section_sub_add{{ $section->id }}" aria-labelledby="sectionsublabeladd{{ $section->id }}" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        {{ Form::open(['route' => ['appraisalform.update', $section->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_section_sub_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section->id, 'data-toggle' => 'validator']) }}
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="sectionsublabeladd{{ $section->id }}">Appraisal Form :
              {{ $category->category }} Version {{ $pivotappraisal->version }}
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body align-items-start justify-content-center">
            <div class="row mb-1">
              <div class="col-sm-2 {{ $errors->has('section_sub_sort_add') ? 'has-error' : '' }}">
                <input type="number" name="section_sub_sort_add{{ $section->id }}" id="section_sub_sort_add{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
              </div>
            </div>
            <div class="row mb-1">
              <div class="mb-1 {{ $errors->has('section_sub_text_add') ? 'has-error' : '' }}">
                <textarea name="section_sub_text_add{{ $section->id }}" id="section_sub_text_add{{ $section->id }}" class="form-control form-control-sm"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
          </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
    <!-- POP UP SECTION SUB -->
  </div>
  @endif










  <!--------------------------------------- 3 --------------------------------------->
  @if (strpos($section->section, '3') !== false)
  <table width="100%">
    <tr>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_text" onclick="myFunction(this)">
          {!! $section->section !!}
        </button>

        <!-- POP UP SECTION -->
        <div class="modal fade" id="section{{ $section->id }}" aria-labelledby="sectionlabel{{ $section->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section, [
            'route' => ['appraisalform.update', $section->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section',
            'data-id' => $section->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionlabel{{ $section->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sort{{ $section->id }}" id="section_sort{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_text') ? 'has-error' : '' }}">
                    <textarea name="section_text{{ $section->id }}" id="section_text{{ $section->id }}" class="form-control form-control-sm">{!! $section->section !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION -->

      </td>
    </tr>
  </table>

  <table width="100%">
    @foreach ($section_subs as $section_sub)
    <tr>
      <td width="30px">
        {{ $no }})
      </td>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section_sub{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="section_sub_text" onclick="myFunction(this)">
          {!! $section_sub->section_sub !!}
        </button>

        <!-- POP UP SECTION SUB -->
        <div class="modal fade" id="section_sub{{ $section_sub->id }}" aria-labelledby="sectionsublabel{{ $section_sub->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section_sub, [
            'route' => ['appraisalform.update', $section_sub->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section_sub',
            'data-id' => $section_sub->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionsublabel{{ $section_sub->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sub_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sub_sort{{ $section_sub->id }}" id="section_sub_sort{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section_sub->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_sub_text') ? 'has-error' : '' }}">
                    <textarea name="section_sub_text{{ $section_sub->id }}" id="section_sub_text{{ $section_sub->id }}" class="form-control form-control-sm">{!! $section_sub->section_sub !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION SUB -->

      </td>
    </tr>
    <tr>
      <td colspan="2">
        {!! Form::textarea('3' . $no, @$value, ['style' => 'width:100%;', 'rows' => 4]) !!}
      </td>
    </tr>
    <tr height="20px"></tr>
    <?php $no++; ?>
    @endforeach
  </table>

  <div class="row mb-3">
    <div style="width: 10%">
      <button type="button" data-bs-toggle="modal" data-bs-target="#section_sub_add{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_sub_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
        P2 <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <!-- POP UP SECTION SUB -->
    <div class="modal fade" id="section_sub_add{{ $section->id }}" aria-labelledby="sectionsublabeladd{{ $section->id }}" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        {{ Form::open(['route' => ['appraisalform.update', $section->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_section_sub_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section->id, 'data-toggle' => 'validator']) }}
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="sectionsublabeladd{{ $section->id }}">Appraisal Form :
              {{ $category->category }} Version {{ $pivotappraisal->version }}
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body align-items-start justify-content-center">
            <div class="row mb-1">
              <div class="col-sm-2 {{ $errors->has('section_sub_sort_add') ? 'has-error' : '' }}">
                <input type="number" name="section_sub_sort_add{{ $section->id }}" id="section_sub_sort_add{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
              </div>
            </div>
            <div class="row mb-1">
              <div class="mb-1 {{ $errors->has('section_sub_text_add') ? 'has-error' : '' }}">
                <textarea name="section_sub_text_add{{ $section->id }}" id="section_sub_text_add{{ $section->id }}" class="form-control form-control-sm"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
          </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
    <!-- POP UP SECTION SUB -->
  </div>
  @endif










  <!--------------------------------------- 4 --------------------------------------->
  @if (strpos($section->section, '4') !== false)
  <table width="100%">
    <tr>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_text" onclick="myFunction(this)">
          {!! $section->section !!}
        </button>

        <!-- POP UP SECTION -->
        <div class="modal fade" id="section{{ $section->id }}" aria-labelledby="sectionlabel{{ $section->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section, [
            'route' => ['appraisalform.update', $section->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section',
            'data-id' => $section->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionlabel{{ $section->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sort{{ $section->id }}" id="section_sort{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_text') ? 'has-error' : '' }}">
                    <textarea name="section_text{{ $section->id }}" id="section_text{{ $section->id }}" class="form-control form-control-sm">{!! $section->section !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION -->

      </td>
    </tr>
  </table>

  <table width="100%">
    @foreach ($section_subs as $section_sub)
    <?php
    $main_questions = App\Models\HumanResources\HRAppraisalMainQuestion::where('section_sub_id', $section_sub->id)
      ->orderBy('section_sub_id', 'ASC')
      ->orderBy('mark', 'ASC')
      ->orderBy('sort', 'ASC')
      ->get();
    ?>

    <tr>
      <td width="30px">
        {{ $no }})
      </td>
      <td colspan="2">

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#section_sub{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="section_sub_text" onclick="myFunction(this)">
          {!! $section_sub->section_sub !!}
        </button>

        <!-- POP UP SECTION SUB -->
        <div class="modal fade" id="section_sub{{ $section_sub->id }}" aria-labelledby="sectionsublabel{{ $section_sub->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($section_sub, [
            'route' => ['appraisalform.update', $section_sub->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_section_sub',
            'data-id' => $section_sub->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="sectionsublabel{{ $section_sub->id }}">Appraisal Form :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('section_sub_sort') ? 'has-error' : '' }}">
                    <input type="number" name="section_sub_sort{{ $section_sub->id }}" id="section_sub_sort{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $section_sub->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('section_sub_text') ? 'has-error' : '' }}">
                    <textarea name="section_sub_text{{ $section_sub->id }}" id="section_sub_text{{ $section_sub->id }}" class="form-control form-control-sm">{!! $section_sub->section_sub !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP SECTION SUB -->

      </td>
    </tr>

    @foreach ($main_questions as $main_question)
    <tr>
      <td></td>
      <td width="40px">
        {!! Form::radio('4' . $no, @$value, @$checked, []) !!}
      </td>
      <td>

        <button type="button" id="myButton" data-bs-toggle="modal" data-bs-target="#main_question{{ $main_question->id }}" data-id="{{ $main_question->id }}" data-name="main_question_text" onclick="myFunction(this)">
          {!! $main_question->main_question !!}
        </button>

        <!-- POP UP MAIN QUESTION -->
        <div class="modal fade" id="main_question{{ $main_question->id }}" aria-labelledby="mainquestionlabel{{ $main_question->id }}" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            {!! Form::model($main_question, [
            'route' => ['appraisalform.update', $main_question->id],
            'method' => 'PATCH',
            'id' => 'form',
            'autocomplete' => 'off',
            'files' => true,
            'class' => 'form_main_question',
            'data-id' => $main_question->id,
            'data-toggle' => 'validator',
            ]) !!}
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="mainquestionlabel{{ $main_question->id }}">Appraisal Form
                  :
                  {{ $category->category }} Version {{ $pivotappraisal->version }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body align-items-start justify-content-center">
                <div class="row mb-1">
                  <div class="col-sm-2 {{ $errors->has('main_question_sort') ? 'has-error' : '' }}">
                    <input type="number" name="main_question_sort{{ $main_question->id }}" id="main_question_sort{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Sort" value="{{ $main_question->sort }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                  <div class="col-sm-2 {{ $errors->has('main_question_mark') ? 'has-error' : '' }}">
                    <input type="number" name="main_question_mark{{ $main_question->id }}" id="main_question_mark{{ $main_question->id }}" class="form-control form-control-sm" placeholder="Mark" value="{{ $main_question->mark }}" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="mb-1 {{ $errors->has('main_question_text') ? 'has-error' : '' }}">
                    <textarea name="main_question_text{{ $main_question->id }}" id="main_question_text{{ $main_question->id }}" class="form-control form-control-sm">{!! $main_question->main_question !!}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
              </div>
            </div>
            {{ Form::close() }}
          </div>
        </div>
        <!-- POP UP MAIN QUESTION -->

      </td>
    </tr>
    @endforeach

    <tr>
      <td></td>
      <td></td>
      <td>
        <div class="row mb-3">
          <div style="width: 10%">
            <button type="button" data-bs-toggle="modal" data-bs-target="#main_question_add{{ $section_sub->id }}" data-id="{{ $section_sub->id }}" data-name="main_question_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
              P3 <i class="fas fa-plus" aria-hidden="true"></i>
            </button>
          </div>

          <!-- POP UP MAIN QUESTION -->
          <div class="modal fade" id="main_question_add{{ $section_sub->id }}" aria-labelledby="mainquestionlabeladd{{ $section_sub->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              {{ Form::open(['route' => ['appraisalform.update', $section_sub->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_main_question_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section_sub->id, 'data-toggle' => 'validator']) }}
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="mainquestionlabeladd{{ $section_sub->id }}">Appraisal
                    Form :
                    {{ $category->category }} Version {{ $pivotappraisal->version }}
                  </h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body align-items-start justify-content-center">
                  <div class="row mb-1">
                    <div class="col-sm-2 {{ $errors->has('main_question_sort_add') ? 'has-error' : '' }}">
                      <input type="number" name="main_question_sort_add{{ $section_sub->id }}" id="main_question_sort_add{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                    <div class="col-sm-2 {{ $errors->has('main_question_mark_add') ? 'has-error' : '' }}">
                      <input type="number" name="main_question_mark_add{{ $section_sub->id }}" id="main_question_mark_add{{ $section_sub->id }}" class="form-control form-control-sm" placeholder="Mark" oninput="this.value = (this.value < 1) ? 1 : this.value;">
                    </div>
                  </div>
                  <div class="row mb-1">
                    <div class="mb-1 {{ $errors->has('main_question_text_add') ? 'has-error' : '' }}">
                      <textarea name="main_question_text_add{{ $section_sub->id }}" id="main_question_text_add{{ $section_sub->id }}" class="form-control form-control-sm"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
                </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
          <!-- POP UP MAIN QUESTION -->
        </div>
      </td>
    </tr>

    <?php $no++; ?>
    @endforeach
  </table>

  <div class="row mb-3">
    <div style="width: 10%">
      <button type="button" data-bs-toggle="modal" data-bs-target="#section_sub_add{{ $section->id }}" data-id="{{ $section->id }}" data-name="section_sub_text_add" onclick="myFunction(this)" class="col-auto btn btn-sm btn-outline-secondary">
        P2 <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <!-- POP UP SECTION SUB -->
    <div class="modal fade" id="section_sub_add{{ $section->id }}" aria-labelledby="sectionsublabeladd{{ $section->id }}" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        {{ Form::open(['route' => ['appraisalform.update', $section->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_section_sub_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section->id, 'data-toggle' => 'validator']) }}
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="sectionsublabeladd{{ $section->id }}">Appraisal Form :
              {{ $category->category }} Version {{ $pivotappraisal->version }}
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body align-items-start justify-content-center">
            <div class="row mb-1">
              <div class="col-sm-2 {{ $errors->has('section_sub_sort_add') ? 'has-error' : '' }}">
                <input type="number" name="section_sub_sort_add{{ $section->id }}" id="section_sub_sort_add{{ $section->id }}" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
              </div>
            </div>
            <div class="row mb-1">
              <div class="mb-1 {{ $errors->has('section_sub_text_add') ? 'has-error' : '' }}">
                <textarea name="section_sub_text_add{{ $section->id }}" id="section_sub_text_add{{ $section->id }}" class="form-control form-control-sm"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
          </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
    <!-- POP UP SECTION SUB -->
  </div>
  @endif
  @endforeach
  <div style="height: 50px;"></div>
  @endforeach

  <div class="row mb-3">
    <div style="width: 10%">
      <button type="button" data-bs-toggle="modal" data-bs-target="#section_add" class="col-auto btn btn-sm btn-outline-danger">
        P1 <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <!-- POP UP SECTION -->
    <div class="modal fade" id="section_add" aria-labelledby="sectionlabeladd" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        {{ Form::open(['route' => ['appraisalform.update', $section->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form_section_add', 'autocomplete' => 'off', 'files' => true, 'data-id' => $section->id, 'data-toggle' => 'validator']) }}
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="sectionlabeladd">Appraisal Form :
              {{ $category->category }} Version {{ $pivotappraisal->version }}
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body align-items-start justify-content-center">
            <div class="row mb-1">
              <div class="col-sm-2 {{ $errors->has('section_sort_add') ? 'has-error' : '' }}">
                <input type="number" name="section_sort_add" id="section_sort_add" class="form-control form-control-sm" placeholder="Sort" oninput="this.value = (this.value < 1) ? 1 : this.value;">
              </div>
            </div>
            <div class="row mb-1">
              <div class="mb-1 {{ $errors->has('section_text_add') ? 'has-error' : '' }}">
                <textarea name="section_text_add" id="section_text_add" class="form-control form-control-sm"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
          </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
    <!-- POP UP SECTION -->
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      <a href="{{ url()->previous() }}">
        <button class="btn btn-sm btn-outline-secondary">BACK</button>
      </a>
    </div>
  </div>

</div>

<script>
  function myFunction(button) {
    var Id = button.getAttribute('data-id');
    var Name = button.getAttribute('data-name');
    var editor = Name + Id;

    CKEDITOR.replace(editor, {
      toolbar: [{
          name: 'clipboard',
          items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo']
        },
        {
          name: 'basicstyles',
          items: ['Bold', 'Italic', 'Underline', '-']
        },
        {
          name: 'paragraph',
          items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft',
            'JustifyCenter',
            'JustifyRight', 'JustifyBlock'
          ]
        },
        {
          name: 'styles',
          items: ['Styles', 'Format', 'Font', 'FontSize']
        },
      ]
    });
  }

  CKEDITOR.replace('section_text_add', {
    toolbar: [{
        name: 'clipboard',
        items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo']
      },
      {
        name: 'basicstyles',
        items: ['Bold', 'Italic', 'Underline', '-']
      },
      {
        name: 'paragraph',
        items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft',
          'JustifyCenter',
          'JustifyRight', 'JustifyBlock'
        ]
      },
      {
        name: 'styles',
        items: ['Styles', 'Format', 'Font', 'FontSize']
      },
    ]
  });
</script>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// ADD AJAX SECTION
$(".form_section_add").on('submit', function (e) {

var editor = CKEDITOR.instances['section_text_add'];
var section_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
add: 'P1',
sort: $('#section_sort_add').val(),
section: section_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#section_add').modal('hide');
// var row = $('#section_add').parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#section_add').modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// ADD AJAX SECTION SUB
$(".form_section_sub_add").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['section_sub_text_add' + ids];
var section_sub_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
add: 'P2',
id: ids,
sort: $('#section_sub_sort_add' + ids).val(),
section_sub: section_sub_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#section_sub_add' + ids).modal('hide');
// var row = $('#section_sub_add' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#section_sub_add' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// ADD AJAX MAIN QUESTION
$(".form_main_question_add").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['main_question_text_add' + ids];
var main_question_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
add: 'P3',
id: ids,
mark: $('#main_question_mark_add' + ids).val(),
sort: $('#main_question_sort_add' + ids).val(),
main_question: main_question_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#main_question_add' + ids).modal('hide');
// var row = $('#main_question_add' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#main_question_add' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// ADD AJAX QUESTION
$(".form_question_add").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['question_text_add' + ids];
var question_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
add: 'P4',
id: ids,
mark: $('#question_mark_add' + ids).val(),
sort: $('#question_sort_add' + ids).val(),
question: question_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#question_add' + ids).modal('hide');
// var row = $('#question_add' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#question_add' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// EDIT AJAX SECTION
$(".form_section").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['section_text' + ids];
var section_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
update: 'section',
id: ids,
sort: $('#section_sort' + ids).val(),
section: section_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#section' + ids).modal('hide');
// var row = $('#section' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#section' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// EDIT AJAX SECTION SUB
$(".form_section_sub").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['section_sub_text' + ids];
var section_sub_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
update: 'section_sub',
id: ids,
sort: $('#section_sub_sort' + ids).val(),
section_sub: section_sub_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#section_sub' + ids).modal('hide');
// var row = $('#section_sub' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#section_sub' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// EDIT AJAX MAIN QUESTION
$(".form_main_question").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['main_question_text' + ids];
var main_question_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
update: 'main_question',
id: ids,
sort: $('#main_question_sort' + ids).val(),
mark: $('#main_question_mark' + ids).val(),
main_question: main_question_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#main_question' + ids).modal('hide');
// var row = $('#main_question' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#main_question' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});


/////////////////////////////////////////////////////////////////////////////////////////
// EDIT AJAX QUESTION
$(".form_question").on('submit', function (e) {

var ids = $(this).data('id');
var editor = CKEDITOR.instances['question_text' + ids];
var question_text = editor.getData();

e.preventDefault();
$.ajax({
url: '{{ url('appraisalform/update') }}',
type: 'PATCH',
data: {
_token: '{!! csrf_token() !!}',
update: 'question',
id: ids,
sort: $('#question_sort' + ids).val(),
mark: $('#question_mark' + ids).val(),
question: question_text
},
dataType: 'json',
global: false,
async: false,
success: function (response) {
$('#question' + ids).modal('hide');
// var row = $('#question' + ids).parent().parent();
// row.remove();
swal.fire({
title: 'Success!',
text: response.message,
icon: response.status
}).then((result) => {
if (result.isConfirmed) {
location.reload();
}
});
},
error: function (resp) {
const res = resp.responseJSON;
$('#question' + ids).modal('hide');
swal.fire('Error!', res.message, 'error');
}
});
});
@endsection