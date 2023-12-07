@extends('layouts.app')
@section('content')
<?php
// load model
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use \Carbon\Carbon;

$r = HROutstation::where('staff_id', \Auth::user()->belongstostaff->id)
		->where(function (Builder $query) {
			$query->whereDate('date_from', '<=', now())
			->whereDate('date_to', '>=', now());
		})
		->where('active', 1)
		->get();
		// ->ddrawsql();
// dd($r);
if ($r->count()) {
	$t = true;
	foreach ($r as $k => $v) {
		$n = HROutstationAttendance::where([
					['outstation_id', $v->id],
					['date_attend', now()->format('Y-m-d')],
					['staff_id', \Auth::user()->belongstostaff->id],
				])
				->whereNull('out')
				->first();
				// ->ddrawsql();
		if($n) {
			$loc[$v->id] = $v->belongstocustomer?->customer;
		} else {
			$loc[] = [];
		}
	}
} else {
	$t = false;
}

$m = HROutstationAttendance::whereDate('date_attend', now())
		->where('staff_id', \Auth::user()->belongstostaff->id)
		->whereNotNull('outstation_id')
		// ->whereNull('out')
		// ->ddrawsql();
		->get();
// dump($m->count());
?>
<div class="container row align-items-start justify-content-center">
	@if($t)
		@if($m->whereNull('out')->count())
			<h4 class="text-center">Please choose your location from the list and hit the "Mark Attendance" button.</h4>
		@else
			<h4 class="text-center">You have mark all your attendance for today.</h4>
		@endif
		<div class="col-sm-6 row m-2" >
			<dl class="row">
				<dt class="col-sm-3">Country Name:</dt>
				<dd class="col-sm-9">{{ $data->countryName }}</dd>

				<dt class="col-sm-3">Country Code:</dt>
				<dd class="col-sm-9">{{ $data->countryCode }}</dd>

				<dt class="col-sm-3">Region Code:</dt>
				<dd class="col-sm-9">{{ $data->regionCode }}</dd>

				<dt class="col-sm-3">Region Name:</dt>
				<dd class="col-sm-9">{{ $data->regionName }}</dd>

				<dt class="col-sm-3">City Name:</dt>
				<dd class="col-sm-9">{{ $data->cityName }}</dd>
				<dt class="col-sm-3">Zipcode:</dt>
				<dd class="col-sm-9">{{ $data->zipCode }}</dd>
				<dt class="col-sm-3">Latitude:</dt>
				<dd class="col-sm-9">{{ $data->latitude }}</dd>
				<dt class="col-sm-3">Longitude:</dt>
				<dd class="col-sm-9">{{ $data->longitude }}</dd>
			</dl>
			<div class="col-sm-12 table-responsive">
				<table id="att" class="table table-sm table-hover" style="font-size:12px;">
					<caption>Attendance for today</caption>
					<thead>
						<tr>
							<th>#</th>
							<th>Location</th>
							<th>Date</th>
							<th>In</th>
							<th>Out</th>
						</tr>
					</thead>
					<tbody>
						@foreach($m as $k => $v)
							<tr>
								<td>{{ $v->id }}</td>
								<td>{{ $v->belongstooutstation?->belongstocustomer?->customer }}</td>
								<td>{{ Carbon::parse($v->date_attend)->format('j M Y') }}</td>
								<td>{{ ($v->in)?Carbon::parse($v->in)->format('g:i a'):NULL }}</td>
								<td>{{ ($v->out)?Carbon::parse($v->out)->format('g:i a'):NULL }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			@if($m->whereNull('out')->count())
				<p>Click button below to mark your attendance</p>
				{{ Form::open(['route' => ['outstationattendance.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
					<div class="form-group row m-2 {{ $errors->has('outstation_id') ? 'has-error' : '' }}">
						{{ Form::label( 'outstation', 'Location : ', ['class' => 'col-sm-4 col-form-label'] ) }}
						<div class="col-sm-8">
							{{ Form::select('outstation_id', $loc, null, ['class' => 'form-select form-select-sm', 'id' => 'outstation', 'placeholder' => 'Please choose']) }}
						</div>
					</div>
					<div class="offset-sm-4 col-sm-8">
						{{ Form::submit('Mark Attendance', ['class' => 'btn btn-sm btn-primary']) }}
					</div>
				{{ Form::close() }}
			@endif
		</div>
	@else
		<h2 class="p-4 m-3 border border-bottom text-center alert alert-danger">Please note, this page can be only use for the outstation personnel. If you are eligible to use this page and mark your attendance, please ask your superior (HR or CS Officer) to assists you by adding your ID into the outstation list.</h2>
	@endif

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
@endsection

