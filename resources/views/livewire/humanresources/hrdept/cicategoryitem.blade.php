<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Item Description</th>
					<th>Ops</th>
				</tr>
			</thead>
			<tbody>
				@if($cicategory->count())
					@foreach($cicategory->hasmanycicategoryitem()->get() as $item)
						<tr>
							<td>{{ $item->description }}</td>
							<td>
								<a href="{{ route('cicategory.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary text-danger" wire:click="del({{$item->id}})">
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
