<style>
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

  @page {
    margin: 0.30cm;
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

<table class="table">
  <tr class="top-row">
    <td align="center" style="width: 17px;">
      NO
    </td>
    <td align="center" style="width: 40px;">
      ID
    </td>
    <td align="center" style="max-width: 100px;">
      NAME
    </td>
    <td align="center" style="max-width: 50px;">
      DEPT
    </td>
    @for ($date = $startDate; $date->lte($endDate); $date->addDay())
    <td style="width: 24px;">
      <?php
      $total_col++;
      $rows[] = $date->format('Y-m-d');
      echo $formattedDate = $date->format('d/m');
      ?>
    </td>
    @endfor
    <td align="center" style="width: 40px;">
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
    <td style="max-width: 100px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
      &nbsp;{{ $overtime->name }}
    </td>
    <td style="max-width: 50px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
      &nbsp;{{ $overtime->department }}
    </td>
    @foreach ($rows as $row)
    <td style="width: 24px;">
      <?php
      $ot = HROvertime::join('hr_overtime_ranges', 'hr_overtime_ranges.id', '=', 'hr_overtimes.overtime_range_id')
        ->where('hr_overtimes.ot_date', '=', $row)
        ->where('hr_overtimes.staff_id', '=', $overtime->staff_id)
        ->where('hr_overtimes.active', 1)
        ->select('hr_overtime_ranges.total_time')
        ->first();

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