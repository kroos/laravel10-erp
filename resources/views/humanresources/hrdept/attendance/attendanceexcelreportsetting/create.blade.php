@extends('layouts.app')
@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Generate Payslip Excel Setting</h4>

	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Description</th>
					<th>Value 1</th>
				</tr>
			</thead>
			<tbody>
				@foreach($settings as $setting)
					<tr>
						<td>{{ $setting->description }}</td>
						<td>
							<input type="number" id="{{ $setting->id }}_setting" step="0.25" name="value" class="col-auto form-control form-control-sm" placeholder="Value" value="{{ $setting->value }}" data-id="{{ $setting->id }}">
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('js')
////////////////////////////////////////////////////////////////////////////////////
$('#1_setting,#2_setting,#3_setting,#4_setting,#5_setting').change(function() {
	$.ajax({
		url: "{{ url('attendancepayslipexcelsetting/update') }}/" + $(this).data('id'),
		type: "PATCH",
		data : {
					id: $(this).data('id'),
					value: $(this).val(),
					_token: '{!! csrf_token() !!}',
				},
		dataType: 'json',
		global: false,
		async:false,
		success: function (response) {
			// console.log(response);
			swal.fire("Good job!", response.message, response.status);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// console.log(textStatus, errorThrown);
			swal.fire("Ooopss! Something wrong!", errorThrown, textStatus);
		}
	})
});
@endsection
