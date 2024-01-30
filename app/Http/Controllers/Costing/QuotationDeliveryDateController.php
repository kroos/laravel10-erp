<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotDeliveryDate;

use Illuminate\Http\Request;

use Session;

class QuotationDeliveryDateController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.delivery_date.index');
	}

	public function create()
	{
		return view('quotation.delivery_date.create');
	}

	public function store(Request $request)
	{
		QuotDeliveryDate::create($request->only(['delivery_date_period']));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotdd.index'));
	}

	public function show(QuotDeliveryDate $quotdd)
	{
	//
	}

	public function edit(QuotDeliveryDate $quotdd)
	{
		return view('quotation.delivery_date.edit', compact(['quotdd']));
	}

	public function update(Request $request, QuotDeliveryDate $quotdd)
	{
		$quotdd->update($request->only(['delivery_date_period']));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotdd.index'));
	}

	public function destroy(QuotDeliveryDate $quotdd)
	{
		// $quotdd->destroy();
		QuotDeliveryDate::destroy($quotdd->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

