<div>
	<div class="hstack align-items-start justify-content-between">
		<div class="col-sm-5 m-3">
			<h4>Create Conditional Incentive Category</h4>
			@livewire('HumanResources.HRDept.CICategoryCreate')
		</div>
		<div class="col-sm-5 m-3">
			<h4>Create Conditional Incentive Category Item</h4>
			@livewire('HumanResources.HRDept.CICategoryItemCreate')
		</div>
	</div>
	<div class="table-responsive mt-3">
		<table class="table table-sm table-hover" id="category" style="font-size: 12px;">
			<thead>
				<tr>
					<th class="scope">ID</th>
					<th class="scope">Category</th>
					<th class="scope">Category Item</th>
					<th class="scope">Ops</th>
				</tr>
			</thead>
			<tbody>
				@if($cicategories->count())
					@foreach($cicategories as $index => $cicategory)
						<tr wire:key="{{ $cicategory->id.now() }}">
							<td class="scope">{{ $index + 1 }}</td>
							<td class="scope">{{ $cicategory->category }}</td>
							<td>
								@livewire('HumanResources.HRDept.CICategoryItem', ['cicategory' => $cicategory], key($cicategory->id.now()))
							</td>
							<td class="scope">
								<a href="{{ route('cicategory.edit', $cicategory->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary text-danger" wire:click="del({{$cicategory->id}})" wire:confirm="Are you sure?">
									<i class="fa-solid fa-trash-can fa-beat"></i>
								</button>
							</td>
						</tr>
					@endforeach
				@endif
			</tbody>
		</table>
	</div>
</div>

@script
<script>
	jQuery.noConflict ();
	(function($){
		/////////////////////////////////////////////////////////////////////////////////////////
		//tooltip
		//$(document).ready(function(){
			$('[data-bs-toggle="tooltip"]').tooltip();
		//});

		/////////////////////////////////////////////////////////////////////////////////////////
		// datatables
		$.fn.dataTable.moment( 'D MMM YYYY' );
		$.fn.dataTable.moment( 'h:mm a' );
		$('#category').DataTable({
			"paging": true,
			"lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
			// "columnDefs": [
			// 	{ type: 'date', 'targets': [2] },
			// 	{ type: 'time', 'targets': [3] },
			// ],
			"order": [ 0, 'asc' ], // sorting the column descending
			responsive: true
		}).on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
			$(document).ready(function() {
				$('[data-bs-toggle="tooltip"]').tooltip();
			});}
		);

	})(jQuery);
</script>
@endscript