<div class="card">
	<div class="card-header">
		Product Attribute
		<a href="{!! route('quotItemAttrib.create') !!}" class="btn btn-primary float-right">Add Product Attribute</a>
	</div>
	<div class="card-body">
		<table class="table table-sm table-hover" style="font-size: 12px" id="mmodel">
			<thead>
				<tr>
					<th>ID</th>
					<th>Product Attribute</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
@foreach(\App\Model\QuotItemAttribute::all() as $k)
				<tr>
					<td>{{ $k->id }}</td>
					<td>{{ $k->attribute }}</td>
					<td class="row">
						<a href="{!! route('quotItemAttrib.edit', $k->id) !!}" title="Update"><i class="far fa-edit"></i></a>
						<!-- <span class="text-danger delete_item" data-id="{!! $k->id !!}" title="Delete"><i class="far fa-trash-alt"></i></span> -->
					</td>
				</tr>
@endforeach
			</tbody>
		</table>
	</div>
</div>