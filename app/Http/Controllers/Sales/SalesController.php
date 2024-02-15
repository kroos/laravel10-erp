<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\Sales\Sales;

// load batch and queue
// use Illuminate\Bus\Batch;
// use Illuminate\Support\Facades\Bus;

// load helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class SalesController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('highMgmtAccess:1|2|5,6|24', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|2|5,6|24', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function index(): View
	{
		$sales = Sales::all();
		return view('sales.sales.index', ['sales' => $sales]);
	}

	public function create(): View
	{
		return view('sales.sales.create');
	}

	public function store(Request $request): RedirectResponse
	{
		// dd($request->except('_token'));
		$validated = $request->validate(
				[
					'date_order' => 'required|date',
					'customer_id' => 'nullable',
					'sales_type_id' => 'required',
					'special_request' => 'required_if:spec_req,true',
					'po_number' => 'nullable',
					'delivery_at' => 'required',
					'urgency' => 'nullable',
					'sales_delivery_id' => 'required',
					'special_delivery_instruction' => 'nullable',
					'jobdesc.*.job_description' => 'required',
					'jobdesc.*.quantity' => 'required',
					'jobdesc.*.uom_id' => 'required',
					'jobdesc.*.sales_get_item_id' => 'required',
					'jobdesc.*.machine_id' => 'required',
					'jobdesc.*.machine_accessory_id' => 'nullable',
					'jobdesc.*.job_description' => 'required',
				],
				[
					// 'date_order.required' => 'Please insert year',
					// 'customer_id.required' => 'Please insert year',
					// 'sales_type_id.required' => 'Please insert year',
					'special_request.required_if' => ':attribute is needed when :attribute is checked',
					// 'po_number.required' => 'Please insert year',
					// 'delivery_at.required' => 'Please insert year',
					// 'urgency.required' => 'Please insert year',
					'sales_delivery_id.*.required' => ':attribute is required',
					// 'special_delivery_instruction.required' => 'Please insert year',
					// 'jobdesc.*.job_description.required' => 'Job Description',
					// 'jobdesc.*.quantity.required' => 'Job Description',
					// 'jobdesc.*.uom_id.required' => 'Job Description UOM',
					// 'jobdesc.*.sales_get_item_id.required' => 'Job Description',
					// 'jobdesc.*.machine_id.required' => 'Job Description Machine',
					// 'jobdesc.*.machine_accessories_id.required' => 'Job Description',
					// 'jobdesc.*.job_description.required' => 'Job Description',
				],
				[
					'date_order' => 'Date',
					'customer_id' => 'Customer',
					'sales_type_id' => 'Order Type',
					'special_request' => 'Special Request Remarks',
					'po_number' => 'PO Number',
					'delivery_at' => 'Estimation Delivery Date',
					'urgency' => 'Mark As Urgent',
					'sales_delivery_id' => 'Delivery Instruction',
					'special_delivery_instruction' => 'Special Delivery Instruction',
					'jobdesc.*.job_description' => 'Job Description',
					'jobdesc.*.quantity' => 'Job Description Quantity',
					'jobdesc.*.uom_id' => 'Job Description UOM ',
					'jobdesc.*.sales_get_item_id.*' => 'Job Description Delivery Instruction',
					'jobdesc.*.machine_id' => 'Job Description Machine',
					'jobdesc.*.machine_accessory_id' => 'Job Description Machine Accessories',
					'jobdesc.*.remarks' => 'Job Description Remarks',
				]
			);

		$user = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id;
		if ($user == 6) {
			$sales_by = 2;
		} else {
			$sales_by = 1;
		}

		$count = Sales::whereYear('created_at', now()->format('Y'))->get()->count() + 1;

		$data = $request->only(['date_order', 'customer_id', 'deliveryby_id', 'sales_type_id', 'po_number', 'delivery_at', 'urgency']);
		$data += ['special_delivery_instruction' => ucwords(Str::lower($request->special_delivery_instruction))];
		$data += ['staff_id' => \Auth::user()->belongstostaff->id];
		$data += ['sales_by_id' => $sales_by];
		$data += ['no' => $count];
		$data += ['year' => now()->format('Y')];
		if ($request->has('spec_req')) {
			$data += ['special_request' => ucwords(Str::lower($request->special_request))];
		}
		$sal = Sales::create($data);

		if ($request->has('sales_delivery_id')) {
			foreach ($request->sales_delivery_id as $k => $v) {
				$sal->belongstomanydelivery()->attach($v);
			}
		}
		if ($request->has('jobdesc')) {
			foreach ($request->jobdesc as $k => $v) {
				// dump($k, $v);
				$job_description = ucwords(Str::lower($v['job_description']));
				$quantity = $v['quantity'];
				$uom_id = $v['uom_id'];
				$machine_id = $v['machine_id'];
				$machine_accessory_id = $v['machine_accessory_id']??NULL;
				$remarks = ucwords(Str::lower($v['remarks']));

				$sjd = $sal->hasmanyjobdescription()->create([
							'job_description' => $job_description,
							'quantity' => $quantity,
							'uom_id' => $uom_id,
							'machine_id' => $machine_id,
							'machine_accessory_id' => $machine_accessory_id,
							'remarks' => $remarks,
						]);
				foreach ($v['sales_get_item_id'] as $k1 => $v1) {
					$sjd->hasmanyjobdescriptiongetitem()->create(['sales_get_item_id' => $v1]);
				}
			}
		}
		return redirect()->route('sales.index')->with('flash_message', 'Successfully Add New Customer Order');
	}

	public function show(Sales $sale): View
	{
	}

	public function edit(Sales $sale): View
	{
		return view('sales.sales.edit', ['sale' => $sale]);
	}

	public function update(Request $request, Sales $sale): RedirectResponse
	{
	}

	public function destroy(Sales $sale): JsonResponse
	{
	}
}
