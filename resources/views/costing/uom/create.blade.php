@extends('layouts.app')

@section('content')
<div class="card">
	<div class="card-header"><h1>Costing Department</h1></div>
	<div class="card-body">
		@include('layouts.info')
		@include('layouts.errorform')

		<ul class="nav nav-tabs">
@foreach( App\Model\Division::find(3)->hasmanydepartment()->whereNotIn('id', [22, 23, 24])->get() as $key)
			<li class="nav-item">
				<a class="nav-link {{ ($key->id == 7)? 'active' : 'disabled' }}" href="{{ route("$key->route.index") }}">{{ $key->department }}</a>
			</li>
@endforeach
		</ul>

		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a class="nav-link active" href="{{ route('quot.index') }}">Quotation</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="{{ route('ics.costing') }}">Intelligence Customer Service</a>
			</li>
		</ul>

		<div class="card">
			<div class="card-header">
				Add UOM
			</div>
			<div class="card-body">

				<ul class="nav nav-tabs">
					<li class="nav-item">
						<a class="nav-link" href="{{ route('customer.index') }}">Customer</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('machine_model.index') }}">Model</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotdd.index') }}">UOM Delivery Date Period</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotItem.index') }}">Product / Item</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotItemAttrib.index') }}">Product / Item Attribute</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="{{ route('quotUOM.index') }}">Unit Of Measurement</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotRem.index') }}">Remarks</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotExcl.index') }}">Exclusion</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotDeal.index') }}">Dealer</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotWarr.index') }}">Warranty</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotBank.index') }}">Bank</a>
					</li>
				</ul>

{!! Form::open(['route' => ['quotUOM.store'], 'id' => 'form', 'files' => true]) !!}
	@include('quotation.uom._form')
{{ Form::close() }}
		
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
//ucwords
$(document).on('keyup', 'input', function () {
	// uch(this);
});

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		uom: {
			validators: {
				notEmpty: {
					message: 'UOM is required. '
				},
			}
		},
	}
});
@endsection

