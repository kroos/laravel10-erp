<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size: 12px;">
			<thead>
				<tr>
					<th>Staff</th>
					<th>Conditional Incentive</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($incentivestaffs as $incentivestaff)
				<tr wire:key="{{ $incentivestaff->id.now() }}">
					<td>{{ $incentivestaff->username }} - {{ $incentivestaff->name }}</td>
					<td>
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Description</th>
									<th>Incentive Deduction</th>
									<th>Ops</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($incentivestaff->belongstomanycicategoryitem()?->get() as $k => $v)
									<tr wire:key="{{ $v->id.now() }}">
										<td>{{ $k + 1 }}</td>
										<td>{!! nl2br($v->description) !!}</td>
										<td>MYR {{ $v->point }}</td>
										<td>
											<button type="button" class="btn btn-sm btn-outline-secondary text-danger" wire:click="delstaffitem('{!! $incentivestaff->id.'_'.$v->id !!}')" wire:confirm="Are you sure?">
												<i class="fa-solid fa-trash-can fa-beat"></i>
											</button>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

</div>
