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
						@livewire('humanresources.hrdept.cicategoryrow', ['cicategory' => $cicategory], key($cicategory->id))
					@endforeach
				@endif
			</tbody>
		</table>
	</div>
</div>
