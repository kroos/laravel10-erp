<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationWarranty;

use Illuminate\Http\Request;

use Session;

class QuotQuotationWarrantyController extends Controller
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

	public function show(QuotQuotationWarranty $quotWarranty)
	{
	//
	}

	public function edit(QuotQuotationWarranty $quotWarranty)
	{
	}

	public function update(Request $request, QuotQuotationWarranty $quotWarranty)
	{
	//
	}

	public function destroy(QuotQuotationWarranty $quotWarranty)
	{
		// $quotWarranty->destroy();
		QuotQuotationWarranty::destroy($quotWarranty->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

