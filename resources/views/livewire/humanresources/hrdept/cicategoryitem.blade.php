<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size: 12px;">
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
							<td class="w-75" {!! ($item->description)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$item->description.'"':null !!}>
								{{-- {{ Str::limit($item->description, 9, ' >') }} --}}
								{{ $item->description }}
							</td>
							<td>RM{{ $item->point }}</td>
							<td>
								<a href="{{ route('cicategoryitem.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="fa-regular fa-pen-to-square fa-beat"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary text-danger" wire:click="deltem({{$item->id}})" wire:confirm="Are you sure?">
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
