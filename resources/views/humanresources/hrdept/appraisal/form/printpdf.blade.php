<style>
  @page {
    size: A4;
    /* or letter, legal, etc. */
    margin: 0.5cm;
    /* You can use different units like px, in, mm, etc. */
  }

  .container {
    font-family: "Helvetica", "Arial", sans-serif;
    font-size: 12px;
  }

  table,
  tr,
  td {
    border-collapse: collapse;
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
    border-left: 1px solid black;
    border-right: 1px solid black;
  }

  .td-border-right {
    border-right: 1px solid black;
  }

  .td-border-left-right-bottom {
    border-right: 1px solid black;
    border-bottom: 1px solid black;
    border-left: 1px solid black;
  }

  .td-border-right-bottom {
    border-right: 1px solid black;
    border-bottom: 1px solid black;
  }

  input[type="button"] {
    background-color: transparent;
    border: 1px solid black;
    color: none;
    padding: 5px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 0px;
    margin: 0px;
    border-radius: 50%;
  }

  .page-break-after {
    page-break-after: auto;
  }

  .avoid-page-break {
    page-break-inside: avoid;
  }
</style>

<?php
$pivotappraisal = DB::table('pivot_dept_appraisals')
    ->where('id', $id)
    ->first();
$department = App\Models\HumanResources\DepartmentPivot::where('id', $pivotappraisal->department_id)->first();
$appraisals = DB::table('pivot_dept_appraisals')
    ->where('department_id', $pivotappraisal->department_id)
    ->where('version', $pivotappraisal->version)
    ->orderBy('sort', 'ASC')
    ->orderBy('id', 'ASC')
    ->get();
?>

<div class="container">

  <h4>Appraisal Form : {{ $department->department }} Version {{ $pivotappraisal->version }}</h4>

  <table height="15px"></table>


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
        <table width="100%">
          <tr>
            <td>
              {!! $section->section !!}
            </td>
          </tr>
        </table>

        <table width="100%">
          <tr class="tr-td-border">
            <td align="center" style="background-color: #e6e6e6;" width="30px">
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
                <tr>
                  <td class="td-border-left-right"></td>
                  <td align="center" width="20px" style="vertical-align:text-top;">
                    <input type="button">
                  </td>
                  <td width="30px" style="vertical-align:text-top;">
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
        <div class="avoid-page-break">
          <table width="100%">
            <tr>
              <td>
                {!! $section->section !!}
              </td>
            </tr>
          </table>

          <table width="100%">
            <tr class="tr-td-border">
              <td align="center" rowspan="2" style="background-color: #e6e6e6;" width="30px">
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
              <td align="center" style="background-color: #e6e6e6;" width="30px">
                <b>1</b>
              </td>
              <td align="center" style="background-color: #e6e6e6;" width="30px">
                <b>2</b>
              </td>
              <td align="center" style="background-color: #e6e6e6;" width="30px">
                <b>3</b>
              </td>
              <td align="center" style="background-color: #e6e6e6;" width="30px">
                <b>4</b>
              </td>
              <td align="center" style="background-color: #e6e6e6;" width="30px">
                <b>5</b>
              </td>
            </tr>

            @foreach ($section_subs as $section_sub)
              <tr class="tr-td-border">
                <td align="center">
                  {{ $no }}
                </td>
                <td>
                  {!! $section_sub->section_sub !!}
                </td>
                <td align="center">
                  <input type="button">
                </td>
                <td align="center">
                  <input type="button">
                </td>
                <td align="center">
                  <input type="button">
                </td>
                <td align="center">
                  <input type="button">
                </td>
                <td align="center">
                  <input type="button">
                </td>
              </tr>
              <?php $no++; ?>
            @endforeach
          </table>
        </div>
      @endif



      <!--------------------------------------- 3 --------------------------------------->
      @if (strpos($section->section, '3') !== false)
        <div class="avoid-page-break">
          <table width="100%">
            <tr>
              <td>
                {!! $section->section !!}
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
                  {!! $section_sub->section_sub !!}
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  {!! Form::textarea('3' . $no, @$value, ['style' => 'width:99%;', 'rows' => 4]) !!}
                </td>
              </tr>
              <tr>
                <td height="15px"></td>
              </tr>
              <?php $no++; ?>
            @endforeach
          </table>
        </div>
      @endif



      <!--------------------------------------- 4 --------------------------------------->
      @if (strpos($section->section, '4') !== false)
        <div class="avoid-page-break">
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
                <tr>
                  <td></td>
                  <td width="20px">
                    <input type="button">
                  </td>
                  <td>
                    {!! $main_question->main_question !!}
                  </td>
                </tr>
              @endforeach
              <?php $no++; ?>
            @endforeach
          </table>
        </div>
      @endif
    @endforeach
    <div style="height: 30px;"></div>
  @endforeach

</div>
