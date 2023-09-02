<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Staff</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.index') }}"><i class="bi bi-person"></i> Staff List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="bi bi-person-add"></i> Add Staff</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Attendance</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="{{ route('attendance.index') }}">Attendance List</a></li>
			<li><a class="dropdown-item" href="#">Another action</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Leave</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="{{ route('hrleave.index') }}">Leave List</a></li>
			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item">
		<a class="nav-link disabled" aria-disabled="true">Disabled</a>
	</li>
</ul>

