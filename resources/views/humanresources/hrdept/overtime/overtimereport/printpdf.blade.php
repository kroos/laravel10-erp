<style>
  .table,
  .table tr,
  .table td {
    border: 1px solid black;
    font-size: 14px;
    border-collapse: collapse;
  }

  .top-row td {
    background-color: #cccccc;
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

<table class="table table-hover table-sm align-middle">
  <tr class="top-row">
    <td class="text-center" style="width: 30px;">
      NO
    </td>
    <td class="text-center" style="width: 55px;">
      ID
    </td>
    <td class="text-center" style="max-width: 150px;">
      NAME
    </td>
    <td class="text-center">
      DEPARTMENT
    </td>
    @for ($date = $startDate; $date->lte($endDate); $date->addDay())
    <td class="text-center" style="max-width: 48px;">
      <?php
      $total_col++;
      $rows[] = $date->format('Y-m-d');
      echo $formattedDate = $date->format('d/m');
      ?>
    </td>
    @endfor
    <td class="text-center" style="max-width: 60px;">
      TOTAL<br />HOURS
    </td>
    <td class="text-center" style="max-width: 70px;">
      SIGNATURE
    </td>
  </tr>

  @foreach ($overtimes as $overtime)
  <?php $total_hour_per_person = '0'; ?>
  <tr>
    <td class="text-truncate text-center" style="width: 30px;">
      {{ $no++ }}
    </td>
    <td class="text-truncate text-center" style="width: 55px;" title="{{ $overtime->username }}">
      {{ $overtime->username }}
    </td>
    <td class="text-truncate" style="max-width: 150px;" title="{{ $overtime->name }}">
      {{ $overtime->name }}
    </td>
    <td class="text-truncate" style="max-width: 1px;" title="{{ $overtime->department }}">
      {{ $overtime->department }}
    </td>
    @foreach ($rows as $row)
    <td class="text-truncate text-center" style="max-width: 48px;">
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
    <td class="text-center" style="max-width: 60px;">
      <?php
      $total_hour = $total_hour + $total_hour_per_person;

      echo (sprintf('%02d', intdiv($total_hour_per_person, 60)) . ':' . sprintf('%02d', ($total_hour_per_person % 60)));
      ?>
    </td>
    <td style="max-width: 70px;"></td>
  </tr>
  @endforeach

  <tr>
    <td align="right" colspan="{{ $total_col+4 }}">
      TOTAL HOURS
    </td>
    <td class="text-center">
      <?php
      echo (sprintf('%02d', intdiv($total_hour, 60)) . ':' . sprintf('%02d', ($total_hour % 60)));
      ?>
    </td>
    <td></td>
  </tr>
</table>