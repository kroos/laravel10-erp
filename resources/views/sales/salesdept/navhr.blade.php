<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Sales</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('sale.index') }}"><span class="mdi mdi-sale-outline"></span>&nbsp;&nbsp;Sales List</a></li>
		</ul>
	</li>

	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Setting</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('salescustomer.index') }}"><span class="mdi bi-people"></span>&nbsp;&nbsp;Customer</a></li>
		</ul>
	</li>
</ul>

