<?php
use Carbon\Carbon;
// whoami?
$me = \Auth::user()->belongstostaff;
$meloc = $me->belongstomanydepartment()->first()->branch_id;
?>
<div>
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size: 12px;">
			<thead>
				<tr>
					<th>Name</th>
					<th>Category Item</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($incentivestaffs as $k1 => $incentivestaff)
				@if ($me->authorise_id == 1 || $me->div_id == 1 || $me->div_id == 2 || $me->div_id == 5)
					<tr wire:key="{{ $incentivestaff->id.now() }}">
						<td>
							{{ $incentivestaff->username }} - {{ $incentivestaff->name }}
						</td>
						<td>
							<table class="table table-sm table-hover" style="font-size: 12px;">
								<thead>
									<tr>
										<th>#</th>
										<th>Description</th>
										<th>Ops</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($incentivestaff->belongstomanycicategoryitem()?->get() as $k2 => $v)
										<tr wire:key="{{ $v->id.$incentivestaff->id.now() }}">
											<td>{{ $k2 + 1 }}</td>
											<td class="w-50">
												{!! nl2br($v->description) !!}
											</td>
											<td>
												<table class="table table-sm table-hover" style="font-size: 12px;">
													<thead>
														<tr>
															<th>Week</th>
															<th>Date</th>
														</tr>
													</thead>
													<tbody>
														@foreach ($weeks as $k3 => $week)
															<tr wire:key="{{ $week->id.$v->id.$incentivestaff->id.now() }}">
																<td>
																	<div class="form-check">
																		<label for="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																			<input type="checkbox" value="{{ $week->id }}" wire:model.change="checked.{{ $v->pivot->id }}.{{ $incentivestaff->id }}.{{ $v->id }}.{{ $week->id }}" id="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																			{{ $week->week }}
																		</label>
																	</div>
																</td>
																<td>
																	<label for="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																		{{ Carbon::parse($week->date_from)->format('j M Y') }}-{{ Carbon::parse($week->date_to)->format('j M Y') }}
																	</label>
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
						</td>
					</tr>
				@elseif ($me->div_id == 4)
					@if ($meloc == $incentivestaff->belongstomanydepartment()->first()->branch_id)
						<tr wire:key="{{ $incentivestaff->id.now() }}">
							<td>
								{{ $incentivestaff->username }} - {{ $incentivestaff->name }}
							</td>
							<td>
								<table class="table table-sm table-hover" style="font-size: 12px;">
									<thead>
										<tr>
											<th>#</th>
											<th>Description</th>
											<th>Ops</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($incentivestaff->belongstomanycicategoryitem()?->get() as $k2 => $v)
											<tr wire:key="{{ $v->id.$incentivestaff->id.now() }}">
												<td>{{ $k2 + 1 }}</td>
												<td class="w-50">
													{!! nl2br($v->description) !!}
												</td>
												<td>
													<table class="table table-sm table-hover" style="font-size: 12px;">
														<thead>
															<tr>
																<th>Week</th>
																<th>Date</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($weeks as $k3 => $week)
																<tr wire:key="{{ $week->id.$v->id.$incentivestaff->id.now() }}">
																	<td>
																		<div class="form-check">
																			<label for="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																				<input type="checkbox" value="{{ $week->id }}" wire:model.change="checked.{{ $v->pivot->id }}.{{ $incentivestaff->id }}.{{ $v->id }}.{{ $week->id }}" id="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																				{{ $week->week }}
																			</label>
																		</div>
																	</td>
																	<td>
																		<label for="check_{{ $k1.$k2.$k3.$week->id }}" class="form-check-label">
																			{{ Carbon::parse($week->date_from)->format('j M Y') }}-{{ Carbon::parse($week->date_to)->format('j M Y') }}
																		</label>
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
							</td>
						</tr>
					@endif
				@endif
			@endforeach
			</tbody>
		</table>
	</div>
</div>
