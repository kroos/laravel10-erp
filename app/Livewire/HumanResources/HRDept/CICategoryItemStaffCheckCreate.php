<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;
use App\Models\HumanResources\OptWeekDates;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;
use App\Models\HumanResources\ConditionalIncentiveStaffItemWeek;

class CICategoryItemStaffCheckCreate extends Component
{
	public $checked = [];

	public function render()
	{
		// finding what week for today
		$weeks = OptWeekDates::where(function(Builder $query) {
																$query->whereDate('date_from', '>=', now()->startOfMonth())
																	->whereDate('date_to', '<=', now()->endOfWeek());
															})
				->get();

		$cistaff = ConditionalIncentiveCategoryItem::all();
		$staff = [];
		foreach ($cistaff as $v) {
			foreach ($v->belongstomanystaff()->get() as $v1) {
				$staff[] = $v1->pivot->staff_id;
			}
		}

		$staffs = array_unique($staff);
		$incentivestaffs = Staff::select('staffs.id', 'logins.username', 'staffs.name')->join('logins', 'staffs.id', '=', 'logins.staff_id')->orderBy('logins.username')->whereIn('staffs.id', $staffs)->where('logins.active', 1)->get();
		return view('livewire.humanresources.hrdept.cicategoryitemstaffcheck', ['incentivestaffs' => $incentivestaffs, 'weeks' => $weeks]);
	}

	public function updated($property, $value)
	{
		// dd($this->checked);

		foreach ($this->checked as $k1 => $v1) {
			// dump($k1, $v1);
			foreach ($v1 as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					dump($k1, $k2, $k3);
					// ConditionalIncentiveStaffItemWeek::create([
					// 	'staff_id' => $k1,
					// 	'cicategory_item_id' => $k2,
					// 	'week_id' => $k3,
					// ]);
				}
			}
		}

	}

	public function mount()
	{
		$cistaff = ConditionalIncentiveCategoryItem::all();
		$staff = [];
		foreach ($cistaff as $v) {
			foreach ($v->belongstomanystaff()->get() as $v1) {
				$staff[] = $v1->pivot->staff_id;
			}
		}
		$staffs = array_unique($staff);
		$r1 = ConditionalIncentiveStaffItemWeek::all();
		$s1 = [];
		foreach ($r1 as $v1) {
			$s1[] = [$v1->staff_id => [$v1->cicategory_item_id => [$v1->week_id => true]]];
			// $s1[$v1->staff_id] = [$v1->cicategory_item_id => [$v1->week_id => true]];
			// $this->checked[$v1->staff_id] = [$v1->cicategory_item_id => [$v1->week_id => true]];
		}
		dump($s1);
		foreach ($s1 as $v1) {
			$this->checked[] = $v1;
		}
		dump($this->checked);
	}

	public function store()
	{
		// foreach ($this->checked as $k1 => $v1) {
		// 	foreach ($v1 as $k2 => $v2) {
		// 		foreach ($v2 as $k3 => $v3) {
		// 			// dump([$k1, $k2, $k3]);
		// 			$res = Staff::find($k1)->belongstomanycicategoryitem()->wherePivot('cicategory_item_id', $k2)->get();
		// 			// dump($res);
		// 			foreach ($res as $v4) {
		// 				// dump($v4->pivot->id);
		// 				ConditionalIncentiveStaffItemWeek::create([
		// 					'pivot_staff_item_id' => $v4->pivot->id,
		// 					'staff_id' => $v4->pivot->staff_id,
		// 					'cicategory_item_id' => $v4->pivot->cicategory_item_id,
		// 					'week_id' => $k3
		// 				]);
		// 			}
		// 		}
		// 	}
		// }
	}
}
