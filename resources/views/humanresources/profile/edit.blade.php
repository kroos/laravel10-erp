@extends ('layouts.app')

@section('content')
<style>
	.btn-sm-custom {
		padding: 0px;
		border-radius: 8px;
		height: 25px;
		width: 40px;
	}

/* .form-control, #ic {
border: 1px solid black;
} */

/* div {
border: 1px solid black;
} */
</style>

<?php
$gender = App\Models\HumanResources\OptGender::pluck('gender', 'id')->sortKeys()->toArray();
$nationality = App\Models\HumanResources\OptCountry::pluck('country', 'id')->sortKeys()->toArray();
$religion = App\Models\HumanResources\OptReligion::pluck('religion', 'id')->sortKeys()->toArray();
$race = App\Models\HumanResources\OptRace::pluck('race', 'id')->sortKeys()->toArray();
$marital_status = App\Models\HumanResources\OptMaritalStatus::pluck('marital_status', 'id')->sortKeys()->toArray();
$relationship = App\Models\HumanResources\OptRelationship::pluck('relationship', 'id')->sortKeys()->toArray();
$health_status = App\Models\HumanResources\OptHealthStatus::pluck('health_status', 'id')->sortKeys()->toArray();
$education_level = App\Models\HumanResources\OptEducationLevel::pluck('education_level', 'id')->sortKeys()->toArray();

$emergencies = $profile->hasmanyemergency()->get();
$spouses = $profile->hasmanyspouse()->get();
$childrens = $profile->hasmanychildren()->get();
$totalRows_emergency = $emergencies->count();
$totalRows_spouse = $spouses->count();
$totalRows_children = $childrens->count();
?>

