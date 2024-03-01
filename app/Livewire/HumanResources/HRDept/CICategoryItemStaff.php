<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

class CICategoryItemStaff extends Component
{

	public function mount()
	{
	}

	public function render()
	{
		$cistaff = ConditionalIncentiveCategoryItem::all();
		$staff = [];
		foreach ($cistaff as $v) {
			foreach ($v->belongstomanystaff()->get() as $v1) {
				$staff[] = $v1->pivot->staff_id;
			}
		}

		$staffs = array_unique($staff);
		$incentivestaffs = Staff::whereIn('id', $staffs)->get();
		return view('livewire.humanresources.hrdept.cicategoryitemstaff', ['incentivestaffs' => $incentivestaffs]);
	}
}
