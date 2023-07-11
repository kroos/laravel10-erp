{{-- <nav class="nav nav-underline justify-content-between"> --}}
<nav class="nav nav-underline justify-content-center">
	@auth
		<a class="nav-item nav-link link-body-emphasis active"  href="#">Human Resource</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Technology</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Design</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Culture</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Business</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Politics</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Opinion</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Science</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Health</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Style</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Travel</a>
	@else
		<a class="nav-item nav-link link-body-emphasis" href="#">Announcement</a>
		<a class="nav-item nav-link link-body-emphasis" href="#">Scan Job</a>
	@endauth
</nav>