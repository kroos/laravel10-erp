<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

  /**
   * Display a listing of the resource.
   */
  public function index(): View
  {
    $customers = Customer::orderBy('customer', 'ASC')->get();

    return view('sales.salescustomer.index', ['customers' => $customers]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(): View
  {
    return view('sales.salescustomer.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
    Customer::create([
      'customer' => $request->customer,
      'contact' => $request->contact,
      'address' => $request->address,
      'phone' => $request->phone,
      'fax' => $request->fax,
      'area' => $request->area,
    ]);

    Session::flash('flash_message', 'Successfully Submit.');
    return redirect()->route('salescustomer.index');
  }

  /**
   * Display the specified resource.
   */
  public function show(Customer $salescustomer): View
  {
    return view('sales.salescustomer.show', ['customer' => $salescustomer]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Customer $salescustomer): View
  {
    return view('sales.salescustomer.edit', ['customer' => $salescustomer]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): RedirectResponse
  {
    Customer::where('id', $request->salescustomer)
      ->update([
        'customer' => $request->customer,
        'contact' => $request->contact,
        'address' => $request->address,
        'phone' => $request->phone,
        'fax' => $request->fax,
        'area' => $request->area,
      ]);

    Session::flash('flash_message', 'Successfully Updated.');
    return redirect()->route('salescustomer.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request): JsonResponse
  {
    Customer::find($request->id)->delete();

    return response()->json([
      'message' => 'Successful Deleted',
      'status' => 'success'
    ]);
  }
}
