<div>
	<form wire:submit.prevent="store">
		<div class="row m-2 @error('staff_id') is-invalid @enderror">
			<label for="cat" class="form-label col-sm-3">Staff : </label>
				<select wire:model.change="staff_id" class="form-select form-select-sm col-sm-auto @error('staff_id') is-invalid @enderror" id="cat" aria-describedby="in1" placeholder="Please choose">
					@foreach ($staffs as $staff)
						<option value="{{ $staff->id }}">{{ $staff->name }}</option>
					@endforeach
				</select>
				@error('staff_id') <div id="in1" class="invalid-feedback">{{ $message }}</div> @enderror
		</div>

		<div class="offset-sm-3 col-sm-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary m-2">Submit</button>
		</div>
	</form>
</div>
