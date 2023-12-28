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

	<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
		<div class="col-sm-auto progress-bar csvprogress" style="width: 0%">0% CSV Processing</div>
		<div id="uploadStatus" class="col-sm-auto "></div>
	</div>

</div>

@endsection
@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@if( request()->id && session()->exists('lastBatchId') )
	<?php $batchId = $request->id ?? session()->get('lastBatchId'); ?>
	// setInterval(percent, 5);
	function percent() {
		window.percentbar = {!! session()->get('progress') !!};
		console.log(percentbar);
		$('.progress').attr('aria-valuenow', percentbar).css('width', percentbar + '%');
		$(".csvprogress").width(percentbar.toPrecision(4) + '%');
		$(".csvprogress").html(percentbar.toPrecision(4) +'%');
		if (percentbar == 100) {
			clearInterval(percent);
			// window.location.replace('{{ url('/') }}');
			<?php session()->forget(['lastBatchId', 'totalJobs', 'pendingJobs', 'processedJobs', 'progress', 'finished']) ?>
		}
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
			console.log([jqXHR, resp, errorThrown]);
			//if (percentComplete == 100) {
				// window.location.reload(true);
				window.location.replace(jqXHR);					// redirect action
			//}
		}
	});
});

@endsection

