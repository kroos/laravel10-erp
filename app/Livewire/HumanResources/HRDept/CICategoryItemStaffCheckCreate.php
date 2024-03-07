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
			foreach ($v1 as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					foreach ($v3 as $k4 => $v4) {
						// dump($k1, $k2, $k3, $k4, $v4);
						if ($v4) {
							ConditionalIncentiveStaffItemWeek::updateOrCreate([
								'pivot_staff_item_id' => $k1,
								'staff_id' => $k2,
								'cicategory_item_id' => $k3,
							],[
								'pivot_staff_item_id' => $k1,
								'staff_id' => $k2,
								'cicategory_item_id' => $k3,
								'week_id' => $k4,
							]);
						} else {
							ConditionalIncentiveStaffItemWeek::where([
								['pivot_staff_item_id', $k1],
								['staff_id', $k2],
								['cicategory_item_id', $k3],
							])->delete();
						}
					}
				}
			}
		}
	}

	public function mount()
	{
		$r1 = ConditionalIncentiveStaffItemWeek::all();
		$s1 = [];
		foreach ($r1 as $v1) {
			$this->checked[$v1->pivot_staff_item_id] = [$v1->staff_id => [$v1->cicategory_item_id => [$v1->week_id => true]]];
		}
		// dump($this->checked);
	}
}
