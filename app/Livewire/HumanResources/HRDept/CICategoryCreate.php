<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\HumanResources\ConditionalIncentiveCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;


class CICategoryCreate extends Component
{
	#[Rule('required|string|min:5', 'Category')]
	public $category;

	public function updated($property, $value)
	{
		if ($property == 'category') {
			$this->category = ucwords(Str::lower($value));
		}
	}

	public function store()
	{
		$this->validate();
		ConditionalIncentiveCategory::create(['category' => $this->category]);
		$this->reset();
		$this->dispatch('cicategorycreate');
		// $this->redirect(route('cicategory.index'), $navigate = true)->with('flash_message', 'Success create Category');
		// return redirect()->route('cicategory.index')->with('flash_message', 'Success create Category');
	}

	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategorycreate');
	}
}
