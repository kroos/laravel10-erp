<div class="card">
	<div class="card-header">
		Bank Clause
		<a href="{!! route('quotBank.create') !!}" class="btn btn-primary float-right">Add Bank Clause</a>
	</div>
	<div class="card-body">
		<table class="table table-sm table-hover" style="font-size: 12px" id="mmodel">
			<thead>
				<tr>
					<th>ID</th>
					<th>Bank Clause</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
@foreach(\App\Model\QuotBank::all() as $k)
				<tr>
					<td>{{ $k->id }}</td>
					<td>{!! nl2br($k->bank) !!}</td>
					<td class="row">
						<a href="{!! route('quotBank.edit', $k->id) !!}" title="Update"><i class="far fa-edit"></i></a>
						<!-- <span class="text-danger delete_item" data-id="{!! $k->id !!}" title="Delete"><i class="far fa-trash-alt"></i></span> -->
					</td>
				</tr>
@endforeach
			</tbody>
		</table>
	</div>
</div>