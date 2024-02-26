<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Component;

class CICategory extends Component
{
    public $cicategories;

    public function render()
    {
        return view('livewire.humanresources.hrdept.cicategory');
    }
}
