@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Attendance Upload</h4>

	{{ Form::open(['route' => ['attendanceupload.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
	<div class="form-group row m-2 {{ $errors->has('softcopy') ? 'has-error' : '' }}">
			{{Form::label('softcopy', 'Excel File', ['class' => 'col-form-label col-sm-2'])}}
		<div class="col-md-10">
			{!! Form::file('softcopy', ['class' => 'form-control form-control-sm', 'id' => 'softcopy', 'aria-describedby' => 'progressbar1']) !!}
		</div>
	</div>
	<div id="progressbar1" class="form-text text-center">Upload File Progress</div>
	<div id="progressBar" class="progress" role="progressbar" aria-label="Progress Bar with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
		<div class="progress-bar percent_upload" id="percent" style="width: 0%">0% Uploading file/s</div>
	</div>
	<div id="uploadStatus" class="col-sm-12 d-flex justify-content-center"></div>
	<div class="row mt-3">
		<div class="col-md-12 text-center">
			{!! Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
		</div>
	</div>
	{!! Form::close() !!}
	<p>&nbsp;</p>
	<?php
	use Illuminate\Http\Request;
	// echo $batch;
	?>
	@if( request()->id || session()->exists('lastBatchIdAttPop') )
		<div id="processcsv" class="row col-sm-12">
			<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="{{ $batch?->progress() }}" aria-valuemin="0" aria-valuemax="100">
				<div class="col-sm-auto progress-bar csvprogress" style="width: 0%">0% Processing...</div>
			</div>
		</div>
		<div id="uploadStatus" class="col-sm-12 text-center">
			<span id="processedJobs">{{ $batch->processedJobs() }}</span> completed out of {{ $batch->totalJobs }} process
		</div>
	@endif
</div>
@endsection

@section('js')
// this form will send twice thus the job batches will be twice also due to bootstrap validator and laravel validator.
// so need to use custome validation. just dont use any 3rd party validation method as it will send its own form instead of our ajax as below
/////////////////////////////////////////////////////////////////////////////////////////

@if( request()->id || session()->exists('lastBatchIdAttPop') )
	<?php $batchId = $request->id ?? session()->get('lastBatchIdAttPop'); ?>
	setInterval(percent, 100);
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
					session()->forget('lastBatchIdAttPop');
					?>
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// console.log(textStatus, errorThrown);
			}
		})
	}
@endif

// File upload via Ajax
$(document).on('submit', '#form', function(evnt){
	if ( !$('#softcopy').val() ) {
		swal.fire('Error!', 'No file upload','error')
		.then(function(){
			window.location.reload(true);
		});
	} else {
		evnt.preventDefault();
		$.ajax({
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						// Declaring JavaScript global variable within function
						window.percentComplete = ((evt.loaded / evt.total) * 100);
						$('#progressBar').attr('aria-valuenow', percentComplete).css('width', percentComplete+'%');
						$(".percent_upload").width(percentComplete.toPrecision(4) + '%');
						$(".percent_upload").html(percentComplete.toPrecision(4) +'%');
					}
				}, false);
				return xhr;
			},
			type: 'POST',
			url: '{{ route('attendanceupload.store') }}',
			contentType: false,
			cache: false,
			processData:false,
			data: new FormData(this),
			beforeSend: function(){
				$(".progress-bar").width('0%');
				$('#uploadStatus').html('<i class="fa-solid fa-spinner fa-spin-pulse fa-beat-fade"></i>');
			},
			success: function(jqXHR, resp, errorThrown){
				// console.log(jqXHR, resp, errorThrown);
				window.location.replace(jqXHR);					// redirect action
			},
			error: function(resp){
				const res = resp.responseJSON;
				swal.fire('Error!', res.message,'error')
				.then(function(){
					window.location.reload(true);
				});
			},
		});

	}
});

// File type validation
$('#softcopy').change(function(){
	// var allowedTypes = ['application/vnd.ms-excel', 'application/pdf', 'application/msword', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
	var allowedTypes = ['application/vnd.ms-excel'];
	var file = this.files[0];
	var fileType = file.type;
	if(!allowedTypes.includes(fileType)){
		// alert('Please select a valid file (PDF/DOC/DOCX/JPEG/JPG/PNG/GIF).');
		swal.fire('Error!', 'Please select a valid file (CSV, XLS OR XLSX file/s only)','error')
		.then(function(){
			window.location.reload(true);
		});
		$("#softcopy").val('');
		return false;
	}
});


// VALIDATOR
// $('#form').bootstrapValidator({
// 	fields: {
// 		softcopy: {
// 			validators: {
// 				notEmpty: {
// 					message: 'Please upload Excel File field',
// 				},
// 				file: {
// 					extension: 'xls,xlsx,csv', // no space
// 					type: 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // no space
// 					message: 'The selected file is not valid. Please use csv, xls or xlsx file format.'
// 				},
// 			}
// 		},
// 	}
// });

@endsection
