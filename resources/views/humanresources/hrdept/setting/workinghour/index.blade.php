@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\OptWorkingHour;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Working Hour &nbsp; <a href="{{ route('workinghour.create') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Working Hour For Next Year</a> </h4>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(OptWorkingHour::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Normal Working Hours ({{ $tp->year }})</th>
			</tr>
			<tr>
				<th>Year</th>
				<th>Time Start AM</th>
				<th>Time End AM</th>
				<th>Time Start PM</th>
				<th>Time End PM</th>
				<th>Effective Date From</th>
				<th>Effective Date To</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(OptWorkingHour::where('group', 0)->where('year', $tp->year)->orderBy('year', 'DESC')->orderBy('effective_date_start')->get() as $t)
			<tr>
				<td>{{ $t->year }}</td>
				<td>{{ Carbon::parse($t->time_start_am)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_end_am)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_start_pm)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_end_pm)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->effective_date_start)->format('D, j M Y') }}</td>
				<td>{{ Carbon::parse($t->effective_date_end)->format('D, j M Y') }}</td>
				<td>{{ $t->remarks }}</td>
				<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('workinghour.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
			</tr>
		@endforeach
		</tbody>
		<thead>
			<tr>
				<th class="text-center" colspan="8">Maintenance Working Hours {{ $tp->year }}</th>
			</tr>
			<tr>
				<th>Year</th>
				<th>Time Start AM</th>
				<th>Time End AM</th>
				<th>Time Start PM</th>
				<th>Time End PM</th>
				<th>Effective Date From</th>
				<th>Effective Date To</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(OptWorkingHour::where('group', 1)->where('year', $tp->year)->orderBy('year', 'DESC')->orderBy('effective_date_start')->get() as $t)
			<tr>
				<td>{{ $t->year }}</td>
				<td>{{ Carbon::parse($t->time_start_am)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_end_am)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_start_pm)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->time_end_pm)->format('g:i a') }}</td>
				<td>{{ Carbon::parse($t->effective_date_start)->format('D, j M Y') }}</td>
				<td>{{ Carbon::parse($t->effective_date_end)->format('D, j M Y') }}</td>
				<td>{{ $t->remarks }}</td>
				<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('workinghour.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
			</tr>
		@endforeach
		</tbody>
	@endforeach
	</table>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
