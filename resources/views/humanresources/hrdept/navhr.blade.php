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
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendanceabsentindicator.index') }}"><i class="fa-regular fa-calendar-xmark fa-beat" style="color: #ff0000;"></i> Absent Attendance Indicator</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendancedailyreport.index') }}"><i class="bi bi-calendar-x"></i> Attendance Daily Report</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendancereport.create') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance Report</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendanceupload.create') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance Upload</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendanceremark.index') }}"><i class="fa-regular fa-calendar fa-beat"></i> Attendance Remarks</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('excelreport.create') }}"><i class="fa-regular fa-file-excel fa-beat"></i> Generate Payslip Excel Report</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('attendancepayslipexcelsetting.create') }}"><i class="fa-regular fa-file-excel fa-beat"></i> Generate Payslip Excel Setting Report</a></li>

		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Leave</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrleave.index') }}"><i class="fa-solid fa-person-walking-arrow-right fa-beat"></i> Leave List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrleave.reject') }}"><i class="fa-solid fa-eject fa-beat"></i> Rejected Leave List</a></li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrleave.cancel') }}">
					<i class="fa-stack fa-2X">
						<i class="fa-solid fa-person-walking-arrow-right fa-beat fa-stack-1x"></i>
						<i class="fa-solid fa-ban fa-stack-1x" style="color:Tomato"></i>
					</i>
				Cancel Leave List
				</a>
			</li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('rleave.index') }}"><i class="fa-solid fa-briefcase fa-beat"></i> Replacement Leave List</a></li>
<!-- 			<li><a class="dropdown-item" href="#">Staff Leave</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Leave Approval</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('leaveapprovalsupervisor.index') }}"><span class="mdi mdi-account-supervisor-outline"></span> Supervisor Leave Approval</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('leaveapprovalhod.index') }}"><span class="mdi mdi-human-capacity-increase"></span> Head of Department Leave Approval</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('leaveapprovaldirector.index') }}"><span class="mdi mdi-human-male-board-poll"></span> Director Leave Approval</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('leaveapprovalhr.index') }}"><i class="fa-solid fa-users-viewfinder fa-beat"></i> Human Resource Leave Approval</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Overtime</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('overtime.index') }}"><i class="fa-solid fa-cloud-moon fa-beat"></i> Overtime List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('overtimereport.index') }}"><i class="fa-solid fa-cloud-moon fa-beat"></i> Overtime Report</a></li>
<!-- 		<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li> -->
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Outstation</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('outstation.index') }}"><i class="fa-solid fa-person-walking-luggage fa-beat"></i> Outstation List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hroutstationattendance.index') }}"><i class="fa-solid fa-person-circle-plus"></i> Outstation Attendance List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('outstationduration.index') }}"><i class="fa-regular fa-clock"></i> Outstation Duration</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Entitlement</a>
		<ul class="dropdown-menu">
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrannualleave.index') }}">
					<i class="fa-regular fa-calendar fa-beat"></i>
					Annual Leave List
				</a>
			</li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrmcleave.index') }}">
					<span class="fa-stack fa-2X">
						<i class="fa-regular fa-calendar fa-beat fa-stack-1x"></i>
						<i class="fa-regular fa-moon fa-beat fa-stack-1x"></i>
					</span>
					Medical Certificate Leave List
				</a>
			</li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrmaternityleave.index') }}">
					<i class="fa-solid fa-person-pregnant fa-beat"></i>
					Maternity Leave List
				</a>
			</li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrreplacementleave.index') }}">
					<i class="fa-solid fa-arrow-rotate-right fa-rotate-270"></i>
					Replacement Leave List
				</a>
			</li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hruplleave.index') }}">
					<i class="fa-stack fa-2X">
						<i class="fa-regular fa-money-bill-1 fa-stack-1x fa-beat"></i>
						<i class="fa-solid fa-xmark fa-stack-1x fa-beat" style="color:Tomato"></i>
					</i>
					Unpaid Leave List
				</a>
			</li>
			<li>
				<a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('hrmcuplleave.index') }}">
					<i class="fa-stack fa-2X">
						<i class="fa-regular fa-money-bill-1 fa-stack-1x fa-beat"></i>
						<i class="fa-regular fa-moon fa-beat fa-stack-1x" style="color:Tomato"></i>
					</i>
					Unpaid Medical Certificate Leave List
				</a>
			</li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Appraisal</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('appraisallist.index') }}"><i class="fa-solid fa-list-check"></i> Appraisal List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('appraisalapoint.index') }}"><i class="fa-solid fa-user-xmark"></i> Appraisal Apoint</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('appraisalform.index') }}"><i class="fa-solid fa-user-xmark"></i> Appraisal Form</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('appraisalexcelreport.create') }}"><i class="fa-solid fa-award"></i> Appraisal Point</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('appraisalsetting.create') }}"><i class="fa-solid fa-wrench fa-beat"></i> Appraisal Point Settings</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Conditional Incentive</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('cicategory.index') }}"><i class="fa-solid fa-chart-line"></i> Incentive Category</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('cicategorystaff.index') }}"><i class="fa-solid fa-people-line fa-beat"></i> Staff Incentive Category Item</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('cicategorystaffcheck.index') }}"><i class="fa-solid fa-person-circle-check fa-beat"></i> Incentive Checking</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Discipline</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('discipline.index') }}"><i class="fa-solid fa-user-xmark"></i> Discipline List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('absent.index') }}"><i class="fa-solid fa-person-circle-exclamation fa-beat"></i> Absent List</a></li>
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
