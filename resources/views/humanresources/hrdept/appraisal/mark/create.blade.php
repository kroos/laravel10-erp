@extends('layouts.app')

@section('content')
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
</style>

<?php

use \Carbon\Carbon;
use App\Models\Staff;
use App\Models\HumanResources\HRAppraisalMark;

$staff = Staff::join('pivot_apoint_appraisals', 'pivot_apoint_appraisals.evaluatee_id', '=', 'staffs.id')
  ->join('logins', 'logins.staff_id', '=', 'staffs.id')
  ->where('pivot_apoint_appraisals.id', $id)
  ->select('staffs.id as staffid', 'staffs.appraisal_category_id as catid', 'staffs.*', 'logins.*',  'pivot_apoint_appraisals.*')
  ->first();

$pivotappraisal = DB::table('pivot_category_appraisals')
  ->join('option_appraisal_categories', 'option_appraisal_categories.id', '=', 'pivot_category_appraisals.category_id')
  ->where('pivot_category_appraisals.category_id', $staff->catid)
  ->orderBy('version', 'DESC')
  ->first();

$appraisals = DB::table('pivot_category_appraisals')
  ->where('category_id', $pivotappraisal->category_id)
  ->where('version', $pivotappraisal->version)
  ->orderBy('sort', 'ASC')
  ->orderBy('id', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>BORANG PENILAIAN PRESTASI PEKERJA<br />{{ $pivotappraisal->category }} Version {{ $pivotappraisal->version }}</h4>

  <br>

  <table width="100%">
    <tr>
      <td>No Pekerja</td>
      <td>:</td>
      <td>{{ $staff->username }}</td>
      <td>Bahagian</td>
      <td>:</td>
      <td>{{ Staff::find($staff->staffid)->belongstomanydepartment()->where('main', 1)->first()->department; }}</td>
    </tr>
    <tr>
      <td width="150px">Nama Pekerja</td>
      <td width="20px">:</td>
      <td width="600px">{{ $staff->name }}</td>
      <td width="150px">Tarikh Masuk</td>
      <td width="20px">:</td>
      <td>{{ Carbon::parse($staff->join)->format('d-m-Y'); }}</td>
    </tr>
    <tr>
      <td>Tarikh</td>
      <td>:</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>

  <br>

  {{ Form::open(['route' => ['appraisalmark.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <input type="hidden" name="pivot_apoint_id" value="{{ $id }}">

  @foreach ($appraisals as $appraisal)
  <?php
  $sections = App\Models\HumanResources\HRAppraisalSection::where('id', $appraisal->section_id)->get();
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
  <input type="hidden" name="section1" value="{{ $section->id }}">

  <table width="100%">
    <tr>
      <td>
        {!! $section->section !!}
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
        {!! $section_sub->section_sub !!}
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
        {!! $main_question->main_question !!}
      </td>
    </tr>

    @foreach ($questions as $question)
    <?php
    $loop1 = HRAppraisalMark::where('question_id', $question->id)->first();
    ?>

    <input type="hidden" name="arraymark1[]" value="{{ '1' . $no . $no_sub }}">

    <tr>
      <td class="td-border-left-right"></td>
      <td align="center" width="40px" style="vertical-align:text-top;">
        {!! Form::radio('1' . $no . $no_sub, $question->id, (($loop1->question_id ?? NULL) == $question->id), []) !!}
      </td>
      <td width="50px" style="vertical-align:text-top;">
        {!! $question->mark !!}m -
      </td>
      <td class="td-border-right">
        {!! $question->question !!}
      </td>
    </tr>
    <tr height="10px">
      <td class="td-border-left-right"></td>
      <td colspan="3" class="td-border-right"></td>
    </tr>
    @endforeach
    <tr height="10px">
      <td class="td-border-left-right"></td>
      <td colspan="3" class="td-border-right"></td>
    </tr>
    <?php $no_sub++; ?>
    @endforeach
    <tr>
      <td class="td-border-left-right-bottom"></td>
      <td colspan="3" class="td-border-right-bottom"></td>
    </tr>
    <?php $no++; ?>
    @endforeach
  </table>
  @endif



  <!--------------------------------------- 2 --------------------------------------->
  @if (strpos($section->section, '2') !== false)
  <input type="hidden" name="section2" value="{{ $section->id }}">

  <table width="100%">
    <tr>
      <td>
        {!! $section->section !!}
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
    <?php
    $loop2 = HRAppraisalMark::where('section_sub_id', $section_sub->id)->first();
    ?>

    <input type="hidden" name="arrayid2[]" value="{{ 'id2' . $no }}">
    <input type="hidden" name="{{ 'id2' . $no }}" value="{{ $section_sub->id }}">
    <input type="hidden" name="arraymark2[]" value="{{ '2' . $no }}">

    <tr class="tr-td-border">
      <td align="center">
        {{ $no }}
      </td>
      <td>
        {!! $section_sub->section_sub !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '1', (($loop2->mark ?? NULL) == '1'), []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '2', (($loop2->mark ?? NULL) == '2'), []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '3', (($loop2->mark ?? NULL) == '3'), []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '4', (($loop2->mark ?? NULL) == '4'), []) !!}
      </td>
      <td align="center">
        {!! Form::radio('2' . $no, '5', (($loop2->mark ?? NULL) == '5'), []) !!}
      </td>
    </tr>
    <?php $no++; ?>
    @endforeach
  </table>
  @endif



  <!--------------------------------------- 3 --------------------------------------->
  @if (strpos($section->section, '3') !== false)
  <input type="hidden" name="section3" value="{{ $section->id }}">

  <table width="100%">
    <tr>
      <td>
        {!! $section->section !!}
      </td>
    </tr>
  </table>

  <table width="100%">
    @foreach ($section_subs as $section_sub)
    <?php
    $loop3 = HRAppraisalMark::where('section_sub_id', $section_sub->id)->first();
    ?>

    <input type="hidden" name="arrayid3[]" value="{{ 'id3' . $no }}">
    <input type="hidden" name="{{ 'id3' . $no }}" value="{{ $section_sub->id }}">
    <input type="hidden" name="arraymark3[]" value="{{ '3' . $no }}">

    <tr>
      <td width="30px">
        {{ $no }})
      </td>
      <td>
        {!! $section_sub->section_sub !!}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        {!! Form::textarea('3' . $no, $loop3->remark ?? NULL, ['style' => 'width:100%;', 'rows' => 4]) !!}
      </td>
    </tr>
    <tr height="20px"></tr>
    <?php $no++; ?>
    @endforeach
  </table>
  @endif



  <!--------------------------------------- 4 --------------------------------------->
  @if (strpos($section->section, '4') !== false)
  <input type="hidden" name="section4" value="{{ $section->id }}">

  <table width="100%">
    <tr>
      <td>
        {!! $section->section !!}
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
        {!! $section_sub->section_sub !!}
      </td>
    </tr>

    @foreach ($main_questions as $main_question)
    <?php
    $loop4 = HRAppraisalMark::where('main_question_id', $main_question->id)->first();
    ?>

    <input type="hidden" name="arraymark4[]" value="{{ '4' . $no }}">

    <tr>
      <td></td>
      <td width="40px">
        {!! Form::radio('4' . $no, $main_question->id, (($loop4->main_question_id ?? NULL) == $main_question->id), []) !!}
      </td>
      <td>
        {!! $main_question->main_question !!}
      </td>
    </tr>
    @endforeach
    <?php $no++; ?>
    @endforeach
  </table>
  @endif
  @endforeach
  <div style="height: 50px;"></div>
  @endforeach

  <div class="row">
    <div class="text-center">
      <input type="hidden" name="id" id="id" value="{{ $id }}">

      <input type="submit" class="btn btn-sm btn-outline-secondary" value="SUBMIT">
    </div>
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
@endsection