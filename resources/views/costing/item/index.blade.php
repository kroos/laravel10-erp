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
				Quotation
				<a href="{{ route('quot.create') }}" class="btn btn-primary float-right">Add Quotation</a>
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
						<a class="nav-link active" href="{{ route('quotItem.index') }}">Product / Item</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotItemAttrib.index') }}">Product / Item Attribute</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('quotUOM.index') }}">Unit Of Measurement</a>
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

				@include('quotation.item._content')
			</div>
		</div>

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
// table
// $.fn.dataTable.moment( 'ddd, D MMM YYYY' );
$("#mmodel").DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[1, "asc" ]],	// sorting the 2nd column ascending
	// responsive: true
});

/////////////////////////////////////////////////////////////////////////////////////////
// item disable
$(document).on('click', '.toggle', function(e){
	
	var Id = $(this).data('id');
	var val = $(this).data('value');
	SwalToggle(Id, val);
	e.preventDefault();
});

function SwalToggle(Id, val){
	swal({
		title: 'Toggle Product / Item Enable or Disable',
		text: "Toggle this Product?",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Toggle',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'PATCH',
					url: '{{ url('quotItem') }}' + '/' + Id + '/updatetoggle',
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
							active: val,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal('Deleted!', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					//$('#disable_user_' + Id).parent().parent().remove();
				})
				.fail(function(){
					swal('Oops...', 'Something went wrong with ajax !', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal('Cancelled', 'Data Unchanged', 'info')
		}
	});
}

/////////////////////////////////////////////////////////////////////////////////////////
// item disable
$(document).on('click', '.delete_item', function(e){
	
	var Id = $(this).data('id');
	SwalDelete(Id);
	e.preventDefault();
});

function SwalDelete(Id){
	swal({
		title: 'Delete Item',
		text: "Delete this item?",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Toggle',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'DELETE',
					url: '{{ url('quotItem') }}' + '/' + Id,
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
					},
					dataType: 'json'
				})
				.done(function(response){
					swal('Deleted!', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					//$('#disable_user_' + Id).parent().parent().remove();
				})
				.fail(function(){
					swal('Oops...', 'Something went wrong with ajax !', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal('Cancelled', 'Data Unchanged', 'info')
		}
	});
}

/////////////////////////////////////////////////////////////////////////////////////////
@endsection

