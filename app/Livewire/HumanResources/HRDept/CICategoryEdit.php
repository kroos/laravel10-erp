<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Attribute\Validate;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;


class CICategoryEdit extends Component
{
	public $cicategory;

	#[Rule('required|string|min:5', 'Category')]
	public $category = '';

	public function mount()
	{
		$this->category = $this->cicategory->category;
	}

	public function updated($property, $value)
	{
		// dd($property, $value);
		if ($property == 'category') {
			$this->category = ucwords(Str::lower($value));
		}
	}

	public function update()
	{
		$this->validate();
		$this->cicategory->update(['category' => $this->category]);
		// $this->cicategory->update([
		// 	'category' => ucwords(Str::lower($this->category))
		// ]);
		$this->reset();
		return redirect()->route('cicategory.index')->with('message', 'Success Edit Category');
	}

	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryedit');
	}
}
