<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationTermOfPayment;

use Illuminate\Http\Request;

use Session;

class QuotQuotationTermOfPaymentController extends Controller
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

	public function show(QuotQuotationTermOfPayment $quotTerm)
	{
	//
	}

	public function edit(QuotQuotationTermOfPayment $quotTerm)
	{
	}

	public function update(Request $request, QuotQuotationTermOfPayment $quotTerm)
	{
	//
	}

	public function destroy(QuotQuotationTermOfPayment $quotTerm)
	{
		// $quotTerm->destroy();
		QuotQuotationTermOfPayment::destroy($quotTerm->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

