<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\Staff;
use Livewire\Component;

class CICategoryItemStaffCreate extends Component
{
	public function mount()
	{
	}

	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryitemstaffcreate', [
			'staffs' => Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')->where('staffs.active', 1)->orderBy('logins.login')->get(),
		]);
	}
}
