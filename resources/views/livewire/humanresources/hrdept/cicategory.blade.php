<div>
	<div class="table-responsive m-1">
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
					@foreach($cicategories as $cicategory)
						<tr wire:key="{{ $cicategory->id }}">
							<td class="scope">{{ $cicategory->id }}</td>
							<td class="scope">{{ $cicategory->category }}</td>
							<td>
								@livewire('humanresources.hrdept.cicategoryitem', ['cicategory' => $cicategory], key($cicategory->id))
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
