@extends('layouts.app')

@section('content')

<?php
$emergencies = $profile->hasmanyemergency()->get();
$spouses = $profile->hasmanyspouse()->get();
$childrens = $profile->hasmanychildren()->get();
?>
<div class="container row align-items-start justify-content-center">
	<div class="col-sm-2 row">
		<img class="rounded-5" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
		<span class="font-weight-bold">ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
		<span> </span>
	</div>
	<div class="col-sm-12 row align-items-start justify-content-center">
		<h4>Staff Profile &nbsp; <a href="{{ route('profile.edit', $profile->id) }}" class="btn btn-sm btn-outline-secondary">Change Password</a></h4>
		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $profile->name }}</dd>
				<dt class="col-sm-5">Identity Card/Passport</dt>
				<dd class="col-sm-7">{{ $profile->ic }}</dd>
				<dt class="col-sm-5">Mobile Number</dt>
				<dd class="col-sm-7">{{ $profile->mobile }}</dd>
				<dt class="col-sm-5">Email</dt>
				<dd class="col-sm-7">{{ $profile->email }}</dd>
				<dt class="col-sm-5">Address</dt>
				<dd class="col-sm-7"><address>{{ $profile->address }}</address></dd>
				<dt class="col-sm-5">Department</dt>
				<dd class="col-sm-7">{{ $profile->belongstomanydepartment()?->wherePivot('main', 1)->first()?->department }}</dd>
			</dl>
		</div>

		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Category</dt>
				<dd class="col-sm-7">{{ $profile->belongstomanydepartment()?->wherePivot('main', 1)->first()?->belongstocategory->category }}</dd>
				<dt class="col-sm-5">Saturday Group</dt>
				<dd class="col-sm-7">{{ $profile->belongstorestdaygroup?->group }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Gender</dt>
				<dd class="col-sm-7">{{ $profile->belongstogender->gender }}</dd>
				<dt class="col-sm-5">Nationality</dt>
				<dd class="col-sm-7">{{ $profile->belongstonationality?->country }}</dd>
				<dt class="col-sm-5">Race</dt>
				<dd class="col-sm-7">{{ $profile->belongstorace?->race }}</dd>
				<dt class="col-sm-5">Religion</dt>
				<dd class="col-sm-7">{{ $profile->belongstoreligion?->religion }}</dd>
				<dt class="col-sm-5">Marital Status</dt>
				<dd class="col-sm-7">{{ $profile->belongstoreligion?->religion }}</dd>
				<dt class="col-sm-5">Join Date</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Confirm Date</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}</dd>

			</dl>
		</div>
	</div>

	<div class="col-sm-12 row align-items-start justify-content-center mt-3">
		<div class="col-sm-4">
			<h4>Emergency Contact</h4>
			@if ($emergencies->count())
				@foreach ($emergencies as $emergency)
					<dl class="row">
						<dt class="col-sm-5">Name</dt>
						<dd class="col-sm-7">{{ $emergency->contact_person }}</dd>
						<dt class="col-sm-5">Relationship</dt>
						<dd class="col-sm-7">{{ $emergency->belongstorelationship?->relationship }}</dd>
						<dt class="col-sm-5">Phone Number</dt>
						<dd class="col-sm-7">{{ $emergency->phone }}</dd>
						<dt class="col-sm-5">Address</dt>
						<dd class="col-sm-7"><address>{{ $emergency->address }}</address></dd>
					</dl>
				@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			<h4>Spouse</h4>
			@if ($spouses->count())
				@foreach ($spouses as $spouse)
					<dl class="row">
						<dt class="col-sm-5">Name</dt>
						<dd class="col-sm-7">{{ $spouse->spouse }}</dd>
						<dt class="col-sm-5">Identity Card/Passport</dt>
						<dd class="col-sm-7">{{ $spouse->id_card_passport }}</dd>
						<dt class="col-sm-5">Phone Number</dt>
						<dd class="col-sm-7">{{ $spouse->phone }}</dd>
						<dt class="col-sm-5">Date Of Birth</dt>
						<dd class="col-sm-7">{{ \Carbon\Carbon::parse($spouse->dob)->format('d F Y') }}</dd>
						<dt class="col-sm-5">Profession</dt>
						<dd class="col-sm-7">{{ $spouse->profession }}</dd>
					</dl>
				@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			<h4>Children</h4>
			@if ($childrens->count())
				@foreach ($childrens as $children)
					<dl class="row">
						<dt class="col-sm-5">Name</dt>
						<dd class="col-sm-7">{{ $children->children }}</dd>
						<dt class="col-sm-5">Date Of Birth</dt>
						<dd class="col-sm-7">{{ \Carbon\Carbon::parse($children->dob)->format('d F Y') }}</dd>
						<dt class="col-sm-5">Gender</dt>
						<dd class="col-sm-7">{{ $children->belongstogender?->gender }}</dd>
						<dt class="col-sm-5">Health Condition</dt>
						<dd class="col-sm-7">{{ $children->belongstohealthstatus?->health_status }}</dd>
						<dt class="col-sm-5">Education Level</dt>
						<dd class="col-sm-7">{{ $children->belongstoeducationlevel?->education_level }}</dd>
					</dl>
				@endforeach
			@endif
		</div>
	</div>

	<div class="col-sm-12 table-responsive">
		<table id="attendance" class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Date</th>
					<th>Day Type</th>
					<th>In</th>
					<th>Break</th>
					<th>Resume</th>
					<th>Out</th>
					<th>Leave</th>
					<th>Outstation</th>
					<th>Overtime</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Date</td>
					<td>Day Type</td>
					<td>In</td>
					<td>Break</td>
					<td>Resume</td>
					<td>Out</td>
					<td>Leave</td>
					<td>Outstation</td>
					<td>Overtime</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('js')

@endsection
