<div>
	@if(session()->has('success'))
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			{{ session('success') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" asria-label="Close"></button>
		</div>
	@endif

	<form wire:submit></form>
	<div class="row m-2">
		<label for="cat" class="form-label col-sm-12">Category : </label>
		<div class="col-sm-6">
			<input type="text" class="form-control is-invalid" id="cat" aria-describedby="in1" value="" wire:model="category">
			<div id="in1" class="invalid-feedback">{{ $error->message }}</div>
		</div>
	</div>


</div>
