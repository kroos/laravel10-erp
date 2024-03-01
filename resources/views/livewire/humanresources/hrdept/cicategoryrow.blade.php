<div>
	<tr>
		<td class="scope">{{ $cicategory->id }}</td>
		<td class="scope">{{ $cicategory->category }}</td>
		<td>
			@livewire('humanresources.hrdept.cicategoryitem', ['cicategory' => $cicategory])
		</td>
		<td class="scope">
			<a wire:navigate href="{{ route('cicategory.edit', $cicategory->id) }}" class="btn btn-sm btn-outline-secondary">
				<i class="fa-regular fa-pen-to-square fa-beat"></i>
			</a>
			<button
				type="button"
				class="btn btn-sm btn-outline-secondary text-danger"
				wire:click="del({{$cicategory->id}})"
				wire:confirm="Are you sure?"
			>
				<i class="fa-solid fa-trash-can fa-beat"></i>
			</button>
		</td>
	</tr>
</div>
