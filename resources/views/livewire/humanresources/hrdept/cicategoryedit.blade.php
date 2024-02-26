<div>
	@if(session()->has('success'))
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			{{ session('success') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" asria-label="Close"></button>
		</div>
	@endif

	<form wire:submit.prevent="update">
		<div class="row m-2 @error('category') is-invalid @endif">
			<label for="cat" class="form-label col-sm-4">Category : </label>
			<div class="col-sm-auto">
				<input type="text" class="form-control @error('category') is-invalid @endif" id="cat" aria-describedby="in1" value="" wire:model="category">
				@error('category') <div id="in1" class="invalid-feedback">{{ $message }}</div> @endif
			</div>
		</div>

		<div class="offset-sm-4 col-sm-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary m-2">Submit</button>
		</div>
	</form>
</div>
