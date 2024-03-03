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
	<h2>Conditional Incentive Category</h2>

	@livewire('HumanResources.HRDept.CICategory')


</div>
@endsection


@section('js')

@endsection


@section('nonjquery')

@endsection
