<?php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load model
use App\Models\Setting;

use App\Models\Staff;

// load helper

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;


class SalesDeptController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('highMgmtAccess:2,NULL'/*, ['only' => ['show', 'edit', 'update']]*/);
	}

	public function index(): View
	{
		return view('sales.index');
	}
}
