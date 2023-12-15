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

	<!-- <div id="processcsv" class="row col-sm-12"> -->
@if(\App\Models\JobBatch::count() )
<!-- 		<div class="progress col-sm-12" role="progressbar" aria-label="CSV Processing" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
			<div class="progress-bar csvprogress" style="width: 0%">0% CSV Processing</div>
		</div>
		<p>&nbsp;</p>
		<div class="table-responsive">
			<table class="table table-hover table-sm">
				<thead>
					<tr>
						<th>File</th>
						<th>Pending</th>
						<th>Success</th>
						<th>Failed</th>
					</tr>
				</thead>
				<tbody class=" table-group-divider">
 -->@foreach(\App\Models\JobBatch::all() as $v)
<!-- 					<tr>
						<td>{{ $v->name }}</td>
						<td>{{ ($v->pending_jobs == 0)?'No Pending':'Pending' }}</td>
						<td>
							@if($v->pending_jobs == 0 && $v->failed_jobs == 0)
								Success
							@else
								@if($v->pending_jobs > 0 && $v->failed_jobs == 0)
									Not Yet Process
								@else
									@if($v->pending_jobs == 0 && $v->failed_jobs > 0)
										Process With Fail
									@endif
								@endif
							@endif
						</td>
						<td>{{ ($v->failed_jobs == 0)?'No Failed':'Failed' }}</td>
					</tr>
 -->@endforeach
<!-- 				</tbody>
			</table>
		</div>
 -->@endif
	<!-- </div> -->
</div>

@endsection
@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@if( request()->id && session()->exists('lastBatchId') )
	<?php $batchId = $request->id ?? session()->get('lastBatchId'); ?>
	setInterval(percent, 5);
	function percent() {
		$.ajax({
			url: '{{ route('progress', ['id' => $batchId]) }}',
			type: "GET",
			data: { _token: '{{ csrf_token() }}'},
			dataType: 'json',
			success: function (response) {
				// var resp = response.responseJSON;
				// return resp;
				var total = parseInt(response.total_jobs);
				var pending = parseInt(response.pending_jobs);
				var job_done = parseInt(total - pending);
				window.percentbar = ((job_done / total) * 100);
				$('.progress').attr('aria-valuenow', percentbar).css('width', percentbar + '%');
				$(".csvprogress").width(percentbar.toPrecision(4) + '%');
				$(".csvprogress").html(percentbar.toPrecision(4) +'%');
				console.log(percentbar);
				if (percentbar == 100) {
					clearInterval(percent);
					window.location.replace('{{ url('/') }}');
					<?php session()->forget('lastBatchId') ?>
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// console.log(textStatus, errorThrown);
			}
		})
	}
@endif
@endsection

