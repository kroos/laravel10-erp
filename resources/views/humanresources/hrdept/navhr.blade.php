<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Staff</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.index') }}"><i class="bi bi-person"></i> Staff List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="bi bi-person-add"></i> Add Staff</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Attendance</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendance.index') }}"><i class="fa-regular fa-calendar"></i> Attendance List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendancereport.index') }}"><i class="fa-regular fa-calendar"></i> Attendance Report</a></li>
<!-- 		<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Leave</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrleave.index') }}">Leave List</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Outstation</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="#">Outstation List</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Setting</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="#">Setting</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
</ul>

