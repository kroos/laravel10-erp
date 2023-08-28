<?php
use App\Models\Staff;
use App\Models\HumanResources\OptReligion;
use App\Models\HumanResources\OptGender;
use App\Models\HumanResources\OptRace;
use App\Models\HumanResources\OptMaritalStatus;
use App\Models\HumanResources\OptCountry;
?>
@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Add Staff</h4>
	{{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

	<div class="row justify-content-center">
		<div class="col-sm-6 gy-1 gx-1 align-items-start">
			<div class="form-group row mb-3 {{ $errors->has('username') ? 'has-error' : '' }}">
				{{ Form::label( 'unam', 'UserName : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('username', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'unam', 'placeholder' => 'Username', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
				{{ Form::label( 'pas', 'Password : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('password', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'pas', 'placeholder' => 'Password', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('status_id') ? 'has-error' : '' }}">
				{{ Form::label( 'sta', 'Staff Status : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="status_id" id="sta" class="form-select form-select-sm" placeholder="Status"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('category_id') ? 'has-error' : '' }}">
				{{ Form::label( 'cat', 'Category : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="category_id" id="cat" class="form-select form-select-sm" placeholder="Category"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('branch_id') ? 'has-error' : '' }}">
				{{ Form::label( 'bra', 'Branch : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="branch_id" id="bra" class="form-select form-select-sm" placeholder="Branch"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('pivot_dept_id') ? 'has-error' : '' }}">
				{{ Form::label( 'dep', 'Department : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="pivot_dept_id" id="dep" class="form-select form-select-sm" placeholder="Department"></select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('restday_group_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rdg', 'Rest Day Group : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="restday_group_id" id="rdg" class="form-select form-select-sm" placeholder="Department"></select>
				</div>
			</div>

			<div class="offset-sm-4 form-check row {{ $errors->has('authorise_id') ? 'has-error' : '' }}">
				<div class="pretty p-icon p-jelly">
					<input class="form-check-input" type="checkbox" value="1" id="auth">
					<div class="state p-info-o">
						<i class="icon mdi mdi-check-all"></i>
						<label class="form-check-label" for="auth">Is System Administrator?</label>
					</div>
				</div>
			</div>









			<div class="form-group row mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
				{{ Form::label( 'nam', 'Name : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('name', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'nam', 'placeholder' => 'Name', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('ic') ? 'has-error' : '' }}">
				{{ Form::label( 'ic', 'Identity Card/Passport : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('ic', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'ic', 'placeholder' => 'Identity Card/Passport', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rel', 'Religion : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('religion_id', OptReligion::pluck('religion', 'id')->toArray(), @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'rel', 'placeholder' => 'Religion', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
				{{ Form::label( 'gen', 'Gender : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('gender_id', OptGender::pluck('gender', 'id')->toArray(), @$value, ['class' => 'form-control form-select col-auto', 'id' => 'gen', 'placeholder' => 'Gender', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('race_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rac', 'Race : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('race_id', OptRace::pluck('race', 'id')->toArray(), @$value, ['class' => 'form-control form-select col-auto', 'id' => 'rac', 'placeholder' => 'Race', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('nationality_id') ? 'has-error' : '' }}">
				{{ Form::label( 'nat', 'Nationality : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('nationality_id', OptCountry::pluck('country', 'id')->toArray(), @$value, ['class' => 'form-control form-select col-auto', 'id' => 'nat', 'placeholder' => 'Nationality', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('marital_status_id') ? 'has-error' : '' }}">
				{{ Form::label( 'mar', 'Marital Status : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('marital_status_id', OptMaritalStatus::pluck('marital_status', 'id')->toArray(), @$value, ['class' => 'form-control form-select col-auto', 'id' => 'mar', 'placeholder' => 'Marital Status', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
				{{ Form::label( 'ema', 'Email : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('email', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'ema', 'placeholder' => 'Email', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('address') ? 'has-error' : '' }}">
				{{ Form::label( 'add', 'Address : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::textarea('address', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'add', 'placeholder' => 'Address', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
				{{ Form::label( 'mob', 'Mobile : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('mobile', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'mob', 'placeholder' => 'Mobile', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
				{{ Form::label( 'pho', 'Phone : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('phone', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'pho', 'placeholder' => 'Phone', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('dob') ? 'has-error' : '' }}">
				{{ Form::label( 'dob', 'Date Of Birth : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('dob', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'dob', 'placeholder' => 'Date Of Birth', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('cimb_account') ? 'has-error' : '' }}">
				{{ form::label( 'cia', 'CIMB Account : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('cimb_account', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'cia', 'placeholder' => 'CIMB Account', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('epf_account') ? 'has-error' : '' }}">
				{{ form::label( 'epf', 'EPF Account : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('epf_account', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'epf', 'placeholder' => 'EPF Account', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('income_tax_no') ? 'has-error' : '' }}">
				{{ form::label( 'itn', 'Income Tax No : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('income_tax_no', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'itn', 'placeholder' => 'Income Tax No', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('socso_no') ? 'has-error' : '' }}">
				{{ form::label( 'son', 'SOCSO No : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('socso_no', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'son', 'placeholder' => 'SOCSO No', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('weight') ? 'has-error' : '' }}">
				{{ form::label( 'wei', 'Weight : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('weight', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'wei', 'placeholder' => 'Weight', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('height') ? 'has-error' : '' }}">
				{{ form::label( 'hei', 'Height : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('height', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'hei', 'placeholder' => 'Height', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('join') ? 'has-error' : '' }}">
				{{ form::label( 'jpo', 'Date Join : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('join', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'jpo', 'placeholder' => 'Date Join', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('image') ? 'has-error' : '' }}">
				{{ Form::label( 'ima', 'Image : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto supportdoc">
					{{ Form::file( 'image', ['class' => 'form-control form-control-sm form-control-file', 'id' => 'ima', 'placeholder' => 'Image']) }}
				</div>
			</div>
		</div>

		<div class="col-sm-6 gy-1 gx-1 align-items-start">

			<div class="col-auto row">
				<div class="row">
					<div class="col-auto">
						<h6>Staff Spouse</h6>
					</div>
					<div class="col-auto">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary spouse_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Spouse
						</button>
					</div>
				</div>
				<div class="row mb-1 g-1 spouse_wrap">
					<div class="row spouse_row">
						<div class="col-auto mb-1 g-1 ">
							<button class="btn btn-sm btn-outline-secondary spouse_remove" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.spouse') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][spouse]" id="spo" class="form-control form-control-sm" placeholder="Spouse">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.phone') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.profession') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">
						</div>
					</div>
				</div>
			</div>
			<p>&nbsp;</p>
			<div class="col-auto row">
				<div class="row">
					<div class="col-auto">
						<h6>Staff Children</h6>
					</div>
					<div class="col-auto">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary children_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Children
						</button>
					</div>
				</div>
				<div class="row mb-1 g-1 children_wrap">
					<div class="row children_row">
						<div class="col-auto mb-1 g-1 ">
							<button class="btn btn-sm btn-outline-secondary children_remove" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.children') ? 'has-error' : '' }}">
							<input type="text" name="staffchildren[1][children]" id="chi_1" class="form-control form-control-sm" placeholder="Children">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.gender_id') ? 'has-error' : '' }}">
							<select name="staffchildren[1][gender_id]" id="cge_1" class="form-select form-select-sm" placeholder="Gender"></select>
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.education_level_id') ? 'has-error' : '' }}">
							<select name="staffchildren[1][education_level_id]" id="cel_1" class="form-select form-select-sm" placeholder="Education Level"></select>
						</div>

						<div class="col-auto mb-1 gx-6 form-group {{ $errors->has('staffchildren.*.health_status_id') ? 'has-error' : '' }}">
							<select name="staffchildren[1][health_status_id]" id="chs_1" class="form-select form-select-sm" placeholder="Health Status"></select>
						</div>

						<div class="form-group form-check col-auto mb-1 gx-6 {{ $errors->has('staffchildren.*.tax_exemption') ? 'has-error' : '' }}">
							<input type="hidden" name="staffchildren[1][tax_exemption]" class="form-check-input" value="No">
							<input type="checkbox" name="staffchildren[1][tax_exemption]" class="form-check-input" value="Yes" id="cte_1">
							<label class="form-check-label" for="cte_1">Valid for Tax Exemption?</label>
						</div>


						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.tax_exemption_percentage_id') ? 'has-error' : '' }}">
							<select name="staffchildren[1][tax_exemption_percentage_id]" id="ctep_1" class="form-select form-select-sm" placeholder="Tax Exemption Percentage"></select>
						</div>

					</div>
				</div>
			</div>

			<p>&nbsp;</p>
			<div class="col-auto row">
				<div class="row">
					<div class="col-auto">
						<h6>Staff Emergency Contact</h6>
					</div>
					<div class="col-auto">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary emergency_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add Emergency Contact
						</button>
					</div>
				</div>
				<div class="row mb-1 g-1 emergency_wrap">
					<div class="row emergency_row">
						<div class="col-auto mb-1 g-1 ">
							<button class="btn btn-sm btn-outline-secondary emergency_remove" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>
						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.contact_person') ? 'has-error' : '' }}">
							<input type="text" name="staffemergency[1][contact_person]" id="ecp_1" class="form-control form-control-sm" placeholder="Emergency Contact">
						</div>
						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.phone') ? 'has-error' : '' }}">
							<input type="text" name="staffemergency[1][phone]" id="epp_1" class="form-control form-control-sm" placeholder="Phone">
						</div>
						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.relationship_id') ? 'has-error' : '' }}">
							<select name="staffemergency[1][relationship_id]" id="ere_1" class="form-select form-select-sm" placeholder="Relationship"></select>
						</div>
						<div class="col-auto mb-1 gx-1 form-group {{ $errors->has('staffemergency.*.address') ? 'has-error' : '' }}">
							<input type="textarea" name="staffemergency[1][address]" id="ead_1" class="form-control form-control-sm" placeholder="Address">
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="offset-6">
		{!! Form::submit('Add Staff', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
	</div>

	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#dob, #jpo').datetimepicker({
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

$('#rel, #gen, #rac, #nat, #mar').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
});

$('#sta').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('status.status') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#cat').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('category.category') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#bra').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('branch.branch') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});
// $('#bra').on("select2:select", function (e) {
// 	console.log("select2:select", e);
// 	$("#dep").remoteChained({
// 		parents : '#bra, #cat',
// 		url : "{{ route('department.department') }}",
// 	});
// });
$('#bra').on("select2:unselect", function (e) {
	console.log("select2:unselect", e);
	$('#dep').val(null).trigger('change');
	// $("#dep").remoteChained({
	// 	parents : '#bra, #cat',
	// 	url : "{{ route('department.department') }}",
	// });
});
// $('#cat').on("select2:select", function (e) {
// 	console.log("select2:select", e);
// 	$("#dep").remoteChained({
// 		parents : '#bra, #cat',
// 		url : "{{ route('department.department') }}",
// 	});
// });
$('#cat').on("select2:unselect", function (e) {
	console.log("select2:unselect", e);
	$('#dep').val(null).trigger('change');
	// $("#dep").remoteChained({
	// 	parents : '#bra, #cat',
	// 	url : "{{ route('department.department') }}",
	// });
});

$('#dep').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('department.department') }}',
		type: 'GET',
		dataType: 'json',
		data: function (params) {
			var query = {
				branch_id: $('#bra').val(),
				category_id: $('#cat').val(),
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#rdg').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('restdaygroup.restdaygroup') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});
















/////////////////////////////////////////////////////////////////////////////////////////
$('#cge_1').select2({
	placeholder: 'Gender',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('gender.gender') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#cel_1').select2({
	placeholder: 'Education Level',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('educationlevel.educationlevel') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#chs_1').select2({
	placeholder: 'Health Status',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('healthstatus.healthstatus') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#ctep_1').select2({
	placeholder: 'Tax Exemption Percentage',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('taxexemptionpercentage.taxexemptionpercentage') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

$('#ere_1').select2({
	placeholder: 'Relationship',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('relationship.relationship') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
			}
			return query;
		}
	},
});

/////////////////////////////////////////////////////////////////////////////////////////
// add spouse : add and remove row

var max_fields  = 4;						//maximum input boxes allowed
var add_buttons	= $(".spouse_add");
var wrappers	= $(".spouse_wrap");

var xs = 1;
$(add_buttons).click(function(){
	// e.preventDefault();

	//max input box allowed
	if(xs < max_fields){
		xs++;
		wrappers.append(

			'<div class="row spouse_row">' +
				'<div class="col-auto mb-1 g-1 ">' +
					'<button class="btn btn-sm btn-outline-secondary spouse_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.spouse') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffspouse[' + xs + '][spouse]" id="spo" class="form-control form-control-sm" placeholder="Spouse">' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.phone') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffspouse[' + xs + '][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.profession') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffspouse[' + xs + '][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">' +
				'</div>' +
			'</div>'

		); //add input box

		//bootstrap validate
		$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][spouse]"]'));
		$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][phone]"]'));
		$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][profession]"]'));
	}
})

$(wrappers).on("click",".spouse_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="staffspouse[' + xs + '][spouse]"]');
	var $option2 = $row.find('[name="staffspouse[' + xs + '][phone]"]');
	var $option3 = $row.find('[name="staffspouse[' + xs + '][profession]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	console.log();
	xs--;
})

/////////////////////////////////////////////////////////////////////////////////////////
// add children : add and remove row

var cmax_fields  = 12;						//maximum input boxes allowed
var cadd_buttons	= $(".children_add");
var cwrappers	= $(".children_wrap");

var xc = 1;
$(cadd_buttons).click(function(){
	// e.preventDefault();

	//max input box allowed
	if(xc < cmax_fields){
		xc++;
		cwrappers.append(
			'<div class="row children_row">' +
				'<div class="col-auto mb-1 g-1 ">' +
					'<button class="btn btn-sm btn-outline-secondary children_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.children') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffchildren[' + xc + '][children]" id="chi_' + xc + '" class="form-control form-control-sm" placeholder="Children">' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.gender_id') ? 'has-error' : '' }}">' +
					'<select name="staffchildren[' + xc + '][gender_id]" id="cge_' + xc + '" class="form-select form-select-sm" placeholder="Gender"></select>' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.education_level_id') ? 'has-error' : '' }}">' +
					'<select name="staffchildren[' + xc + '][education_level_id]" id="cel_' + xc + '" class="form-select form-select-sm" placeholder="Education Level"></select>' +
				'</div>' +
				'<div class="col-auto mb-1 gx-6 form-group {{ $errors->has('staffchildren.*.health_status_id') ? 'has-error' : '' }}">' +
					'<select name="staffchildren[' + xc + '][health_status_id]" id="chs_' + xc + '" class="form-select form-select-sm" placeholder="Health Status"></select>' +
				'</div>' +
				'<div class="form-group form-check col-auto mb-1 gx-6 {{ $errors->has('staffchildren.*.tax_exemption') ? 'has-error' : '' }}">' +
					'<input type="hidden" name="staffchildren[' + xc + '][tax_exemption]" class="form-check-input" value="No">' +
					'<input type="checkbox" name="staffchildren[' + xc + '][tax_exemption]" class="form-check-input" value="Yes" id="cte_' + xc + '">' +
					'<label class="form-check-label" for="cte_' + xc + '">Valid for Tax Exemption?</label>' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.tax_exemption_percentage_id') ? 'has-error' : '' }}">' +
					'<select name="staffchildren[' + xc + '][tax_exemption_percentage_id]" id="ctep_' + xc + '" class="form-select form-select-sm" placeholder="Tax Exemption Percentage"></select>' +
				'</div>' +
			'</div>'
		); //add input box

		$('#cge_' + xc +'').select2({
			placeholder: 'Gender',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('gender.gender') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
					}
					return query;
				}
			},
		});

		$('#cel_' + xc +'').select2({
			placeholder: 'Education Level',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('educationlevel.educationlevel') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
					}
					return query;
				}
			},
		});

		$('#chs_' + xc +'').select2({
			placeholder: 'Health Status',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('healthstatus.healthstatus') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
					}
					return query;
				}
			},
		});

		$('#ctep_' + xc +'').select2({
			placeholder: 'Tax Exemption Percentage',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('taxexemptionpercentage.taxexemptionpercentage') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
					}
					return query;
				}
			},
		});

		//bootstrap validate
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffchildren['+ xc +'][spouse]"]'));
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffchildren['+ xc +'][phone]"]'));
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffchildren['+ xc +'][profession]"]'));
	}
})

$(cwrappers).on("click",".children_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="staffchildren[' + xc + '][spouse]"]');
	var $option2 = $row.find('[name="staffchildren[' + xc + '][phone]"]');
	var $option3 = $row.find('[name="staffchildren[' + xc + '][profession]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	console.log();
	xc--;
})

/////////////////////////////////////////////////////////////////////////////////////////
// add emergency : add and remove row

var emax_fields = 3;						//maximum input boxes allowed
var eadd_buttons = $(".emergency_add");
var ewrappers = $(".emergency_wrap");

var xe = 1;
$(eadd_buttons).click(function(){
	// e.preventDefault();

	//max input box allowed
	if(xe < emax_fields){
		xe++;
		ewrappers.append(
			'<div class="row emergency_row">' +
				'<div class="col-auto mb-1 g-1 ">' +
					'<button class="btn btn-sm btn-outline-secondary emergency_remove" type="button">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.contact_person') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffemergency[' + xe + '][contact_person]" id="ecp_' + xe + '" class="form-control form-control-sm" placeholder="Emergency Contact">' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.phone') ? 'has-error' : '' }}">' +
					'<input type="text" name="staffemergency[' + xe + '][phone]" id="epp_' + xe + '" class="form-control form-control-sm" placeholder="Phone">' +
				'</div>' +
				'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffemergency.*.relationship_id') ? 'has-error' : '' }}">' +
					'<select name="staffemergency[' + xe + '][relationship_id]" id="ere_' + xe + '" class="form-select form-select-sm" placeholder="Relationship"></select>' +
				'</div>' +
				'<div class="col-auto mb-1 gx-1 form-group {{ $errors->has('staffemergency.*.address') ? 'has-error' : '' }}">' +
					'<input type="textarea" name="staffemergency[' + xe + '][address]" id="ead_' + xe + '" class="form-control form-control-sm" placeholder="Health Status">' +
				'</div>' +
			'</div>'
		); //add input box

		$('#ere_' + xe +'').select2({
			placeholder: 'Gender',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('relationship.relationship') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
					}
					return query;
				}
			},
		});


		//bootstrap validate
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffemergency['+ xe +'][contact_person]"]'));
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffemergency['+ xe +'][phone]"]'));
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffemergency['+ xe +'][relationship_id]"]'));
		$('#form').bootstrapValidator('addField',	$('.children_row')	.find('[name="staffemergency['+ xe +'][address]"]'));
	}
})

$(ewrappers).on("click",".children_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="staffemergency[' + xe + '][contact_person]"]');
	var $option2 = $row.find('[name="staffemergency[' + xe + '][phone]"]');
	var $option3 = $row.find('[name="staffemergency[' + xe + '][relationship_id]"]');
	var $option4 = $row.find('[name="staffemergency[' + xe + '][address]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	console.log();
	xe--;
})

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		username: {
			validators: {
				notEmpty: {
					message: 'Please insert username. '
				},
				remote: {
					type: 'POST',
					url: '{{ route('loginuser.loginuser') }}',
					message: 'Username exist. Please use another userame. ',
					data: function(validator) {
								return {
											_token: '{!! csrf_token() !!}',
											username: $('#unam').val(),
								};
							},
					delay: 1,		// wait 0.001 seconds
				},
			}
		},
		password: {
			validators: {
				notEmpty: {
					message: 'Please insert password. '
				},
			}
		},
		status_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
		category_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
		branch_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
		pivot_dept_id: {
			validators: {
				notEmpty: {
					field: 'branch_id',
					field: 'category_id',
					message: 'Please choose. '
				},
			}
		},
		restday_group_id: {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose. '
				// },
			}
		},
		authorise_id: {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose. '
				// },
			}
		},











		name: {
			validators: {
				notEmpty: {
					message: 'Please insert new staff name. '
				},
			}
		},
		ic: {
			validators: {
				notEmpty: {
					message: 'Please insert Identity Card or Passport. '
				},
			}
		},
		religion_id: {
			validators: {
				notEmpty: {
					message: 'Please select. '
				},
			}
		},
		gender_id: {
			validators: {
				notEmpty: {
					message: 'Please select. '
				},
			}
		},
		race_id: {
			validators: {
				notEmpty: {
					message: 'Please select. '
				},
			}
		},
		nationality_id: {
			validators: {
				// notEmpty: {
				// 	message: 'Please select. '
				// },
			}
		},
		marital_status_id: {
			validators: {
				notEmpty: {
					message: 'Please select. '
				},
			}
		},
		email: {
			validators: {
				notEmpty: {
					message: 'Please insert email. '
				},
			}
		},
		address: {
			validators: {
				notEmpty: {
					message: 'Please insert address. '
				},
			}
		},
		mobile: {
			validators: {
				notEmpty: {
					message: 'Please insert mobile. '
				},
			}
		},
		phone: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		dob: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		cimb_account: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		epf_account: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		income_tax_no: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		socso_no: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		weight: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		height: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		join: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
			}
		},
		join: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert phone. '
				// },
				date: {
					format: 'YYYY-MM-DD',
					message: 'The value is not a valid date. '
				},
			}
		},
		image: {
			validators: {
				file: {
					extension: 'jpeg,jpg,png,bmp',
					type: 'image/jpeg,image/png,image/bmp',
					maxSize: 2097152,	// 2048 * 1024,
					message: 'The selected file is not valid. Please use jpeg or png and the image is below than 3MB. '
				},
			}
		},

// spouse
@for ($ie = 1; $ie <= 4; $ie++)
		'staffspouse[{{ $ie }}][spouse]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse. '
				// },
				regexp: {
					regexp: /^[a-z\s\'\@]+$/i,
					message: 'The full name can consist of alphabetical characters, \', @ and spaces only'
				},
			}
		},
		'staffspouse[{{ $ie }}][phone]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse phone. '
				// },
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffspouse[{{ $ie }}][profession]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },
			}
		},
@endfor
// children
@for ($ic = 1; $ic <= 4; $ic++)
		'staffchildren[{{ $ic }}][children]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse. '
				// },
				regexp: {
					regexp: /^[a-z\s\'\@]+$/i,
					message: 'The full name can consist of alphabetical characters, \', @ and spaces only'
				},
			}
		},
		'staffchildren[{{ $ic }}][gender_id]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse phone. '
				// },
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffchildren[{{ $ic }}][education_level_id]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffchildren[{{ $ic }}][health_status_id]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffchildren[{{ $ic }}][tax_exemption]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },
				// numeric: {
				// 	message: 'Only numbers. '
				// },
			}
		},
@endfor
@for ($ie = 1; $ie <= 4; $ie++)
		'staffemergency[{{ $ie }}][contact_person]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse. '
				// },
				// regexp: {
				// 	regexp: /^[a-z\s\'\@]+$/i,
				// 	message: 'The full name can consist of alphabetieal characters, \', @ and spaces only'
				// },
			}
		},
		'staffemergency[{{ $ie }}][phone]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse phone. '
				// },
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffemergency[{{ $ie }}][relationship_id]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },
				numerie: {
					message: 'Only numbers. '
				},
			}
		},
		'staffemergency[{{ $ie }}][address]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert spouse profession. '
				// },

			}
		},
@endfor
	}
});

@endsection
