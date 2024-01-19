<style>
  .theme,
  .theme tr,
  .theme td {
    border-collapse: collapse;
    width: 100%;
    font-size: 20px;
    font-family: 'Arial', sans-serif;
  }

  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 10px;
    border-collapse: collapse;
    width: 100%;
  }

  .table td {
    height: 18px;
  }

  .top-row td {
    background-color: #cccccc;
  }

  .overflow {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
  }

  .footer,
  .footer tr,
  .footer td {
    font-size: 11px;
    border-collapse: collapse;
    width: 100%;
  }

  @page {
    margin: 0.7cm;
    size: A4 landscape;
  }

.remark,
.remark tr,
.remark td {
  border-collapse: collapse;
    width: 100%;
    font-size: 12px;
    font-family: 'Arial', sans-serif;
}
</style>
<?php

use Carbon\Carbon;

use App\Models\HumanResources\HROvertime;

$no = 1;
$total_col = 0;
$total_hour = '0';

if ($date_start != NULL && $date_end != NULL) {
  $startDate = Carbon::parse($date_start);
  $endDate = Carbon::parse($date_end);
}
?>

<table class="theme">
  <tr>
    <td align="center">
      Overtime Claim Form {{Carbon::parse($date_start)->format('j')}} - {{Carbon::parse($date_end)->format('j')}} {{Carbon::parse($date_end)->format('F')}} {{Carbon::parse($date_end)->format('Y') }} ({{ $title }} of {{ $month }} {{ $year }}) </td>
  </tr>
</table>

<table height="15px"></table>

<table class="table">
  <tr class="top-row">
    <td align="center" style="width: 20px;">
      NO
    </td>
    <td align="center" style="width: 40px;">
      ID
    </td>
    <td align="center">
      NAME
    </td>
    <td align="center" style="width: 70px;">
      DEPT
    </td>
    @for ($date = $startDate; $date->lte($endDate); $date->addDay())
    <td align="center" style="width: 30px;">
      <?php
      $total_col++;
      $rows[] = $date->format('Y-m-d');
      echo $formattedDate = $date->format('d/m');
      ?>
    </td>
    @endfor
    <td align="center" style="width: 50px;">
      TOTAL<br />HOURS
    </td>
    <td align="center" style="width: 60px;">
      SIGNATURE
    </td>
  </tr>

  @foreach ($overtimes as $overtime)
  <?php $total_hour_per_person = '0'; ?>
  <tr>
    <td align="center">
      {{ $no++ }}
    </td>
    <td align="center">
      {{ $overtime->username }}
    </td>
    <td>
      <div class="overflow" style="max-width: 250px;">
        &nbsp;{{ $overtime->name }}
      </div>
    </td>
    <td>
      <div class="overflow" style="width: 65px">
        &nbsp;{{ $overtime->department }}
      </div>
    </td>
    @foreach ($rows as $row)
    <?php
    $ot = HROvertime::join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
      ->where('hr_overtimes.ot_date', '=', $row)
      ->where('hr_overtimes.staff_id', '=', $overtime->staff_id)
      ->where('hr_overtimes.active', 1)
      ->select('hr_overtimes.assign_staff_id', 'hr_overtime_ranges.total_time')
      ->first();

    $background = "";

    if ($ot) {
      $department_id = $ot->belongstoassignstaff->belongstomanydepartment()->first()->department_id;

      if ($department_id == '14' || $department_id == '15') {
        $background = "background-color: #d9d9d9";
      }
    }
    ?>
    <td align="center" style="<?php echo $background; ?>">
      <?php
      if ($ot) {
        echo $timeString_per_person = (Carbon::parse($ot->total_time))->format('H:i');

        // Explode the time string into an array of hours, minutes, and seconds
        $timeArray_per_person = explode(':', $timeString_per_person);

        // Calculate the total minutes
        $totalMinutes_per_person = ($timeArray_per_person[0] * 60) + $timeArray_per_person[1];
        $total_hour_per_person = $total_hour_per_person + $totalMinutes_per_person;
      }
      ?>
    </td>
    @endforeach
    <td align="right">
      <?php
      $total_hour = $total_hour + $total_hour_per_person;

      echo (sprintf('%02d', intdiv($total_hour_per_person, 60)) . ':' . sprintf('%02d', ($total_hour_per_person % 60)));
      ?>
      &nbsp;
    </td>
    <td></td>
  </tr>
  @endforeach

  <tr>
    <td align="right" colspan="{{ $total_col+4 }}">
      TOTAL HOURS&nbsp;&nbsp;
    </td>
    <td align="right">
      <?php
      echo (sprintf('%02d', intdiv($total_hour, 60)) . ':' . sprintf('%02d', ($total_hour % 60)));
      ?>
      &nbsp;
    </td>
    <td></td>
  </tr>
</table>

<table style="height: 2px;"></table>

<table class="remark">
  <tr>
    <td style="width:24px">
      <div style="width: 16px; height: 16px; background-color: #d9d9d9;"></div>
    </td>
    <td>
      REMARK
    </td>
  </tr>
</table>

<table style="height: 30px;"></table>

<table class="footer">
  <tr>
    <td align="center" style="width: 33%">
      ____________________________________________<br />SUBMITTED BY
    </td>
    <td align="center" style="width: 33%">
      ____________________________________________<br />CHECKED BY PRODUCTION SUPERVISOR
    </td>
    <td align="center" style="width: 33%">
      ____________________________________________<br />APPROVED BY
    </td>
  </tr>
</table>