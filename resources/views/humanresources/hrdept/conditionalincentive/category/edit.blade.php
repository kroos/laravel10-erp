@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Edit Conditional Incentive Category</h2>

	@livewire('HumanResources.HRDept.CICategoryEdit', ['cicategory' => $cicategory])
</div>
	@endsection

	@section('js')
	/////////////////////////////////////////////////////////////////////////////////////////

	@endsection
