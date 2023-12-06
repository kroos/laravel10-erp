@extends('layouts.app')
@section('content')
<?php
// load model
use App\Models\HumanResources\HROutstation;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
} else {
	$t = false;
}
?>
<div class="container row align-items-start justify-content-center">
	<h2 class="text-center">Please note, this page can be only use for the personnel that is outstation. HR or CS officer will add your ID for outstation list.</h2>
	@if($t)
		<div class="col-sm-6 row border border-primary" >
			<p>Country Name: {{ $data->countryName }}</p>
			<p>Country Code: {{ $data->countryCode }}</p>
			<p>Region Code: {{ $data->regionCode }}</p>
			<p>Region Name: {{ $data->regionName }}</p>
			<p>City Name: {{ $data->cityName }}</p>
			<p>Zipcode: {{ $data->zipCode }}</p>
			<p>Latitude: {{ $data->latitude }}</p>
			<p>Longitude: {{ $data->longitude }}</p>
			<p>Click button below to mark your attendance</p>
			{{ Form::open(['route' => ['outstationattendance.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
				{{ Form::hidden('outstation_id', $r->first()?->id) }}
				{{ Form::submit('Mark Attendance', ['type' => 'submit', 'class' => 'btn btn-sm btn-primary']) }}
			{{ Form::close() }}
		</div>
	@else
		<h4 class="p-4 m-3 border border-bottom text-center alert alert-danger">Sorry, you cant mark your attendance yet. Please ask your superior (HR or CS Officer) to assists you.</h2>
	@endif






























</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
@endsection

