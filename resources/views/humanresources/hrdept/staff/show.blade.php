@extends('layouts.app')

@section('content')
<div class="col justify-content-center row">
	@include('humanresources.hrdept.navhr')
	<h4 class="align-items-center">Profile {{ $staff->name }}</h4>
	<div class="d-flex flex-column align-items-center text-center p-3 py-5">
		<img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $staff->image) }}">
		<span class="font-weight-bold">{{ $staff->name }}</span>
		<span class="font-weight-bold">{{ $staff->hasmanylogin()->where('active', 1)->first()->username }}</span>
		<span> </span>
	</div>
	<div class="col-sm-6 row">
		<div class="col-5">Name :</div>
		<div class="col-7">{{ $staff->name }}</div>
		<div class="col-5">Identity Card/Passport :</div>
		<div class="col-7">{{ $staff->ic }}</div>
		<div class="col-5">Religion :</div>
		<div class="col-7">{{ $staff->belongstoreligion->religion }}</div>
		<div class="col-5">Gender :</div>
		<div class="col-7">{{ $staff->belongstogender->gender }}</div>
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
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip

@endsection
