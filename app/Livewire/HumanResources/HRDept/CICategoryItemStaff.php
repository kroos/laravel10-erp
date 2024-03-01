<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;
use Livewire\Attributes\On;

class CICategoryItemStaff extends Component
{
	#[On('cicategoryitemstaffcreate')]
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
		$incentivestaffs = Staff::select('staffs.id', 'logins.username', 'staffs.name')->join('logins', 'staffs.id', '=', 'logins.staff_id')->orderBy('logins.username')->whereIn('staffs.id', $staffs)->where('logins.active', 1)->get();
		return view('livewire.humanresources.hrdept.cicategoryitemstaff', ['incentivestaffs' => $incentivestaffs]);
	}

	public function delstaffitem($array)
	{
		$st = explode('_', $array);
		Staff::find($st[0])->belongstomanycicategoryitem()->detach($st[1]);
	}
}
