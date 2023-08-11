{{-- <nav class="nav nav-underline justify-content-between"> --}}
<nav class="nav nav-underline justify-content-center">
	@auth
		<a class="nav-item nav-link link-body-emphasis active" href="{{ route('hrdept.index') }}">Human Resource</a>
	@else
		<a class="nav-item nav-link link-body-emphasis" href="#">Announcement</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Scan Job</a>
	@endauth
</nav>