<div class="container rounded bg-white mt-2 mb-2">

	<div class="row">
		<div class="col-md-2 border-right">
			<div class="d-flex flex-column align-items-center text-center p-3 py-5">
				<img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
				<span class="font-weight-bold">ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
				<span> </span>
			</div>
		</div>

		<div class="col-md-10 border-right">
			<div class="p-1 py-3">

				{!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

				<div class="row">
					<div class="d-flex justify-content-between align-items-center">
						<h4 class="text-right">Staff Profile</h4>
					</div>
				</div>
				<div class="row mb-5">
					<div class="col-md-6 border-right">
						<div class="px-3">
							<div class="row mt-3">
								<div class="col-md-12">
									<label for="name" class="labels">Name</label>
									<input type="text" class="form-control" value="{{ $profile->name }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6 {{ $errors->has('ic') ? 'has-error' : '' }}">
									<label for="ic" class="labels">IC</label>
									{!! Form::text( 'ic', @$value, ['class' => 'form-control', 'id' => 'ic', 'placeholder' => 'Please Insert'] ) !!}
								</div>
								<div class="col-md-6 {{ $errors->has('mobile') ? 'has-error' : '' }}">
									<label for="mobile" class="labels">PHONE NUMBER</label>
									{!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-12 {{ $errors->has('email') ? 'has-error' : '' }}">
									<label for="email" class="labels">EMAIL</label>
									{!! Form::text( 'email', @$value, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'Please Insert'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-12 {{ $errors->has('address') ? 'has-error' : '' }}">
									<label for="address" class="labels">ADDRESS</label>
									{!! Form::text( 'address', @$value, ['class' => 'form-control', 'id' => 'address', 'placeholder' => 'Please Insert'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-12">
									<label for="department" class="labels">DEPARTMENT</label>
									<input type="text" id="department" class="form-control" value="{{ $profile->belongstomanydepartment()->first()->department }}" readonly>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 border-right">
						<div class="px-3">
							<div class="row mt-3">
								<div class="col-md-6">
									<label for="category" class="labels">CATEGORY</label>
									<input type="text" id="category" class="form-control" value="{{ $profile->belongstomanydepartment->first()->belongstocategory->category }}" readonly>
								</div>
								<div class="col-md-6">
									<label for="restday_group_id" class="labels">SATURDAY GROUPING</label>
									<input type="text" id="restday_group_id" class="form-control" value="Group {{ $profile->restday_group_id }}" readonly>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6 {{ $errors->has('dob') ? 'has-error' : '' }}">
									<label for="dob" class="labels">DATE OF BIRTH</label>
									{!! Form::text( 'dob', @$value, ['class' => 'form-control dob-input', 'id' => 'dob', 'placeholder' => 'Please Select'] ) !!}
								</div>
								<div class="col-md-6 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
									<label for="gender_id" class="labels">GENDER</label>
									{!! Form::select( 'gender_id', $gender, @$value, ['class' => 'form-control select-input', 'id' => 'gender_id', 'placeholder' => 'Please Select'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6 {{ $errors->has('nationality_id') ? 'has-error' : '' }}">
									<label for="nationality_id" class="labels">NATIONALITY</label>
									{!! Form::select( 'nationality_id', $nationality, @$value, ['class' => 'form-control select-input', 'id' => 'nationality_id', 'placeholder' => 'Please Select'] ) !!}
								</div>
								<div class="col-md-6 {{ $errors->has('race_id') ? 'has-error' : '' }}">
									<label for="race_id" class="labels">RACE</label>
									{!! Form::select( 'race_id', $race, @$value, ['class' => 'form-control select-input', 'id' => 'race_id', 'placeholder' => 'Please Select'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
									<label for="religion_id" class="labels">RELIGION</label>
									{!! Form::select( 'religion_id', $religion, @$value, ['class' => 'form-control select-input', 'id' => 'religion_id', 'placeholder' => 'Please Select'] ) !!}
								</div>
								<div class="col-md-6 {{ $errors->has('marital_status_id') ? 'has-error' : '' }}">
									<label for="marital_status_id" class="labels">MARITAL STATUS</label>
									{!! Form::select( 'marital_status_id', $marital_status, @$value, ['class' => 'form-control select-input', 'id' => 'marital_status_id', 'placeholder' => 'Please Select'] ) !!}
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6">
									<label for="join_date" class="labels">JOIN DATE</label>
									<input type="text" id="join_date" class="form-control" value="{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}" readonly>
								</div>
								<div class="col-md-6">
									<label for="confirm_date" class="labels">CONFIRM DATE</label>
									<input type="text" id="confirm_date" class="form-control" value="{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!--------------------------- EMERGENCY ---------------------------->
				<?php $i = 1 ?>
				<div class="row">
					<div class="d-flex justify-content-between align-items-center col-md-3">
						<h4 class="text-right">Emergency Contact</h4>
					</div>
					<div class="col-md-9">
						@if ($totalRows_emergency < 2) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_emergency" type="button">
							<i class="bi-plus" aria-hidden="true"></i>
						</button>
						@endif
					</div>
				</div>
				<div class="row mb-5">
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_emergency_odd">

							@foreach ($emergencies as $emergency)
							@if ($loop->odd)

							<div class="table_emergency">
								<input type="hidden" name="emer[{{ $i }}][id]" value="{{ $emergency->id }}">
								<input type="hidden" name="emer[{{ $i }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('emer.'.$i.'.contact_person') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][contact_person]" class="labels">NAME</label>
										{!! Form::text( "emer[$i][contact_person]", @$emergency->contact_person, ['class' => 'form-control', 'id' => "emer[$i][contact_person]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('emer.'.$i.'.relationship_id') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][relationship_id]" class="labels">RELATIONSHIP</label>
										{!! Form::select( "emer[$i][relationship_id]", $relationship, @$emergency->relationship_id, ['class' => 'form-control select-input', 'id' => "emer[$i][relationship_id]", 'placeholder' => 'Please Select'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('emer.'.$i.'.phone') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][phone]" class="labels">PHONE NUMBER</label>
										{!! Form::text( "emer[$i][phone]", @$emergency->phone, ['class' => 'form-control', 'id' => "emer[$i][phone]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('emer.'.$i.'.address') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][address]" class="labels">ADDRESS</label>
										{!! Form::text( "emer[$i][address]", @$emergency->address, ['class' => 'form-control', 'id' => "emer[$i][address]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_emergency" data-id="{{ $emergency->id }}" data-table="emergency"></button>
								</div>
							</div>

							<?php $i++ ?>
							@endif
							@endforeach

						</div>
					</div>
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_emergency_even">

							@foreach ($emergencies as $emergency)
							@if ($loop->even)

							<div class="table_emergency">
								<input type="hidden" name="emer[{{ $i }}][id]" value="{{ $emergency->id }}">
								<input type="hidden" name="emer[{{ $i }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('emer.'.$i.'.contact_person') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][contact_person]" class="labels">NAME</label>
										{!! Form::text( "emer[$i][contact_person]", @$emergency->contact_person, ['class' => 'form-control', 'id' => "emer[$i][contact_person]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('emer.'.$i.'.relationship_id') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][relationship_id]" class="labels">RELATIONSHIP</label>
										{!! Form::select( "emer[$i][relationship_id]", $relationship, @$emergency->relationship_id, ['class' => 'form-control select-input', 'id' => "emer[$i][relationship_id]", 'placeholder' => 'Please Select'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('emer.'.$i.'.phone') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][phone]" class="labels">PHONE NUMBER</label>
										{!! Form::text( "emer[$i][phone]", @$emergency->phone, ['class' => 'form-control', 'id' => "emer[$i][phone]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('emer.'.$i.'.address') ? 'has-error' : '' }}">
										<label for="emer[{{$i}}][address]" class="labels">ADDRESS</label>
										{!! Form::text( "emer[$i][address]", @$emergency->address, ['class' => 'form-control', 'id' => "emer[$i][address]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_emergency" data-id="{{ $emergency->id }}" data-table="emergency"></button>
								</div>
							</div>

							<?php $i++ ?>
							@endif
							@endforeach

						</div>
					</div>
				</div>

				<!--------------------------- SPOUSE ---------------------------->
				<?php $j = 1 ?>
				<div class="row">
					<div class="d-flex justify-content-between align-items-center col-md-2">
						<h4 class="text-right">Spouse</h4>
					</div>
					<div class="col-md-10">
						@if ($totalRows_spouse < 4) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_spouse" type="button">
							<i class="bi-plus" aria-hidden="true"></i>
						</button>
						@endif
          			</div>
				</div>
				<div class="row">
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_spouse_odd">

							@foreach ($spouses as $spouse)
							@if ($loop->odd)

							<div class="mb-5 table_spouse">
								<input type="hidden" name="spou[{{ $j }}][id]" value="{{ $spouse->id }}">
								<input type="hidden" name="spou[{{ $j }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('spou.'.$j.'.spouse') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][spouse]" class="labels">NAME</label>
										{!! Form::text( "spou[$j][spouse]", @$spouse->spouse, ['class' => 'form-control', 'id' => "spou[$j][spouse]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.id_card_passport') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][id_card_passport]" class="labels">IC</label>
										{!! Form::text( "spou[$j][id_card_passport]", @$spouse->id_card_passport, ['class' => 'form-control', 'id' => "spou[$j][id_card_passport]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.phone') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][phone]" class="labels">PHONE NUMBER</label>
										{!! Form::text( "spou[$j][phone]", @$spouse->phone, ['class' => 'form-control', 'id' => "spou[$j][phone]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.dob') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][dob]" class="labels">Date Of Birth</label>
										{!! Form::text( "spou[$j][dob]", @$spouse->dob, ['class' => 'form-control dob-input', 'id' => "spou[$j][dob]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.profession') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][profession]" class="labels">Profession</label>
										{!! Form::text( "spou[$j][profession]", @$spouse->profession, ['class' => 'form-control', 'id' => "spou[$j][profession]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_spouse" data-id="{{ $spouse->id }}" data-table="spouse"></button>
								</div>
							</div>

							<?php $j++ ?>
							@endif
							@endforeach

						</div>
					</div>
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_spouse_even">

							@foreach ($spouses as $spouse)
							@if ($loop->even)

							<div class="mb-5 table_spouse">
								<input type="hidden" name="spou[{{ $j }}][id]" value="{{ $spouse->id }}">
								<input type="hidden" name="spou[{{ $j }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('spou.'.$j.'.spouse') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][spouse]" class="labels">NAME</label>
										{!! Form::text( "spou[$j][spouse]", @$spouse->spouse, ['class' => 'form-control', 'id' => "spou[$j][spouse]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.id_card_passport') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][id_card_passport]" class="labels">IC</label>
										{!! Form::text( "spou[$j][id_card_passport]", @$spouse->id_card_passport, ['class' => 'form-control', 'id' => "spou[$j][id_card_passport]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.phone') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][phone]" class="labels">PHONE NUMBER</label>
										{!! Form::text( "spou[$j][phone]", @$spouse->phone, ['class' => 'form-control', 'id' => "spou[$j][phone]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.dob') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][dob]" class="labels">Date Of Birth</label>
										{!! Form::text( "spou[$j][dob]", @$spouse->dob, ['class' => 'form-control dob-input', 'id' => "spou[$j][dob]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('spou.'.$j.'.profession') ? 'has-error' : '' }}">
										<label for="spou[{{$j}}][profession]" class="labels">Profession</label>
										{!! Form::text( "spou[$j][profession]", @$spouse->profession, ['class' => 'form-control', 'id' => "spou[$j][profession]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_spouse" data-id="{{ $spouse->id }}" data-table="spouse"></button>
								</div>
							</div>

							<?php $j++ ?>
							@endif
							@endforeach

						</div>
					</div>
				</div>

				<!--------------------------- CHILDREN ---------------------------->
				<?php $k = 1 ?>
				<div class="row">
					<div class="d-flex justify-content-between align-items-center col-md-2">
						<h4 class="text-right">Children</h4>
					</div>
					<div class="col-md-10">
						@if ($totalRows_children < 25) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_children" type="button">
							<i class="bi-plus" aria-hidden="true"></i>
						</button>
						@endif
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_children_odd">

							@foreach ($childrens as $children)
							@if ($loop->odd)

							<div class="mb-5 table_children">
								<input type="hidden" name="chil[{{ $k }}][id]" value="{{ $children->id }}">
								<input type="hidden" name="chil[{{ $k }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.children') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][children]" class="labels">NAME</label>
										{!! Form::text( "chil[$k][children]", @$children->children, ['class' => 'form-control', 'id' => "chil[$k][children]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('chil.'.$k.'.dob') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][dob]" class="labels">Date Of Birth</label>
										{!! Form::text( "chil[$k][dob]", @$children->dob, ['class' => 'form-control dob-input', 'id' => "chil[$k][dob]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('chil.'.$k.'.gender_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][gender_id]" class="labels">Gender</label>
										{!! Form::select( "chil[$k][gender_id]", $gender, @$children->gender_id, ['class' => 'form-control select-input', 'id' => "chil[$k][gender_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.health_status_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][health_status_id]" class="labels">Health Condition</label>
										{!! Form::select( "chil[$k][health_status_id]", $health_status, @$children->health_status_id, ['class' => 'form-control select-input', 'id' => "chil[$k][health_status_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.education_level_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][education_level_id]" class="labels">Education Level</label>
										{!! Form::select( "chil[$k][education_level_id]", $education_level, @$children->education_level_id, ['class' => 'form-control select-input', 'id' => "chil[$k][education_level_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_children" data-id="{{ $children->id }}" data-table="children"></button>
								</div>
							</div>

							<?php $k++ ?>
							@endif
							@endforeach

						</div>
					</div>
					<div class="col-md-6 border-right">
						<div class="px-3 wrap_children_even">

							@foreach ($childrens as $children)
							@if ($loop->even)

							<div class="mb-5 table_children">
								<input type="hidden" name="chil[{{ $k }}][id]" value="{{ $children->id }}">
								<input type="hidden" name="chil[{{ $k }}][staff_id]" value="{{ $profile-> id }}">

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.children') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][children]" class="labels">NAME</label>
										{!! Form::text( "chil[$k][children]", @$children->children, ['class' => 'form-control', 'id' => "chil[$k][children]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6 {{ $errors->has('chil.'.$k.'.dob') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][dob]" class="labels">Date Of Birth</label>
										{!! Form::text( "chil[$k][dob]", @$children->dob, ['class' => 'form-control dob-input', 'id' => "chil[$k][dob]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
									<div class="col-md-6 {{ $errors->has('chil.'.$k.'.gender_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][gender_id]" class="labels">Gender</label>
										{!! Form::select( "chil[$k][gender_id]", $gender, @$children->gender_id, ['class' => 'form-control select-input', 'id' => "chil[$k][gender_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.health_status_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][health_status_id]" class="labels">Health Condition</label>
										{!! Form::select( "chil[$k][health_status_id]", $health_status, @$children->health_status_id, ['class' => 'form-control select-input', 'id' => "chil[$k][health_status_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12 {{ $errors->has('chil.'.$k.'.education_level_id') ? 'has-error' : '' }}">
										<label for="chil[{{$k}}][education_level_id]" class="labels">Education Level</label>
										{!! Form::select( "chil[$k][education_level_id]", $education_level, @$children->education_level_id, ['class' => 'form-control select-input', 'id' => "chil[$k][education_level_id]", 'placeholder' => 'Please Insert'] ) !!}
									</div>
								</div>

								<div class="mt-1 d-flex flex-row justify-content-end">
									<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_children" data-id="{{ $children->id }}" data-table="children"></button>
								</div>
							</div>

							<?php $k++ ?>
							@endif
							@endforeach

						</div>
					</div>
				</div>

				<div class="row">
					<div class="text-center">
						{!! Form::button('Save', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
					</div>
				</div>

				{!! Form::close() !!}

				<div class="row mt-4">
					<div class="text-center">
						<a href="{{ url()->previous() }}">
							<button class="btn btn-sm btn-outline-secondary">Back</button>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DELETE EMERGENCY
$(document).on('click', '.delete_emergency', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Emergency Contact',
		text: 'Are you sure to delete this contact?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('profile') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function(e){
	window.location.reload(true);
});


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE SPOUSE
$(document).on('click', '.delete_spouse', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Spouse Contact',
		text: 'Are you sure to delete this contact?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('profile') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function(e){
	window.location.reload(true);
});


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE CHILDREN
$(document).on('click', '.delete_children', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Children Contact',
		text: 'Are you sure to delete this contact?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('profile') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function(e){
	window.location.reload(true);
});

/////////////////////////////////////////////////////////////////////////////////////////
// ADD EMERGENCY
var max_emergency = 2;
var totalRows_emergency = {{ $totalRows_emergency }};

$(".add_emergency").click(function() {

	if (totalRows_emergency % 2 === 0) {
		var wrap_emergency = $(".wrap_emergency_odd");
	} else {
		var wrap_emergency = $(".wrap_emergency_even");
	}

	if(totalRows_emergency < max_emergency) { totalRows_emergency++; wrap_emergency.append( '<div class="table_emergency">' + '<input type="hidden" name="emer[' +totalRows_emergency+'][id]" value="">' +
		'<input type="hidden" name="emer['+totalRows_emergency+'][staff_id]" value="{{ $profile-> id}}">' +
		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('emer.*.contact_person') ? 'has-error' : '' }}">' +
				'<label for="emer['+totalRows_emergency+'][contact_person]" class="labels">NAME</label>' +
				'<input class="form-control" id="emer['+totalRows_emergency+'][contact_person]" placeholder="Please Insert" name="emer['+totalRows_emergency+'][contact_person]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-6 {{ $errors->has('emer.*.relationship_id') ? 'has-error' : '' }}">' +
				'<label for="emer['+totalRows_emergency+'][relationship_id]" class="labels">RELATIONSHIP</label>' +
				'<select class="form-control select-input" id="emer['+totalRows_emergency+'][relationship_id]" name="emer['+totalRows_emergency+'][relationship_id]">' +
					@foreach ($relationship as $relationship_id => $relationship_js)
					'<option value="{{$relationship_id}}">{{ $relationship_js }}</option>' +
					@endforeach
				'</select>' +
			'</div>' +
			'<div class="col-md-6 {{ $errors->has('emer.*.phone') ? 'has-error' : '' }}">' +
				'<label for="emer['+totalRows_emergency+'][phone]" class="labels">PHONE NUMBER</label>' +
				'<input class="form-control" id="emer['+totalRows_emergency+'][phone]" placeholder="Please Insert" name="emer['+totalRows_emergency+'][phone]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('emer.*.address') ? 'has-error' : '' }}">' +
				'<label for="emer['+totalRows_emergency+'][address]" class="labels">ADDRESS</label>' +
				'<input class="form-control" id="emer['+totalRows_emergency+'][address]" placeholder="Please Insert" name="emer['+totalRows_emergency+'][address]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="mt-1 d-flex flex-row justify-content-end">' +
			'<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg remove_emergency"></button>' +
		'</div>' +
	'</div>'

	);

	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][staff_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][contact_person]"]'));
	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][relationship_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][phone]"]'));
	$('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][address]"]'));

	$('.dob-input').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format: 'YYYY-MM-DD',
		useCurrent: true,
	});

	$('.select-input').select2({
		placeholder: 'Please Select',
		width: '100%',
		allowClear: true,
		closeOnSelect: true,
	});
};
});

// DELETE EMERGENCY
$(".wrap_emergency_odd").on("click",".remove_emergency", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="emer['+ totalRows_emergency +'][id]"]');
	var $option2 = $row.find('[name="emer['+ totalRows_emergency +'][staff_id]"]');
	var $option3 = $row.find('[name="emer['+ totalRows_emergency +'][contact_person]"]');
	var $option4 = $row.find('[name="emer['+ totalRows_emergency +'][relationship_id]"]');
	var $option5 = $row.find('[name="emer['+ totalRows_emergency +'][phone]"]');
	var $option6 = $row.find('[name="emer['+ totalRows_emergency +'][address]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	console.log();
	totalRows_emergency--;
});

// DELETE EMERGENCY
$(".wrap_emergency_even").on("click",".remove_emergency", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="emer['+ totalRows_emergency +'][id]"]');
	var $option2 = $row.find('[name="emer['+ totalRows_emergency +'][staff_id]"]');
	var $option3 = $row.find('[name="emer['+ totalRows_emergency +'][contact_person]"]');
	var $option4 = $row.find('[name="emer['+ totalRows_emergency +'][relationship_id]"]');
	var $option5 = $row.find('[name="emer['+ totalRows_emergency +'][phone]"]');
	var $option6 = $row.find('[name="emer['+ totalRows_emergency +'][address]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	console.log();
	totalRows_emergency--;
});


/////////////////////////////////////////////////////////////////////////////////////////
// ADD SPOUSE
var max_spouse = 4;
var totalRows_spouse = {{ $totalRows_spouse }};

$(".add_spouse").click(function() {

	if (totalRows_spouse % 2 === 0) {
		var wrap_spouse = $(".wrap_spouse_odd");
	} else {
		var wrap_spouse = $(".wrap_spouse_even");
	}

	if(totalRows_spouse < max_spouse) { totalRows_spouse++; wrap_spouse.append( '<div class="mb-5 table_spouse">' + '<input type="hidden" name="spou[' +totalRows_spouse+'][id]" value="">' +
		'<input type="hidden" name="spou['+totalRows_spouse+'][staff_id]" value="{{ $profile-> id }}">' +
		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('spou.*.spouse') ? 'has-error' : '' }}">' +
				'<label for="spou['+totalRows_spouse+'][spouse]" class="labels">NAME</label>' +
				'<input class="form-control" id="spou['+totalRows_spouse+'][spouse]" placeholder="Please Insert" name="spou['+totalRows_spouse+'][spouse]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-6 {{ $errors->has('spou.*.id_card_passport') ? 'has-error' : '' }}">' +
				'<label for="spou['+totalRows_spouse+'][id_card_passport]" class="labels">IC</label>' +
				'<input class="form-control" id="spou['+totalRows_spouse+'][id_card_passport]" placeholder="Please Insert" name="spou['+totalRows_spouse+'][id_card_passport]" type="text" value="">' +
			'</div>' +
			'<div class="col-md-6 {{ $errors->has('spou.*.phone') ? 'has-error' : '' }}">' +
				'<label for="spou['+totalRows_spouse+'][phone]" class="labels">PHONE NUMBER</label>' +
				'<input class="form-control" id="spou['+totalRows_spouse+'][phone]" placeholder="Please Insert" name="spou['+totalRows_spouse+'][phone]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-6 {{ $errors->has('spou.*.dob') ? 'has-error' : '' }}">' +
				'<label for="spou['+totalRows_spouse+'][dob]" class="labels">Date Of Birth</label>' +
				'<input class="form-control dob-input" id="spou['+totalRows_spouse+'][dob]" placeholder="Please Insert" name="spou['+totalRows_spouse+'][dob]" type="text" value="">' +
			'</div>' +
			'<div class="col-md-6 {{ $errors->has('spou.*.profession') ? 'has-error' : '' }}">' +
				'<label for="spou['+totalRows_spouse+'][profession]" class="labels">Profession</label>' +
				'<input class="form-control" id="spou['+totalRows_spouse+'][profession]" placeholder="Please Insert" name="spou['+totalRows_spouse+'][profession]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="mt-1 d-flex flex-row justify-content-end">' +
			'<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg remove_spouse"></button>' +
		'</div>' +
	'</div>'

	);

	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][staff_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][spouse]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][phone]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][dob]"]'));
	$('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][profession]"]'));

	$('.dob-input').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format: 'YYYY-MM-DD',
		useCurrent: true,
	});

	$('.select-input').select2({
		placeholder: 'Please Select',
		width: '100%',
		allowClear: true,
		closeOnSelect: true,
	});
};
});

// DELETE SPOUSE
$(".wrap_spouse_odd").on("click",".remove_spouse", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="spou['+ totalRows_spouse +'][id]"]');
	var $option2 = $row.find('[name="spou['+ totalRows_spouse +'][staff_id]"]');
	var $option3 = $row.find('[name="spou['+ totalRows_spouse +'][spouse]"]');
	var $option4 = $row.find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]');
	var $option5 = $row.find('[name="spou['+ totalRows_spouse +'][phone]"]');
	var $option6 = $row.find('[name="spou['+ totalRows_spouse +'][dob]"]');
	var $option7 = $row.find('[name="spou['+ totalRows_spouse +'][profession]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	$('#form').bootstrapValidator('removeField', $option7);
	console.log();
	totalRows_spouse--;
});

// DELETE SPOUSE
$(".wrap_spouse_even").on("click",".remove_spouse", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="spou['+ totalRows_spouse +'][id]"]');
	var $option2 = $row.find('[name="spou['+ totalRows_spouse +'][staff_id]"]');
	var $option3 = $row.find('[name="spou['+ totalRows_spouse +'][spouse]"]');
	var $option4 = $row.find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]');
	var $option5 = $row.find('[name="spou['+ totalRows_spouse +'][phone]"]');
	var $option6 = $row.find('[name="spou['+ totalRows_spouse +'][dob]"]');
	var $option7 = $row.find('[name="spou['+ totalRows_spouse +'][profession]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	$('#form').bootstrapValidator('removeField', $option7);
	console.log();
	totalRows_spouse--;
});


/////////////////////////////////////////////////////////////////////////////////////////
// ADD CHILDREN
var max_children = 25;
var totalRows_children = {{ $totalRows_children }};
var test = {{ $totalRows_children }};

$(".add_children").click(function() {

	if (totalRows_children % 2 === 0) {
		var wrap_children = $(".wrap_children_odd");
	} else {
		var wrap_children = $(".wrap_children_even");
	}

	if(totalRows_children < max_children) { totalRows_children++; wrap_children.append( '<div class="mb-5 table_children">' + '<input type="hidden" name="chil[' +totalRows_children+'][id]" value="">' +
		'<input type="hidden" name="chil['+totalRows_children+'][staff_id]" value="{{ $profile-> id }}">' +
		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('chil.*.children') ? 'has-error' : '' }}">' +
				'<label for="chil['+totalRows_children+'][children]" class="labels">NAME</label>' +
				'<input class="form-control" id="chil['+totalRows_children+'][children]" placeholder="Please Insert" name="chil['+totalRows_children+'][children]" type="text" value="">' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-6 {{ $errors->has('chil.*.dob') ? 'has-error' : '' }}">' +
				'<label for="chil['+totalRows_children+'][dob]" class="labels">Date Of Birth</label>' +
				'<input class="form-control dob-input" id="chil['+totalRows_children+'][dob]" placeholder="Please Insert" name="chil['+totalRows_children+'][dob]" type="text" value="">' +
			'</div>' +
			'<div class="col-md-6 {{ $errors->has('chil.*.gender_id') ? 'has-error' : '' }}">' +
				'<label for="chil['+totalRows_children+'][gender_id]" class="labels">Gender</label>' +
				'<select class="form-control select-input" id="chil['+totalRows_children+'][gender_id]" name="chil['+totalRows_children+'][gender_id]">' +
					@foreach ($gender as $gender_id => $gender_js)
					'<option value="{{$gender_id}}">{{ $gender_js }}</option>' +
					@endforeach
				'</select>' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('chil.*.health_status_id') ? 'has-error' : '' }}">' +
				'<label for="chil['+totalRows_children+'][health_status_id]" class="labels">Health Condition</label>' +
				'<select class="form-control select-input" id="chil['+totalRows_children+'][health_status_id]" name="chil['+totalRows_children+'][health_status_id]">' +
					@foreach ($health_status as $health_status_id => $health_status_js)
					'<option value="{{$health_status_id}}">{{ $health_status_js }}</option>' +
					@endforeach
				'</select>' +
			'</div>' +
		'</div>' +

		'<div class="row mt-3">' +
			'<div class="col-md-12 {{ $errors->has('chil.*.education_level_id') ? 'has-error' : '' }}">' +
				'<label for="chil['+totalRows_children+'][education_level_id]" class="labels">Education Level</label>' +
				'<select class="form-control select-input" id="chil['+totalRows_children+'][education_level_id]" name="chil['+totalRows_children+'][education_level_id]">' +
					@foreach ($education_level as $education_level_id => $education_level_js)
					'<option value="{{$education_level_id}}">{{ $education_level_js }}</option>' +
					@endforeach
				'</select>' +
			'</div>' +
		'</div>' +

		'<div class="mt-1 d-flex flex-row justify-content-end">' +
			'<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg remove_children"></button>' +
		'</div>' +
	'</div>'

	);

	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][staff_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][children]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][dob]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][gender_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][health_status_id]"]'));
	$('#form').bootstrapValidator('addField', $('.table_children') .find('[name="chil['+ totalRows_children +'][education_level_id]"]'));

	$('.dob-input').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format: 'YYYY-MM-DD',
		useCurrent: true,
	});

	$('.select-input').select2({
		placeholder: 'Please Select',
		width: '100%',
		allowClear: true,
		closeOnSelect: true,
	});
};
});

// DELETE CHILDREN
$(".wrap_children_odd").on("click",".remove_children", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="chil['+ totalRows_children +'][id]"]');
	var $option2 = $row.find('[name="chil['+ totalRows_children +'][staff_id]"]');
	var $option3 = $row.find('[name="chil['+ totalRows_children +'][children]"]');
	var $option4 = $row.find('[name="chil['+ totalRows_children +'][dob]"]');
	var $option5 = $row.find('[name="chil['+ totalRows_children +'][gender_id]"]');
	var $option6 = $row.find('[name="chil['+ totalRows_children +'][health_status_id]"]');
	var $option7 = $row.find('[name="chil['+ totalRows_children +'][education_level_id]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	$('#form').bootstrapValidator('removeField', $option7);
	console.log();
	totalRows_children--;
});

// DELETE CHILDREN
$(".wrap_children_even").on("click",".remove_children", function(e){
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="chil['+ totalRows_children +'][id]"]');
	var $option2 = $row.find('[name="chil['+ totalRows_children +'][staff_id]"]');
	var $option3 = $row.find('[name="chil['+ totalRows_children +'][children]"]');
	var $option4 = $row.find('[name="chil['+ totalRows_children +'][dob]"]');
	var $option5 = $row.find('[name="chil['+ totalRows_children +'][gender_id]"]');
	var $option6 = $row.find('[name="chil['+ totalRows_children +'][health_status_id]"]');
	var $option7 = $row.find('[name="chil['+ totalRows_children +'][education_level_id]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	$('#form').bootstrapValidator('removeField', $option7);
	console.log();
	totalRows_children--;
});


/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('.dob-input').datetimepicker({
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// SELECTION
$('.select-input').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATOR
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: '',
			invalid: '',
			validating: ''
		},
		fields: {
			ic: {
				validators: {
					notEmpty: {
						message: 'Please insert ic.'
					},
					numeric: {
						message: 'The value is not numeric'
					}
				}
			},

			mobile: {
				validators: {
					notEmpty: {
						message: 'Please insert mobile number.'
					},
					numeric: {
						message: 'The value is not numeric'
					}
				}
			},

			email: {
				validators: {
					notEmpty: {
						message: 'Please insert email.'
					},
					emailAddress: {
						message: 'The value is not a valid email.'
					}
				}
			},

			address: {
				validators: {
					notEmpty: {
						message: 'Please insert address.'
					}
				}
			},

			dob: {
				validators: {
					notEmpty: {
						message: 'Please insert date of birth.'
					}
				}
			},

			gender_id: {
				validators: {
					notEmpty: {
						message: 'Please select a gender.'
					}
				}
			},

			nationality_id: {
				validators: {
					notEmpty: {
						message: 'Please select a nationality.'
					}
				}
			},

			race_id: {
				validators: {
					notEmpty: {
						message: 'Please select a race.'
					}
				}
			},

			religion_id: {
				validators: {
					notEmpty: {
						message: 'Please select a religion.'
					}
				}
			},

			marital_status_id: {
				validators: {
					notEmpty: {
						message: 'Please select a marital status.'
					}
				}
			},

			<?php $l = 1; ?>
			<?php foreach ($emergencies as $emergency) { ?>
				'emer[{{ $l }}][contact_person]': {
					validators: {
						notEmpty: {
							message: 'Please insert contact person.'
						}
					}
				},

				'emer[{{ $l }}][relationship_id]': {
					validators: {
						notEmpty: {
							message: 'Please select a relationship.'
						}
					}
				},

				'emer[{{ $l }}][phone]': {
					validators: {
						notEmpty: {
							message: 'Please insert phone number.'
						},
						numeric: {
							message: 'The value is not numeric'
						}
					}
				},

				'emer[{{ $l }}][address]': {
					validators: {
						notEmpty: {
							message: 'Please insert address.'
						}
					}
				},
				<?php $l++; ?>
			<?php } ?>

			<?php $m = 1; ?>
			<?php foreach ($spouses as $spouse) { ?>
				'spou[{{ $m }}][spouse]': {
					validators: {
						notEmpty: {
							message: 'Please insert spouse name.'
						}
					}
				},

				'spou[{{ $m }}][id_card_passport]': {
					validators: {
						notEmpty: {
							message: 'Please insert ic.'
						},
						numeric: {
							message: 'The value is not numeric'
						}
					}
				},

				'spou[{{ $m }}][phone]': {
					validators: {
						notEmpty: {
							message: 'Please insert phone number.'
						},
						numeric: {
							message: 'The value is not numeric'
						}
					}
				},

				'spou[{{ $m }}][dob]': {
					validators: {
						notEmpty: {
							message: 'Please insert date of birth.'
						}
					}
				},

				'spou[{{ $m }}][profession]': {
					validators: {
						notEmpty: {
							message: 'Please insert profession.'
						}
					}
				},
				<?php $m++; ?>
			<?php } ?>

			<?php $n = 1; ?>
			<?php foreach ($childrens as $children) { ?>
				'chil[{{ $n }}][children]': {
					validators: {
						notEmpty: {
							message: 'Please insert children name.'
						}
					}
				},

				'chil[{{ $n }}][dob]': {
					validators: {
						notEmpty: {
							message: 'Please insert date of birth.'
						}
					}
				},

				'chil[{{ $n }}][gender_id]': {
					validators: {
						notEmpty: {
							message: 'Please select a gender.'
						}
					}
				},

				'chil[{{ $n }}][health_status_id]': {
					validators: {
						notEmpty: {
							message: 'Please select a health status.'
						}
					}
				},

				'chil[{{ $n }}][education_level_id]': {
					validators: {
						notEmpty: {
							message: 'Please select an educational status.'
						}
					}
				},
				<?php $n++; ?>
			<?php } ?>

		}
	})
});
@endsection
