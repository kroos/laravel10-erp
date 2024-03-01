<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\HumanResources\ConditionalIncentiveCategory;

class CICategoryItemStaffCreate extends Component
{
	#[Rule('required', 'Staff')]
	public $staff_id = [];

	#[Rule('required', 'Incentive Item')]
	public $cicategory_item_id = [];

	public function store()
	{
		$this->validate();
		foreach ($this->staff_id as $v) {
			Staff::find($v)->belongstomanycicategoryitem()->attach($this->cicategory_item_id);
		}
		$this->reset();
		$this->dispatch('cicategoryitemstaffcreate');
		// return redirect()->route('cicategory.index')->with('message', 'Success Edit Item Category');
	}

	public function render()
	{
		$staffs = Staff::select('staffs.id', 'logins.username', 'staffs.name')->join('logins', 'staffs.id', '=', 'logins.staff_id')->where('staffs.active', 1)->where('logins.active', 1)->orderBy('logins.username')->get();
		return view('livewire.humanresources.hrdept.cicategoryitemstaffcreate', [
			'staffs' => $staffs,
			'cicategories' => ConditionalIncentiveCategory::all(),
		]);
	}
}
