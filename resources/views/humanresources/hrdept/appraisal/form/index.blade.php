@extends('layouts.app')

@section('content')

<style>
	/* div {
		border: 1px solid red;
	} */
</style>


<div class="container">
	@include('humanresources.hrdept.navhr')

	<h4>Appraisal Form</h4>

	<div class="row">&nbsp;</div>

	@foreach ($departments as $department)
	<div class="row" style="background-color: #f0f0f0; font-size: 20px;">
		<div class="col-sm-12">
			<a class="btn btn-primary btn-sm" href="{{ route('appraisalform.create', ['id' => $department->id]) }}" role="button" >+</a> 
			{{ $department->department }}
		</div>
	</div>
	<div class="row">

	</div>
	<div class="row">&nbsp;</div>
	@endforeach

</div>
@endsection

@section('js')

@endsection