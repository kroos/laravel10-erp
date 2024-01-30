<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationRemark;

use Illuminate\Http\Request;

use Session;

class QuotQuotationRemarkController extends Controller
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

	public function show(QuotQuotationRemark $quotRemark)
	{
	//
	}

	public function edit(QuotQuotationRemark $quotRemark)
	{
	}

	public function update(Request $request, QuotQuotationRemark $quotRemark)
	{
	//
	}

	public function destroy(QuotQuotationRemark $quotRemark)
	{
		// $quotRemark->destroy();
		QuotQuotationRemark::destroy($quotRemark->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

