@extends('layouts.app')

@push('styles')
	@livewireStyles
@endpush

@push('scripts')
	@livewireScripts
@endpush

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Staff Checking Incentive</h2>

	<div class="hstack align-items-start justify-content-between">
		<div class="col-sm-12 m-3">
			@livewire('HumanResources.HRDept.CICategoryItemStaffCheckCreate')
		</div>
	</div>
</div>
@endsection