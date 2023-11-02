<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-target="#main_nav" href="#" role="button" aria-expanded="false">Staff</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.index') }}"><i class="fa-solid fa-users fa-beat"></i> Staff List</a></li>
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
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendanceupload.create') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance Upload</a></li>
<!-- 		<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Overtime</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('overtime.index') }}"><i class="fa-solid fa-cloud-moon fa-beat"></i> Overtime List</a></li>
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
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Outstation</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('outstation.index') }}"><i class="fa-solid fa-person-walking-luggage fa-beat"></i> Outstation List</a></li>
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
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Setting</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrsetting.index') }}"><i class="fa-solid fa-users-gear fa-beat"></i> Setting</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('workinghour.index') }}"><i class="fa-regular fa-clock fa-beat"></i> Working Hour Configuration</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('holidaycalendar.index') }}"><i class="fa-solid fa-gifts fa-beat"></i> Holiday Calendar Configuration</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('annualleave.index') }}"><i class="fa-regular fa-calendar fa-beat"></i> Generate Annual Leave Entitlements</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('mcleave.index') }}"><i class="fa-solid fa-house-chimney-medical fa-beat"></i> Generate MC Leave Entitlements</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('maternityleave.index') }}"><i class="fa-solid fa-person-breastfeeding fa-beat"></i> Generate Maternity Leave Entitlements</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
</ul>

