<div>
	<form wire:submit.prevent="store">
		<div class="row m-2 @error('ci_category_id') is-invalid @enderror">
			<label for="catId" class="form-label col-sm-3">Category : </label>
			<div class="col-sm-9">
				<select id="catId" class="form-select form-select-sm col-sm-5 @error('ci_category_id') is-invalid @enderror" aria-describedby="in0" wire:model="ci_category_id">
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