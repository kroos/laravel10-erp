@extends('layouts.app')

@section('content')

<?php
$emergencies = $profile->hasmanyemergency()->get();
$spouses = $profile->hasmanyspouse()->get();
$childrens = $profile->hasmanychildren()->get();
?>

<div class="container rounded bg-white mt-2 mb-2">
	<div class="row">
		<div class="col-sm-2 border-right">
			<div class="d-flex flex-column align-items-center text-center p-3 py-5">
				<img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
				<span class="font-weight-bold">ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
				<span class="font-weight-bold">Password: {{ $profile->hasmanylogin()->where('active', 1)->first()->password }}</span>
				<span> </span>
			</div>
		</div>
		<div class="col-sm-10 border-right">
			<div class="p-1 py-3">
				<div class="row">
					<div class="d-flex justify-content-between align-items-center col-sm-2">
						<h4 class="text-right">Staff Profile</h4>
					</div>
					<div class="col-sm-10">
						<a href="{{ route('profile.edit', $profile->id) }}">
							<button class="btn btn-sm btn-outline-secondary">EDIT</button>
						</a>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col-sm-6 border-right">
						<div class="px-3">
							<div class="row mt-3">
								<div class="col-sm-12">
									<label class="labels">Name</label>
									<input type="text" class="form-control" value="{{ $profile->name }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">IC</label>
									<input type="text" class="form-control" value="{{ $profile->ic }}" readonly>
								</div>
								<div class="col-sm-6">
									<label class="labels">PHONE NUMBER</label>
									<input type="text" class="form-control" value="{{ $profile->mobile }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-12">
									<label class="labels">EMAIL</label>
									<input type="text" class="form-control" value="{{ $profile->email }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-12">
									<label class="labels">ADDRESS</label>
									<input type="text" class="form-control" value="{{ $profile->address }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-12">
									<label class="labels">DEPARTMENT</label>
									<input type="text" class="form-control" value="{{ $profile->belongstomanydepartment()->first()->department }}" readonly>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 border-right">
						<div class="px-3">
							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">CATEGORY</label>
									<input type="text" class="form-control" value="{{ $profile->belongstomanydepartment?->first()?->belongstocategory->category }}" readonly>
								</div>
								<div class="col-sm-6">
									<label class="labels">SATURDAY GROUPING</label>
									<input type="text" class="form-control" value="Group {{ $profile->restday_group_id }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">DATE OF BIRTH</label>
									<input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}" readonly>

								</div>
								<div class="col-sm-6">
									<label class="labels">GENDER</label>
									<input type="text" class="form-control" value="{{ $profile->belongstogender->gender }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">NATIONALITY</label>
									<input type="text" class="form-control" value="{{ $profile->belongstonationality?->country }}" readonly>
								</div>
								<div class="col-sm-6">
									<label class="labels">RACE</label>
									<input type="text" class="form-control" value="{{ $profile->belongstorace?->race }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">RELIGION</label>
									<input type="text" class="form-control" value="{{ $profile->belongstoreligion?->religion }}" readonly>
								</div>
								<div class="col-sm-6">
									<label class="labels">MARITAL STATUS</label>
									<input type="text" class="form-control" value="{{ $profile->belongstomaritalstatus?->marital_status }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-sm-6">
									<label class="labels">JOIN DATE</label>
									<input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}" readonly>
								</div>
								<div class="col-sm-6">
									<label class="labels">CONFIRM DATE</label>
									<input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>

				@if ($emergencies->count() != 0)
				<div class="row">
					<div class="d-flex justify-content-between align-items-center">
						<h4 class="text-right">Emergency Contact</h4>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($emergencies as $emergency)
							@if ($loop->odd)

							<div>
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $emergency->contact_person }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">RELATIONSHIP</label>
										<input type="text" class="form-control" value="{{ $emergency->belongstorelationship?->relationship}}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">PHONE NUMBER</label>
										<input type="text" class="form-control" value="{{ $emergency->phone }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">ADDRESS</label>
										<input type="text" class="form-control" value="{{ $emergency->address }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($emergencies as $emergency)
							@if ($loop->even)

							<div>
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $emergency->contact_person }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">RELATIONSHIP</label>
										<input type="text" class="form-control" value="{{ $emergency->belongstorelationship?->relationship }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">PHONE NUMBER</label>
										<input type="text" class="form-control" value="{{ $emergency->phone }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">ADDRESS</label>
										<input type="text" class="form-control" value="{{ $emergency->address }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
				</div>
				@endif


				@if ($spouses->count())
				<div class="row">
					<div class="d-flex justify-content-between align-items-center">
						<h4 class="text-right">Spouse</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($spouses as $spouse)
							@if ($loop->odd)

							<div class="mb-5">
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $spouse->spouse }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">IC</label>
										<input type="text" class="form-control" value="{{ $spouse->id_card_passport }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">PHONE NUMBER</label>
										<input type="text" class="form-control" value="{{ $spouse->phone }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">Date Of Birth</label>
										<input type="text" class="form-control" value="{{ $spouse->dob }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">Profession</label>
										<input type="text" class="form-control" value="{{ $spouse->profession }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($spouses as $spouse)
							@if ($loop->even)

							<div class="mb-5">
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $spouse->spouse }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">IC</label>
										<input type="text" class="form-control" value="{{ $spouse->id_card_passport }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">PHONE NUMBER</label>
										<input type="text" class="form-control" value="{{ $spouse->phone }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">Date Of Birth</label>
										<input type="text" class="form-control" value="{{ $spouse->dob }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">Profession</label>
										<input type="text" class="form-control" value="{{ $spouse->profession }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
				</div>
				@endif


				@if ($childrens->count() != 0)
				<div class="row">
					<div class="d-flex justify-content-between align-items-center">
						<h4 class="text-right">Children</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($childrens as $children)
							@if ($loop->odd)

							<div class="mb-5">
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $children->children }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">Date Of Birth</label>
										<input type="text" class="form-control" value="{{ $children->dob }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">Gender</label>
										<input type="text" class="form-control" value="{{ $children->belongstogender?->gender }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">Health Condition</label>
										<input type="text" class="form-control" value="{{ $children->belongstohealthstatus?->health_status }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">Education Level</label>
										<input type="text" class="form-control" value="{{ $children->belongstoeducationlevel?->education_level }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
					<div class="col-sm-6 border-right">
						<div class="px-3">

							@foreach ($childrens as $children)
							@if ($loop->even)

							<div class="mb-5">
								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">NAME</label>
										<input type="text" class="form-control" value="{{ $children->children }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-6">
										<label class="labels">Date Of Birth</label>
										<input type="text" class="form-control" value="{{ $children->dob }}" readonly>
									</div>
									<div class="col-sm-6">
										<label class="labels">Gender</label>
										<input type="text" class="form-control" value="{{ $children->belongstogender?->gender }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">Health Condition</label>
										<input type="text" class="form-control" value="{{ $children->belongstohealthstatus?->health_status }}" readonly>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-12">
										<label class="labels">Education Level</label>
										<input type="text" class="form-control" value="{{ $children->belongstoeducationlevel?->education_level }}" readonly>
									</div>
								</div>
							</div>

							@endif
							@endforeach

						</div>
					</div>
				</div>
				@endif

			</div>
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
