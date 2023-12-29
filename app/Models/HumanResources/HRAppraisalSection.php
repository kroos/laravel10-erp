<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRAppraisalSection extends Model
{
    use HasFactory;
    // protected $connection = 'mysql';
    protected $table = 'hr_appraisal_sections';

    ////////////////////////// belongsToMany //////////////////////////
    public function belongstomanydepartmentpivot(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\DepartmentPivot::class, 'pivot_dept_appraisals', 'section_id', 'department_id')->withTimestamps();
	}
}
