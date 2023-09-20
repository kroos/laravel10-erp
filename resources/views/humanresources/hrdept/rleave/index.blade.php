@extends('layouts.app')

@section('content')
<?php
// load facade
use Illuminate\Database\Eloquent\Builder;

// load models
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;

// load helper
use App\Helpers\UnavailableDateTime;

// load lib
use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
  @include('humanresources.hrdept.navhr')
  <h4>Replacement Leave</h4>
  <div class="">
    <table id="attendance" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Type</th>
          <th>Cause</th>
          <th>Leave</th>
          <th>Date</th>
          <th>In</th>
          <th>Break</th>
          <th>Resume</th>
          <th>Out</th>
          <th>Duration</th>
          <th>Overtime</th>
          <th>Remarks</th>
          <th>Exception</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
@endsection




















@section('js')

@endsection

@section('nonjquery')

@endsection