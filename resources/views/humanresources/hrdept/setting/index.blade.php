@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Setting</h4>
	<table id="setting" class="table table-hover table-sm align-middle" style="font-size:12px">
		<thead>
			<tr>
				<th>ID</th>
				<th>Setting</th>
				<th>Description</th>
				<th>Status</th>
				<th>Date Update</th>
			</tr>
		</thead>
		<tbody>
			@foreach($setting as $set)
				@if($set->id != 5 && $set->id != 6 && $set->id != 7)
					<tr>
						<td>{{ $set->id }}</td>
						<td>{{ $set->setting }}</td>
						<td>{{ $set->remarks }}</td>
						<td>
							<div class="form-check form-switch">
								<input type="checkbox" name="active" value="1" class="form-check-input" role="switch" id="{{$set->id}}_setting" data-id="{{ $set->id }}" {{ (!is_null($set->active))?'checked=checked':NULL }}>
								<label class="form-check-label" for="{{$set->id}}_setting">{{ (!is_null($set->active))?'Enable':'Disable' }}</label>
							</div>
						</td>
						<td>{{ \Carbon\Carbon::parse($set->updated_at)->format('j M Y') }}</td>
					</tr>
				@endif
				@if($set->id == 5)
					<tr>
						<td>{{ $set->id }}</td>
						<td>{{ $set->setting }}</td>
						<td>{{ $set->remarks }}</td>
						<td>
							<input type="number" name="active" value="{{ $set->active }}" id="{{$set->id}}_setting" data-id="{{ $set->id }}" class="form-control form-control-small col-auto" />
						</td>
						<td>{{ \Carbon\Carbon::parse($set->updated_at)->format('j M Y') }}</td>
					</tr>
				@endif
				@if($set->id == 6)
					<tr>
						<td>{{ $set->id }}</td>
						<td>{{ $set->setting }}</td>
						<td>{{ $set->remarks }}</td>
						<td>
							<div class="form-check form-switch">
								<input type="checkbox" name="active" value="1" class="form-check-input" role="switch" id="{{$set->id}}_setting" data-id="{{ $set->id }}" {{ (!is_null($set->active))?'checked=checked':NULL }}>
								<label class="form-check-label" for="{{$set->id}}_setting">{{ (!is_null($set->active))?'Enable':'Disable' }}</label>
							</div>
						</td>
						<td>{{ \Carbon\Carbon::parse($set->updated_at)->format('j M Y') }}</td>
					</tr>
				@endif
				@if($set->id == 7)
					<tr>
						<td>{{ $set->id }}</td>
						<td>{{ $set->setting }}</td>
						<td>{{ $set->remarks }}</td>
						<td>
							<div class="form-check form-switch">
								<input type="checkbox" name="active" value="1" class="form-check-input" role="switch" id="{{$set->id}}_setting" data-id="{{ $set->id }}" {{ (!is_null($set->active))?'checked=checked':NULL }}>
								<label class="form-check-label" for="{{$set->id}}_setting">{{ (!is_null($set->active))?'Enable':'Disable' }}</label>
							</div>
						</td>
						<td>{{ \Carbon\Carbon::parse($set->updated_at)->format('j M Y') }}</td>
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#1_setting,#2_setting,#3_setting,#4_setting,#6_setting,#7_setting').change(function() {
	// console.log($(this).prop('checked'));
	// console.log($(this).val());		// cant rely on value, always give value 1 even if its unchecked

	var dat = $.ajax({
		url: "{{ url('hrsetting') }}" + "/" + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					value1: $(this).prop('checked'),
					_token: '{!! csrf_token() !!}',
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			return response;
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	}).responseText;
	var data = $.parseJSON( dat );
	console.log(data.active);
	$("label[for='"+$(this).attr("id")+"']").text(data.active);
	swal.fire("Good job!", data.status, "success");
	// alert(data.status);
});

$('#5_setting').change(function() {
	var dat = $.ajax({
		url: "{{ url('hrsetting') }}" + "/" + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					active: $(this).val(),
					_token: '{!! csrf_token() !!}',
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			return response;
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	}).responseText;
	var data = $.parseJSON( dat );
	console.log(data.active);
	// $("label[for='"+$(this).attr("id")+"']").text(data.active);
	swal.fire("Good job!", data.status, "success");
	// alert(data.status);
});
@endsection
