@extends('layouts.app')

@section('content')
<div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-body-secondary">
			<div class="col-lg-6 px-0">
				<h1 class="display-4 fst-italic">Error 404 : Page Not Found</h1>
				<p class="lead my-3">Please contact administrator or you can click on link below.</p>
				<p class="lead mb-0"><a href="{{ url('/') }}" class="text-body-emphasis fw-bold">Home</a></p>
			</div>
		</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////
@endsection


