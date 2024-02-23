<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesJobDescription;

// load batch and queue
// use Illuminate\Bus\Batch;
// use Illuminate\Support\Facades\Bus;

// load helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class SalesJobDescriptionController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('highMgmtAccess:1|2|5,6|24', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|2|5,6|24', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function index(): View
	{
	}

	public function create(): View
	{
	}

	public function store(Request $request): RedirectResponse
	{
	}

	public function show(SalesJobDescription $salesjobdescription): View
	{
	}

	public function edit(SalesJobDescription $salesjobdescription): View
	{
	}

	public function update(Request $request, SalesJobDescription $salesjobdescription): RedirectResponse
	{
	}

	public function destroy(SalesJobDescription $salesjobdescription): JsonResponse
	{
		// dd(request()->all());
		$salesjobdescription->belongstomanysalesgetitem()->detach();
		$salesjobdescription->delete();
		return response()->json([
			'message' => 'Successfully delete Job Description',
			'status' => 'success'
		]);
	}
}
