<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use App\Models\HumanResources\ConditionalIncentiveCategory;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;


class CICategoryItemCreate extends Component
{
	#[Rule('required', 'Conditional Incentive Category')]
	public $ci_category_id = '';

	#[Rule('required|string|min:5', 'Item Category Description')]
	public $description = '';

	#[Rule('required|numeric|integer|min:0|max:100', 'Item Category Incentive Deduction')]
	public $point = 0;

	// some function from livewire. see docs
	#[On('cicategoryitemdel')]
	#[On('cicategorydel')]
	public function mount()
	{
		$this->cat = ConditionalIncentiveCategory::all();
	}

	// some function from livewire. see docs
	public function updated($property, $value)
	{
		if ($property == 'description') {
			$this->description = ucwords(Str::lower($value));
		}
	}

	public function store()
	{
		$this->validate();
		ConditionalIncentiveCategoryItem::create([
			'ci_category_id' => $this->ci_category_id,
			'description' => $this->description,
			'point' => $this->point,
		]);
		$this->reset();
		$this->dispatch('cicategoryitemcreate');
		// return redirect()->route('cicategory.index')->with('message', 'Success Edit Item Category');
	}

	#[On('cicategorycreate')]
	#[On('cicategoryitemcreate')]
	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryitemcreate', [
			'cat' => ConditionalIncentiveCategory::all(),
		]);
	}
}
