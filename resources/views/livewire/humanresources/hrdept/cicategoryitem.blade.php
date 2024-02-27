<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Item Description</th>
					<th>Incentive Deduct</th>
					<th>Ops</th>
				</tr>
			</thead>
			<tbody>
				@if($cicategory->count())
					@foreach($cicategory->hasmanycicategoryitem()->get() as $item)
						<tr>
							<td {!! ($item->description)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$item->description.'"':null !!}
							>
								{{ Str::limit($item->description, 9, ' >') }}
							</td>
							<td>{{ $item->point }}</td>
							<td>
								<a href="{{ route('cicategoryitem.create') }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-square-plus fa-beat"></i>
								</a>
								<a href="{{ route('cicategoryitem.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary text-danger" wire:click="deltem({{$item->id}})">
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
