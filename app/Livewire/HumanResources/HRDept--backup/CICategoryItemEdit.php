<?php

namespace App\Livewire\HumanResources\HRDept;

use App\Models\HumanResources\ConditionalIncentiveCategory;
use Livewire\Attribute\Validate;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;


class CICategoryItemEdit extends Component
{
	public $cicategoryitem;

	public $cat;

	#[Rule('required', 'Conditional Incentive Category')]
	public $ci_category_id;

	#[Rule('required|string|min:5', 'Item Category Description')]
	public $description;

	#[Rule('required|numeric|integer|min:0|max:100', 'Item Category Incentive Deduction')]
	public $point;

	// some function from livewire. see docs
	public function mount()
	{
		$this->ci_category_id = $this->cicategoryitem->ci_category_id;
		$this->description = $this->cicategoryitem->description;
		$this->point = $this->cicategoryitem->point;
		$this->cat = ConditionalIncentiveCategory::all();
	}

	// some function from livewire. see docs
	public function updated($property, $value)
	{
		if ($property == 'description') {
			$this->description = ucwords(Str::lower($value));
		}
	}

	public function update()
	{
		$this->validate();
		$this->cicategoryitem->update([
			'ci_category_id' => $this->ci_category_id,
			'description' => $this->description,
			'point' => $this->point,
		]);
		$this->reset();
		return redirect()->route('cicategory.index')->with('message', 'Success Edit Item Category');
	}

	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryitemedit');
	}
}
