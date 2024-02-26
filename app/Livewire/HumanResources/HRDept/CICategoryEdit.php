<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Component;

class CICategoryEdit extends Component
{
	public $cicategory;

	public $category;

	public function mount($cicategory)
	{
		$this->category = $cicategory;
	}

	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryedit');
	}
}
