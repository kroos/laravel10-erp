<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationSectionItemAttrib;

use Illuminate\Http\Request;

use Session;

class QuotationSectionItemAttributeController extends Controller
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

	public function show(QuotQuotationSectionItemAttrib $quotSectionItemAttrib)
	{
	//
	}

	public function edit(QuotQuotationSectionItemAttrib $quotSectionItemAttrib)
	{
	}

	public function update(Request $request, QuotQuotationSectionItemAttrib $quotSectionItemAttrib)
	{
	//
	}

	public function destroy(QuotQuotationSectionItemAttrib $quotSectionItemAttrib)
	{
		// $quotSectionItemAttrib->destroy();
		QuotQuotationSectionItemAttrib::destroy($quotSectionItemAttrib->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
