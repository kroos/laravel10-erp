<div class="card">
	<div class="card-header">
		UOM Delivery Date Period List
		<a href="{!! route('quotdd.create') !!}" class="btn btn-primary float-right">Add UOM Delivery Date Period</a>
	</div>
	<div class="card-body">
		<table class="table table-sm table-hover" style="font-size: 12px" id="mmodel">
			<thead>
				<tr>
					<th>ID</th>
					<th>UOM Period</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
@foreach(\App\Model\QuotDeliveryDate::all() as $k)
				<tr>
					<td>{{ $k->id }}</td>
					<td>{{ $k->delivery_date_period }}</td>
					<td>
						<div class="row">
							<a href="{!! route('quotdd.edit', $k->id) !!}" title="Update"><i class="far fa-edit"></i></a>
							<span class="text-danger delete_model" data-id="{!! $k->id !!}" title="Delete"><i class="far fa-trash-alt"></i></span>
						</div>
					</td>
				</tr>
@endforeach
			</tbody>
		</table>
	</div>
</div>