<?php

namespace App\Http\Controllers\Costing;

// load model
use \App\Model\QuotQuotation;
use \App\Model\QuotQuotationSection;
use \App\Model\QuotQuotationSectionItem;
use \App\Model\QuotQuotationSectionItemAttrib;

use \App\Model\QuotQuotationRevision;
use \App\Model\QuotQuotationTermOfPayment;
use \App\Model\QuotQuotationExclusion;
use \App\Model\QuotQuotationRemark;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// load session
use Session;

// load image library
use Intervention\Image\ImageManagerStatic as Image;

class QuotationController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('marketingAndBusinessDevelopment.costing.quotation.index');
	}

	public function create()
	{
		return view('marketingAndBusinessDevelopment.costing.quotation.create');
	}

	public function store(Request $request)
	{
		// dd($request->all());
		$qt = \Auth::user()->belongtostaff->hasmanyquotation()->create( array_add($request->only(['date', 'currency_id', 'customer_id', 'attn', 'subject', 'description', 'grandamount', 'tax_id', 'tax_value', 'discount', 'mutual', 'from', 'to', 'period_id', 'validity', 'bank_id', 'dealer_price', 'budget_quot']), 'active', 1) );

		if ($request->has('qs')) {
			foreach ($request->qs as $k1 => $v1) {

				$qt1 = $qt->hasmanyquotsection()->create([
					'section' => $v1['section'],
				]);

				// dd($v1['qssection']);
				if( array_has(  $v1, 'qssection') ) {
					foreach($v1['qssection'] as $k2 => $v2){
						$qt2 = $qt1->hasmanyquotsectionitem()->create([
							'item_id' => $v2['item_id'],
							'price_unit' => $v2['price_unit'],
							'description' => $v2['description'],
							'quantity' => $v2['quantity'],
							'uom_id' => $v2['uom_id'],
							'tax_id' => $v2['tax_id'],
							'tax_value' => $v2['tax_value'],
						]);

						if( array_has(  $v2, 'qsitem') ) {
							foreach ($v2['qsitem'] as $k3 => $v3) {

								// $request->qs[1]['qssection'][2]['qsitem'][2]['image']->store('public/images/quot');					// yang ni jadi
								// var_dump(array_has( $request->qs[$k1]['qssection'][$k2]['qsitem'][$k3], 'image' ));

								if( array_has(  $v3, 'image' ) ) {

									$filename = $v3['image']->store('public/images/quot');

									$ass1 = explode('/', $filename);
									$ass2 = array_except($ass1, ['0']);
									$image = implode('/', $ass2);

									// dd($image);

									$imag = Image::make(storage_path('app/'.$filename));

									// resize the image to a height of 400 and constrain aspect ratio (auto width)
									$imag->resize(NULL, 400, function ($constraint) {
										$constraint->aspectRatio();
									});

									// convert all image to jpg
									// $imag->encode('jpg', 75);

									$imag->save();
								} else {
									$image = NULL;
								}

								$qt3 = $qt2->hasmanyquotsectionitemattrib()->create([
									'attribute_id' => $v3['attribute_id'],
									'description_attribute' => $v3['description_attribute'],
									'remarks' => $v3['remarks'],
									'image' => $image,
								]);
							}
						}
					}
				}
			}
		}

		if($request->has('qstop')) {
			foreach ($request->qstop as $k4 => $v4) {
				$qt->hasmanytermofpayment()->create([
					'term_of_payment' => $v4['term_of_payment'],
				]);
			}
		}

		if($request->has('qsexclusions')) {
			foreach ($request->qsexclusions as $k5 => $v5) {
				$qt->hasmanyexclusions()->create([
					'exclusion_id' => $v5['exclusion_id'],
				]);
			}
		}

		if($request->has('qsremark')) {
			foreach ($request->qsremark as $k6 => $v6) {
				$qt->hasmanyremarks()->create([
					'remark_id' => $v6['remark_id'],
				]);
			}
		}

		if($request->has('qsdealer')) {
			foreach ($request->qsdealer as $k7 => $v7) {
				$qt->hasmanydealer()->create([
					'dealer_id' => $v7['dealer_id'],
				]);
			}
		}

		if($request->has('qswarranty')) {
			foreach ($request->qswarranty as $k8 => $v8) {
				$qt->hasmanywarranty()->create([
					'warranty_id' => $v8['warranty_id'],
				]);
			}
		}

		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quot.index'));
	}

	public function show(QuotQuotation $quot)
	{
		echo view('pdfleave.quotation', compact(['quot']));
	}

	public function edit(QuotQuotation $quot)
	{
		return view('marketingAndBusinessDevelopment.costing.quotation.edit', compact(['quot']));
	}

	public function update(Request $request, QuotQuotation $quot)
	{
		if(is_null($request->bank_id)){
			$bank = NULL;
		} else {
			$bank = $request->bank_id;
		}
		// dd( array_add($request->all(), 'bank', $bank) );
		$quot->update( array_add(array_add($request->only(['date', 'currency_id', 'customer_id', 'attn', 'subject', 'description', 'grandamount', 'tax_id', 'tax_value', 'discount', 'mutual', 'from', 'to', 'period_id', 'validity', 'dealer_price', 'budget_quot']), 'active', 1), 'bank_id', $bank) );
// dd($request->mutual);
		// if( $request->has('mutual') ) {
		// 	foreach($request->mutual as $k => $v) {
		// 		$quot->update([
		// 			'mutual' => $v
		// 		]);
		// 	}
		// }

		$filename1 = $request->file('revision_file')->store('public/quot_revs');

		$ass11 = explode('/', $filename1);
		$ass21 = array_except($ass11, ['0']);
		$revfile = implode('/', $ass21);

		$quot->hasmanyrevision()->create(array_add($request->only('revision'), 'revision_file', $revfile));

		if ($request->has('qs')) {

			foreach ($request->qs as $k1 => $v1) {
// var_dump($request->qs);
// die();

				$qw1 = $quot->hasmanyquotsection()->updateOrCreate(
					[
						'id' => $v1['id']
					],
					[
						'section' => $v1['section'],
					]);

				// dd($v1['qssection']);
				if( array_has(  $v1, 'qssection') ) {
					foreach($v1['qssection'] as $k2 => $v2){
						$qw2 = $qw1->hasmanyquotsectionitem()->updateOrCreate(
						[
							'id' => $v2['id']
						],
						[
							'item_id' => $v2['item_id'],
							'price_unit' => $v2['price_unit'],
							'description' => $v2['description'],
							'quantity' => $v2['quantity'],
							'uom_id' => $v2['uom_id'],
							'tax_id' => $v2['tax_id'],
							'tax_value' => $v2['tax_value'],
						]);

						if( array_has(  $v2, 'qsitem') ) {
							foreach ($v2['qsitem'] as $k3 => $v3) {

								// $request->qs[1]['qssection'][2]['qsitem'][2]['image']->store('public/images/quot');					// yang ni jadi
								// var_dump(array_has( $request->qs[$k1]['qssection'][$k2]['qsitem'][$k3], 'image' ));

								if( array_has(  $v3, 'image' ) ) {

									$filename = $v3['image']->store('public/images/quot');

									$ass1 = explode('/', $filename);
									$ass2 = array_except($ass1, ['0']);
									$image = implode('/', $ass2);

									// dd($image);

									$imag = Image::make(storage_path('app/'.$filename));

									// resize the image to a height of 400 and constrain aspect ratio (auto width)
									$imag->resize(NULL, 400, function ($constraint) {
										$constraint->aspectRatio();
									});

									// convert all image to jpg
									// $imag->encode('jpg', 75);

									$imag->save();

									$qw2->hasmanyquotsectionitemattrib()->updateOrCreate(
									[
										'id' => $v3['id']
									],
									[
										'attribute_id' => $v3['attribute_id'],
										'description_attribute' => $v3['description_attribute'],
										'remarks' => $v3['remarks'],
										'image' => $image,
									]);

								} else {

									$qw2->hasmanyquotsectionitemattrib()->updateOrCreate(
									[
										'id' => $v3['id']
									],
									[
										'attribute_id' => $v3['attribute_id'],
										'description_attribute' => $v3['description_attribute'],
										'remarks' => $v3['remarks'],
										// 'image' => $image,
									]);

								}
							}
						}
					}
				}
			}
		}

		if($request->has('qstop')) {
			foreach ($request->qstop as $k4 => $v4) {
				$quot->hasmanytermofpayment()->updateOrCreate(
					[
						'id' => $v4['id']
					],
					[
						'term_of_payment' => $v4['term_of_payment'],
					]);
			}
		}

		if($request->has('qsexclusions')) {
			foreach ($request->qsexclusions as $k5 => $v5) {
				$quot->hasmanyexclusions()->updateOrCreate(
					[
						'id' => $v5['id']
					],
					[
						// 'quot_id' => $v5['quot_id'],
						'exclusion_id' => $v5['exclusion_id'],
					]);
			}
		}

		if($request->has('qsremark')) {
			foreach ($request->qsremark as $k6 => $v6) {
				$quot->hasmanyremarks()->updateOrCreate(
					[
						'id' => $v6['id']
					],
					[
						// 'quot_id' => $v6['quot_id'],
						'remark_id' => $v6['remark_id'],
					]);
			}
		}

		if($request->has('qsdealer')) {
			foreach ($request->qsdealer as $k7 => $v7) {
				$quot->hasmanydealer()->updateOrCreate(
					[
						'id' => $v7['id']
					],
					[
						'dealer_id' => $v7['dealer_id'],
					]);
			}
		}

		if($request->has('qswarranty')) {
			foreach ($request->qswarranty as $k8 => $v8) {
				$quot->hasmanywarranty()->updateOrCreate(
					[
						'id' => $v8['id']
					],
					[
						'warranty_id' => $v8['warranty_id'],
					]);
			}
		}

		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quot.index'));
	}

	public function destroy(QuotQuotation $quot)
	{
		//
	}
}
