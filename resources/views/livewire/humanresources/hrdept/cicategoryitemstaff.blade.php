<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Staff</th>
					<th>Conditional Incentive</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($incentivestaffs as $incentivestaff)
				<tr wire:key="{{ $incentivestaff->id.now() }}">
					<td>{{ $incentivestaff->name }}</td>
					<td>
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Description</th>
									<th>Incentive Deduction</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($incentivestaff->belongstomanycicategoryitem()?->get() as $k => $v)
									<tr wire:key="{{ $v->id.now() }}">
										<td>{{ $k + 1 }}</td>
										<td>{!! nl2br($v->description) !!}</td>
										<td>MYR {{ $v->point }}</td>
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
