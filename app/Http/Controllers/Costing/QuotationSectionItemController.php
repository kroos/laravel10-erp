<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationSectionItem;

use Illuminate\Http\Request;

use Session;

class QuotationSectionItemController extends Controller
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

	public function show(QuotQuotationSectionItem $quotSectionItem)
	{
	//
	}

	public function edit(QuotQuotationSectionItem $quotSectionItem)
	{
	}

	public function update(Request $request, QuotQuotationSectionItem $quotSectionItem)
	{
	//
	}

	public function destroy(QuotQuotationSectionItem $quotSectionItem)
	{
		$quotSectionItem->hasmanyquotsectionitemattrib()->delete();
		// $quotSectionItem->destroy();
		QuotQuotationSectionItem::destroy($quotSectionItem->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

