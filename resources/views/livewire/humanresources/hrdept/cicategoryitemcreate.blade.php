<div>
	<form wire:submit.prevent="store">
		<div class="row m-2 @error('ci_category_id') is-invalid @enderror">
			<label for="catId" class="form-label col-sm-3">Category : </label>
			<div class="col-sm-9">
				<select id="catId" class="form-select form-select-sm col-sm-5 @error('ci_category_id') is-invalid @enderror" aria-describedby="in0" wire:model.change="ci_category_id">
					<option value="">Please choose</option>
					@foreach ($cat as $k => $v)
						<option value="{{ $v->id }}">{{ $v->category }}</option>
					@endforeach
				</select>
				@error('ci_category_id') <div id="in0" class="invalid-feedback">{{ $message }}</div> @enderror
			</div>
		</div>

		<div class="row m-2 @error('description') is-invalid @enderror">
			<label for="desc" class="form-label col-sm-3">Item Category Description : </label>
			<div class="col-sm-9">
				<textarea id="desc" cols="30" rows="10" class="form-control form-control-sm col-sm-auto @error('description') is-invalid @enderror" wire:model.change="description"></textarea>
				@error('description') <div id="in1" class="invalid-feedback">{{ $message }}</div> @enderror
			</div>
		</div>

		<div class="row m-2 @error('point') is-invalid @enderror">
			<label for="desc" class="form-label col-sm-3">Item Category Incentive Deduction : </label>
			<div class="col-sm-9">
				<input type="number" class="form-control form-control-sm col-sm-auto @error('point') is-invalid @enderror" id="desc" aria-describedby="in2" wire:model.change="point">
				@error('point') <div id="in2" class="invalid-feedback">{{ $message }}</div> @enderror
			</div>
		</div>

		<div class="offset-sm-3 col-sm-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary m-2">Submit</button>
		</div>
	</form>
</div>

@script
<script>
jQuery.noConflict ();
	(function($){
		$('#catId').select2({
			placeholder: 'Please Select',
			// width: '100%',
			allowClear: true,
			closeOnSelect: true,
		});

/////////////////////////////////////////////////////////////////////////////////////////
//tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#category').DataTable({
	"paging": true,
	"lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
	"order": [ 2, 'desc' ], // sorting the column descending
	responsive: true
}).on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

	})(jQuery);
</script>
@endscript