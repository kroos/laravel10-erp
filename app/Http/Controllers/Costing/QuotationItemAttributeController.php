<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotItemAttribute;

use Illuminate\Http\Request;

use Session;

class QuotationItemAttributeController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.attrib.index');
	}

	public function create()
	{
		return view('quotation.attrib.create');
	}

	public function store(Request $request)
	{
		QuotItemAttribute::create($request->only('attribute'));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotItemAttrib.index'));
	}

	public function show(QuotItemAttribute $quotItemAttrib)
	{
	//
	}

	public function edit(QuotItemAttribute $quotItemAttrib)
	{
		return view('quotation.attrib.edit', compact('quotItemAttrib'));
	}

	public function update(Request $request, QuotItemAttribute $quotItemAttrib)
	{
		$quotItemAttrib->update( $request->only('attribute') );
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotItemAttrib.index'));
	}

	public function destroy(QuotItemAttribute $quotItemAttrib)
	{
		// $quotItemAttrib->destroy();
		QuotItemAttribute::destroy($quotItemAttrib->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

