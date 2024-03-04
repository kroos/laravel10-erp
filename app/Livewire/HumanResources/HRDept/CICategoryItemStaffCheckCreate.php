<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;
use App\Models\HumanResources\OptWeekDates;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

class CICategoryItemStaffCheckCreate extends Component
{
	public $checked = [];

	public function render()
	{
		// finding what week for today
		$weeks = OptWeekDates::where(function(Builder $query) {
																$query->whereDate('date_from', '>=', now()->startOfMonth())
																	->whereDate('date_to', '<=', now()->endOfMonth());
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

	public function store()
	{
		dd($this->checked);
	}
}
