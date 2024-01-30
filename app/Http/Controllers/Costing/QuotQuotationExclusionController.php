<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationExclusion;

use Illuminate\Http\Request;

use Session;

class QuotQuotationExclusionController extends Controller
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

	public function show(QuotQuotationExclusion $quotExclusion)
	{
	//
	}

	public function edit(QuotQuotationExclusion $quotExclusion)
	{
	}

	public function update(Request $request, QuotQuotationExclusion $quotExclusion)
	{
	//
	}

	public function destroy(QuotQuotationExclusion $quotExclusion)
	{
		// $quotExclusion->destroy();
		QuotQuotationExclusion::destroy($quotExclusion->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

