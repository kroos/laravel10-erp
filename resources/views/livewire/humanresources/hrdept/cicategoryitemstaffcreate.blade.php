<div>
	<form wire:submit.prevent="store">
		<div class="col-sm-12 row justify-content-between">
			<div class="col-sm-5 m-1">
				<div class="hstack m-1 @error('staff_id') has-error is-invalid @enderror">
					<label for="cat" class="form-label col-sm-3">Staff : </label>
					<div class="col-sm-9 vh-100 overflow-y-auto" >
						@foreach ($staffs as $staff)
							<div class="form-check" wire:key="{{ $staff->id.now() }}" style="font-size: 12px;">
								<input wire:model.change="staff_id" class="form-check-input" type="checkbox" value="{{ $staff->id }}" id="staffs_{{ $staff->id }}">
								<label class="form-check-label" for="staffs_{{ $staff->id }}">
									{{ $staff->username.'  '.$staff->name }}
								</label>
							</div>
						@endforeach
					</div>
				</div>
				@error('staff_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
				{{-- var_export($staff_id) --}}
			</div>

			<div class="col-sm-5 m-1">
				<div class="row m-1 @error('cicategory_item_id') has-error is-invalid @enderror">
					<div class="table-responsive mt-3">
						<table class="table table-sm table-hover" id="category" style="font-size: 12px;">
							<thead>
								<tr>
									<th>Category</th>
									<th>Category Item</th>
								</tr>
							</thead>
							<tbody>
								@if($cicategories->count())
									@foreach($cicategories as $index => $cicategory)
										<tr wire:key="{{ $cicategory->id.now() }}">
											<td>
												{{ $cicategory->category }}
											</td>
											<td>
												<div class="table-responsive">
													<table class="table table-sm table-hover" id="categoryitem" style="font-size: 12px;">
														<thead>
															<tr>
																<th>Item Description</th>
																<th>Incentive Deduct</th>
															</tr>
														</thead>
														<tbody>
															@if($cicategory->count())
																@foreach($cicategory->hasmanycicategoryitem()->get() as $item)
																	<tr wire:key="{{ $item->id.now() }}">
																		<td {!! ($item->description)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$item->description.'"':null !!}
																		>
																			<label for="categoryitem_{{ $item->id }}">
																				<input type="checkbox" value="{{ $item->id }}" id="categoryitem_{{ $item->id }}" wire:model.change="cicategory_item_id">
																				{{ Str::limit($item->description, 9, ' >') }}
																			</label>
																		</td>
																		<td>
																			<label for="categoryitem_{{ $item->id }}">
																				MYR{{ $item->point }}
																			</label>
																		</td>
																	</tr>
																@endforeach
															@endif
														</tbody>
													</table>
												</div>
											</td>
										</tr>
									@endforeach
								@endif
							</tbody>
						</table>
					</div>
				</div>
				@error('cicategory_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
				{{-- var_export($cicategory_item_id) --}}
			</div>
		</div>

		<div class="offset-sm-6 col-sm-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary m-2">Submit</button>
		</div>
	</form>
</div>
