@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Generate Excel Report</h4>
	{{ Form::open(['route' => ['excelreport.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
	<div class="row justify-content-center">
		<div class="col-sm-6 gy-1 gx-1 align-items-start">

			<div class="form-group row mb-3 {{ $errors->has('from') ? 'has-error' : '' }}">
				{{ Form::label( 'from1', 'From : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-sm-8" style="position:relative;">
					{{ Form::text('from', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'from1', 'placeholder' => 'From', 'autocomplete' => 'off']) }}
				</div>
			</div>
			<div class="form-group row mb-3 {{ $errors->has('to') ? 'has-error' : '' }}">
				{{ Form::label( 'to1', 'To : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-sm-8" style="position:relative;">
					{{ Form::text('to', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'to1', 'placeholder' => 'To', 'autocomplete' => 'off']) }}
				</div>
			</div>
		<div class="col-sm-12 offset-4 mb-6">
			{!! Form::submit('Generate Excel', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
		</div>
		</div>
	</div>
	{!! Form::close() !!}
<?php
use Illuminate\Http\Request;
?>
	@if( request()->id && session()->exists('lastBatchId') )

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
$('#from1').datetimepicker({
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
	useCurrent: false,
	maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change dp.update', function(e) {
	$('#form').bootstrapValidator('revalidateField', "from");
	$('#to1').datetimepicker('minDate', $('#from1').val());
});

$('#to1').datetimepicker({
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
	useCurrent: false,
	maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
})
.on('dp.change dp.update', function(e) {
	$('#form').bootstrapValidator('revalidateField', "to");
	$('#from1').datetimepicker('maxDate', $('#to1').val());
});

/////////////////////////////////////////////////////////////////////////////////////////
@if( request()->id && session()->exists('lastBatchId') )
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

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: 'fas fa-light fa-check',
			invalid: 'fas fa-sharp fa-light fa-xmark',
			validating: 'fas fa-duotone fa-spinner-third'
		},
		fields: {
			from: {
				validators: {
					notEmpty: {
						message: 'Please insert date '
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'Invalid date '
					},
				}
			},
			to: {
				validators: {
					notEmpty: {
						message: 'Please insert date '
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'Invalid date '
					},
				}
			},
		}
	})
});
@endsection

@section('nonjquery')
@endsection
