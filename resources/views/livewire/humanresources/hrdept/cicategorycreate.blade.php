<div>
	<form wire:submit.prevent="store">
		<div class="row m-2 @error('category') is-invalid @enderror">
			<label for="cat" class="form-label col-sm-3">Category : </label>
				<input type="text" class="form-control form-control-sm col-sm-auto @error('category') is-invalid @enderror" id="cat" aria-describedby="in1" value="" wire:model.change="category">
				@error('category') <div id="in1" class="invalid-feedback">{{ $message }}</div> @enderror
		</div>

		<div class="offset-sm-3 col-sm-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary m-2">Submit</button>
		</div>
	</form>
</div>
