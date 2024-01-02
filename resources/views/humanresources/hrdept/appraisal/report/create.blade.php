@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<div class="col-sm-12 row">
		{{ Form::open(['route' => ['appraisalexcelreport.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}

		<div class="form-group row m-2 {{ $errors->has('year') ? 'has-error' : '' }}">
			{{ Form::label('year', 'Appraisal Report Year :', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8">
				<input name="year" id="year" type="text" class="form-control form-control-sm col-sm-8" placeholder="Year" />
			</div>
		</div>

		<div class="form-group row m-3">
			<div class="col-sm-8 offset-sm-4">
				{!! Form::button('Appraisal Report', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
			</div>
		</div>
		{{ Form::close() }}
	</div>

<?php
use Illuminate\Http\Request;
?>
@if( request()->id || session()->exists('lastBatchId') )

	<div id="processcsv" class="row col-sm-12">
		<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="{{ $batch->progress() }}" aria-valuemin="0" aria-valuemax="100">
			<div class="col-sm-auto progress-bar csvprogress" style="width: 0%">0% CSV Processing</div>
		</div>
	</div>
	<div id="uploadStatus" class="col-sm-auto ">
		<span id="processedJobs">{{ $batch->processedJobs() }}</span> completed out of {{ $batch->totalJobs }} process
	</div>
@endif


</div>
@endsection
@section('js')
@if( request()->id || session()->exists('lastBatchId') )
	<?php
	$batchId = $request->id ?? session()->get('lastBatchId');
	?>
	setInterval(percent, 500);
	function percent() {
		$.ajax({
			url: '{{ route('progress', ['id' => $batchId]) }}',
			type: "GET",
			data: { _token: '{{ csrf_token() }}'},
			dataType: 'json',
			success: function (response) {
				window.percentbar = response.progress;
				$('.progress').attr('aria-valuenow', percentbar).css('width', percentbar + '%');
				$(".csvprogress").width(percentbar + '%');
				$(".csvprogress").html(percentbar +'%');
				$('#processedJobs').html(response.processedJobs);
				console.log(percentbar);
				if (percentbar == 100) {
					clearInterval(percent);
					window.location.replace('{{ route('appraisalexcelreport.create') }}');
					<?php
					session()->forget('lastBatchId');
					?>
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		})
	}
@endif

// File upload via Ajax
$("#form").on('submit', function(e){
	e.preventDefault();
	$.ajax({
		type: 'POST',
		url: '{{ route('appraisalexcelreport.store') }}',
		data: new FormData(this),
		contentType: false,
		cache: false,
		processData:false,
		beforeSend: function(){
			$(".progress-bar").width('0%');
			$('#uploadStatus').html('<i class="fa-solid fa-spinner fa-spin-pulse fa-beat-fade"></i>');
		},
		error: function(resp){
			const res = resp.responseJSON;
			swal.fire('Error!', res.message,'error')
			.then(function(){
				window.location.reload(true);
			});
		},
		success: function(jqXHR, resp, errorThrown){
			// console.log(jqXHR, resp, errorThrown);
			window.location.replace(jqXHR);					// redirect action
		}
	});
});
@endsection

