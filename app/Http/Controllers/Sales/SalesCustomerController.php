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
use App\Models\Customer;

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

class SalesCustomerController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('highMgmtAccess:1|2|5,6|24');
	}

	public function index(): View
	{
    $customers = Customer::all();

		return view('sales.salescustomer.index', ['customers' => $customers]);
	}
}
