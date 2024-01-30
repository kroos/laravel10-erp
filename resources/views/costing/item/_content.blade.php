<div class="card">
	<div class="card-header">
		Product / Item List
		<a href="{!! route('quotItem.create') !!}" class="btn btn-primary float-right">Add Product / Item</a>
	</div>
	<div class="card-body">
		<table class="table table-sm table-hover" style="font-size: 12px" id="mmodel">
			<thead>
				<tr>
					<th>ID</th>
					<th>Product/Item</th>
					<th>Info</th>
					<th>Price</th>
					<th>Remarks</th>
					<th>Active</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
@foreach(\App\Model\QuotItem::all() as $k)
				<tr>
					<td>{{ $k->id }}</td>
					<td>{{ $k->item }}</td>
					<td>{{ $k->info }}</td>
					<td>{{ $k->price }}</td>
					<td>{{ $k->remarks }}</td>
					<td>
						<span class="text-{!! ($k->active == 1)?'success':'danger' !!} toggle" data-id="{{ $k->id }}" data-value="{!! ($k->active == 1)?0:1 !!}" title="{!! ($k->active == 1)?'Deactivate':'Activate' !!}"><i class="fa fa-toggle-{!! ($k->active == 1)?'on':'off' !!}"></i></span>
					</td>
					<td class="row">
						<a href="{!! route('quotItem.edit', $k->id) !!}" title="Update"><i class="far fa-edit"></i></a>
						<!-- <span class="text-danger delete_item" data-id="{!! $k->id !!}" title="Delete"><i class="far fa-trash-alt"></i></span> -->
					</td>
				</tr>
@endforeach
			</tbody>
		</table>
	</div>
</div>