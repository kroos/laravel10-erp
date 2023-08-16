@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-center">Add Staff</h4>
	{{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
	<div class="col-sm-6 row">

		<div class="form-group row mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
			{{ Form::label( 'name', 'Name : ', ['class' => 'col-sm-5 col-form-label'] ) }}
			<div class="col-auto">
				{{ Form::text('name', @$value, ['class' => 'form-control col-auto', 'id' => 'reason', 'placeholder' => 'Name', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row mb-3 {{ $errors->has('ic') ? 'has-error' : '' }}">
			{{ Form::label( 'ic', 'Identity Card/Passport : ', ['class' => 'col-sm-5 col-form-label'] ) }}
			<div class="col-auto">
				{{ Form::text('ic', @$value, ['class' => 'form-control col-auto', 'id' => 'ic', 'placeholder' => 'Identity Card/Passport', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row mb-3 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
			{{ Form::label( 'ic', 'Religion : ', ['class' => 'col-sm-5 col-form-label'] ) }}
			<div class="col-auto">
				{{ Form::text('religion_id', @$value, ['class' => 'form-control col-auto', 'id' => 'ic', 'placeholder' => 'Religion', 'autocomplete' => 'off']) }}
			</div>
		</div>







<?php $staff = \App\Models\Staff::findOrFail(37) ?>
		<div class="col-5">Religion :</div>
		<div class="col-7">{{ $staff->belongstoreligion?->religion }}</div>
		<div class="col-5">Gender :</div>
		<div class="col-7">{{ $staff->belongstogender?->gender }}</div>
		<div class="col-5">Race :</div>
		<div class="col-7">{{ $staff->belongstorace?->race }}</div>
		<div class="col-5">Nationality :</div>
		<div class="col-7">{{ $staff->belongstonationality?->country }}</div>
		<div class="col-5">Marital Status :</div>
		<div class="col-7">{{ $staff->belongstomaritalstatus?->marital_status }}</div>
		<div class="col-5">Email :</div>
		<div class="col-7">{{ $staff->email }}</div>
		<div class="col-5">Address :</div>
		<div class="col-7">{{ $staff->address }}</div>
		<div class="col-5">Place of Birth :</div>
		<div class="col-7">{{ $staff->place_of_birth }}</div>
		<div class="col-5">Mobile :</div>
		<div class="col-7">{{ $staff->mobile }}</div>
		<div class="col-5">Phone :</div>
		<div class="col-7">{{ $staff->phone }}</div>
		<div class="col-5">Date of Birth :</div>
		<div class="col-7">{{ \Carbon\Carbon::parse($staff->dob)->format('j M Y') }}</div>
		<div class="col-5">CIMB Account :</div>
		<div class="col-7">{{ $staff->cimb_account }}</div>
		<div class="col-5">EPF Account :</div>
		<div class="col-7">{{ $staff->epf_account }}</div>
		<div class="col-5">Income Tax No :</div>
		<div class="col-7">{{ $staff->income_tax_no }}</div>
		<div class="col-5">SOCSO No :</div>
		<div class="col-7">{{ $staff->socso_no }}</div>
		<div class="col-5">SOCSO No :</div>
		<div class="col-7">{{ $staff->socso_no }}</div>
		<div class="col-5">Weight :</div>
		<div class="col-7">{{ $staff->weight }} kg</div>
		<div class="col-5">Height :</div>
		<div class="col-7">{{ $staff->height }} cm</div>
		<div class="col-5">Date Join :</div>
		<div class="col-7">{{ \Carbon\Carbon::parse($staff->join)->format('j M Y') }}</div>
		<div class="col-5">Date Confirmed :</div>
		<div class="col-7">{{ \Carbon\Carbon::parse($staff->confirmed)->format('j M Y') }}</div>
	</div>
	<div class="col-sm-6 row">
		<div class="col-5">System Authorised :</div>
		<div class="col-7">{{ $staff->belongstoauthorised?->authorise }}</div>
		<div class="col-5">Leave Approval Personnel :</div>
		<div class="col-7">{{ $staff->belongstodivision?->div }}</div>
		<div class="col-5">Leave Approval Flow :</div>
		<div class="col-7">{{ $staff->belongstoleaveapprovalflow?->description }}</div>
		<div class="col-5">RestDay Group :</div>
		<div class="col-7">{{ $staff->belongstorestdaygroup?->group }}</div>
		<div class="col-5">Cross Backup To :</div>
<?php
$cb = $staff->crossbackupto()->get();
?>
		<div class="col-7">
			@if($cb->count())
			<ul>
				@foreach($cb as $r)
				<li>{{ $r->name }}</li>
				@endforeach
			</ul>
			@endif
		</div>
		<div class="col-5">Cross Backup For :</div>
<?php
$cbf = $staff->crossbackupfrom()->get();
?>
		<div class="col-7">
			@if($cbf->count())
			<ul>
				@foreach($cbf as $rf)
				<li>{{ $rf->name }}</li>
				@endforeach
			</ul>
			@endif
		</div>
		<div class="col-5">Spouse :</div>
		<div class="col-7">
			@if($staff->hasmanyspouse()?->get()->count())
			<table class="table table-sm table-hover" style="font-size:12px;">
				<thead>
					<tr>
						<th>Name</th>
						<th>Phone</th>
					</tr>
				</thead>
				<tbody>
				@foreach($staff->hasmanyspouse()?->get() as $sp)
					<tr>
						<td>$sp->spouse</td>
						<td>$sp->phone</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			@endif
		</div>
		<div class="col-5">Children :</div>
		<div class="col-7">
			@if($staff->hasmanychildren()?->get()->count())
			<table class="table table-sm table-hover" style="font-size:12px;">
				<thead>
					<tr>
						<th>Name</th>
						<th>Age</th>
						<th>Tax Exemption (%)</th>
					</tr>
				</thead>
				<tbody>
				@foreach($staff->hasmanychildren()?->get() as $sc)
					<tr>
						<td>{{$sc->children}}</td>
						<td>{{ \Carbon\Carbon::parse($sc->dob)->toPeriod(now(), 1, 'year')->count() }} year/s</td>
						<td>{{ $sc->belongstotaxexemptionpercentage?->tax_exemption_percentage }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			@endif
		</div>
		<div class="col-5">Emergency Contact :</div>
		<div class="col-7">
			@if($staff->hasmanyemergency()?->get()->count())
			<table class="table table-sm table-hover" style="font-size:12px;">
				<thead>
					<tr>
						<th>Name</th>
						<th>Phone</th>
					</tr>
				</thead>
				<tbody>
				@foreach($staff->hasmanyemergency()?->get() as $sc)
					<tr>
						<td>{{ $sc->contact_person }}</td>
						<td>{{ $sc->phone }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			@endif
		</div>
	</div>
	{!! Form::submit('Save', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip

@endsection
