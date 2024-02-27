<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Component;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

class CICategoryItem extends Component
{
    public $cicategory;

    public function render()
    {
        return view('livewire.humanresources.hrdept.cicategoryitem');
    }

    public function deltem($id)
    {
        ConditionalIncentiveCategoryItem::find($id)->delete();
    }
}
