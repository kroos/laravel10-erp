@extends('layouts.app')

@section('content')
<?php
use \Carbon\Carbon;
?>

<div class="col-sm-12 row">

  <h4>Holiday Calendar</h4>

  <table class="table table-hover table-sm" style="font-size:12px">
    <thead>
      <tr>
        <th class="text-center" colspan="6">&nbsp;</th>
      </tr>
      <tr>
        <th class="text-center" colspan="6">Holiday Calendar {{ $holidaycalendar }}</th>
      </tr>
      <tr>
        <th width="250px">From</th>
        <th width="250px">To</th>
        <th width="100px">Duration</th>
        <th>Holiday</th>
      </tr>
    </thead>
    <tbody>
      @foreach($holidays as $holiday)
      <tr>
        <td>{{ Carbon::parse($holiday->date_start)->format('d-m-Y l') }}</td>
        <td>{{ Carbon::parse($holiday->date_end)->format('d-m-Y l') }}</td>
        <td>{{ Carbon::parse($holiday->date_start)->daysUntil($holiday->date_end, 1)->count() }} day/s</td>
        <td>{{ $holiday->holiday }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@section('js')
@endsection