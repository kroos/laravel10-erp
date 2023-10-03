<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Staff</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.index') }}"><i class="fa-solid fa-users fa-beat"></i> Staff List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Attendance</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendance.index') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendancereport.index') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance Report</a></li>
<!-- 		<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Leave</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrleave.index') }}"><i class="fa-solid fa-person-walking-arrow-right fa-beat"></i> Leave List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('rleave.index') }}"><i class="fa-solid fa-briefcase fa-beat"></i> Replacement Leave List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('rleave.create') }}"><i class="fa-solid fa-person-walking-arrow-loop-left fa-beat"></i> Add Replacement Leave</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Outstation</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="#"><i class="fa-solid fa-person-walking-luggage fa-beat"></i> Outstation List</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Discipline</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('discipline.index') }}"><i class="fa-solid fa-user-xmark"></i> Discipline List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('discipline.create') }}"><i class="fa-solid fa-clipboard-user"></i> Add Discipline</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Setting</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="#"><i class="fa-solid fa-users-gear fa-beat"></i> Setting</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
</ul>

