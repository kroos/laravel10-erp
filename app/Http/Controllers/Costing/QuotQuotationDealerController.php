<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationDealer;

use Illuminate\Http\Request;

use Session;

class QuotQuotationDealerController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
	}

	public function create()
	{
	}

	public function store(Request $request)
	{
	}

	public function show(QuotQuotationDealer $quotDealer)
	{
	//
	}

	public function edit(QuotQuotationDealer $quotDealer)
	{
	}

	public function update(Request $request, QuotQuotationDealer $quotDealer)
	{
	//
	}

	public function destroy(QuotQuotationDealer $quotDealer)
	{
		// $quotDealer->destroy();
		QuotQuotationDealer::destroy($quotDealer->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

