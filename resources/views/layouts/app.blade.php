<!doctype html>
<html lang="en" data-bs-theme="auto">
<?php
use \Carbon\Carbon;

$currentYear = Carbon::now()->year;
?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="" type="image/x-icon" rel="icon" />
	<meta name="description" content="">
	<title>{!! config('app.name') !!}</title>
	<link href="{{ asset('images/logo.png') }}" type="image/x-icon" rel="icon" />
	<meta name="keywords" content="erp system, erp" />
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<!-- Styles -->
	<link href="{{ URL::asset('css/app.css') }}" rel="stylesheet">
	<!-- @livewireStyles -->
</head>
<body class="container-fluid flex align-items-start justify-content-center">
	<div class="container ">
		<header class="border-bottom lh-1 py-3">
			<!-- navigator -->
			<nav class="navbar navbar-expand-lg bg-body-tertiary">
				<div class="container-fluid">
					<a class="navbar-brand" href="{{ url('/') }}"> <img src="{{ asset('images/logo.png') }}" class="img-fluid rounded" alt="Home" width="40%"> </a>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor04" aria-controls="navbarColor04" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarColor04">
						<ul class="navbar-nav me-auto">
							<li class="nav-item">
								<a class="nav-link active" href="{{ url('/') }}">Home
									<span class="visually-hidden">(current)</span>
								</a>
							</li>
						</ul>
						@if (Route::has('login'))
							@auth
								<div class="dropdown">
									<a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ Auth::user()->belongstostaff->name }}</a>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="{{ route('profile.show', Auth::user()->belongstostaff->id) }}"><i class="fa-regular fa-user"></i> Profile</a></li>
										<li><a class="dropdown-item" href="#"><i class="fa-regular fa-comment"></i> Notifications</a></li>
										<!-- <li><a class="dropdown-item" href="{{ route('holidaycalendar.show', $currentYear) }}"><i class="fa-regular fa-calendar"></i> Holiday</a></li> -->
										<li><a class="dropdown-item" href="{{ route('leave.index') }}"><i class="fa-solid fa-mug-hot"></i> Apply Leave</a></li>
										<li><a class="dropdown-item" href="{{ route('outstationattendance.index') }}"><i class="fa-solid fa-user-plus"></i> Outstation Attendance</a></li>
										<form method="POST" action="{{ route('logout') }}">
											@csrf
											<li>
												<a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"><i class="fas fa-light fa-right-from-bracket"></i> Log Out</a>
											</li>
										</form>
									</ul>
								</div>
							@else
								<a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Sign in</a>
							@endauth
						@endif
					</div>
				</div>
			</nav>
			<!-- end navigator -->
			<div class="row flex-nowrap justify-content-between align-items-center">
				<div class="col-4 pt-1">
					<!-- <a class="link-secondary" href="#">Subscribe</a> -->
				</div>
				<div class="col-4 text-center">
					<a class="blog-header-logo text-body-emphasis text-decoration-none" href="{{ url('/') }}">{!! config('app.name') !!}</a>
					<noscript>
						<style type="text/css">
							.pagecontainer {display:none;}
						</style>
						<div class="noscriptmsg text-danger">
							This page requires JavaScript. Please enable it or you can contact your IT administrator.
							<meta http-equiv="refresh" content="0; url={{ url('/') }}" />
						</div>
					</noscript>
				</div>
				<div class="col-4 d-flex justify-content-end align-items-center">
					&nbsp;
				</div>
			</div>
		</header>

		<!-- <div class="nav-scroller py-1 mb-3 border-bottom"> -->
		<div class="py-1 mb-3 border-bottom">
			@include('layouts.nav')
		</div>
	</div>

	<main class="container ">
		<div class="row g-5">
			<!-- <div class="col-md-8"> do not uncomment this  -->
			<div class="col-md-12">

				<!-- <h4 class="pb-4 mb-4 fst-italic border-bottom text-center">
					{{ config('app.name') }}
					need to put something or loose it for this div
				</h4> -->

				<!-- IF SUCCESS -->
				@if(Session::has('flash_message'))
				<h6 class="pb-4 mb-4 border-bottom text-center alert alert-success">
					{{ Session::get('flash_message') }}
				</h6>
				@endif

				<!-- IF ERROR -->
				@if(Session::has('flash_danger'))
				<h6 class="pb-4 mb-4 border-bottom text-center alert alert-danger">
					{{ Session::get('flash_danger') }}
				</h6>
				@endif

				@if(Session::has('status'))
				<h6 class="pb-4 mb-4 border-bottom text-center alert alert-success">
					{{ Session::get('status') }}
				</h6>
				@endif

				@if(count($errors) > 0 )
				<article class="blog-post">
					<ul class="list-group">
						@foreach($errors->all() as $err)
							<li class="list-group-item list-group-item-danger">
								{!! $err !!}
							</li>
						@endforeach
					</ul>
				</article>
				@endif

				<article class="blog-post d-flex justify-content-center align-items-center">
					@yield('content')
				</article>
			</div>
		</div>
	</main>
	<footer class="py-5 text-center text-body-secondary bg-body-tertiary ">
		<p>{{ config('app.name') }} made from <a href="">Bootstrap</a> & <a href="">Laravel v.{{ app()->version() }}</a> by <a href="{{ url('/') }}">IPMA Industry Sdn Bhd</a>.</p>
	</footer>
</body>

<!-- <script type="module" src="{{ asset('js/fullcalendar/bootstrap5/index.global.js') }}"></script> -->
<!-- <script type="module" src="{{ asset('js/fullcalendar/daygrid/index.global.js') }}"></script> -->
<!-- <script type="module" src="{{ asset('js/fullcalendar/multimonth/index.global.js') }}"></script> -->
<script src="{{ asset('js/fullcalendar/index.global.js') }}"></script>
<!-- <script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script> -->
<!-- <script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script> -->
<script src="{{ asset('js/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('js/ckeditor/adapters/jquery.js') }}"></script>
<script >
	jQuery.noConflict ();
	(function($){
		$(document).ready(function(){
			@section('js')
			@show
		});
	})(jQuery);
</script>
<script>
	@section('nonjquery')
	@show
</script>
<!-- @livewireScripts -->
</html>

