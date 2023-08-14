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
	</div>
	<div class="col-sm-6">
asd
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip

@endsection
