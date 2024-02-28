<?php
namespace App\Livewire\HumanResources\HRDept;

use App\Models\HumanResources\ConditionalIncentiveCategory;
use Livewire\Component;

class CICategory extends Component
{
	public $cicategories;

	// public function render()
	// {
	// 	return view('livewire.humanresources.hrdept.cicategory');
	// }

	public function del(ConditionalIncentiveCategory $cicategories)
	{
		$cicategories->delete();
	}
}
