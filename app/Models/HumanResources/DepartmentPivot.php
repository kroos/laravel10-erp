<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DepartmentPivot extends Model
{
	use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'pivot_dept_cate_branches';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanystaff(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\Staff::class, 'pivot_staff_pivotdepts', 'pivot_dept_id', 'staff_id')->withTimestamps();
	}

	public function belongstomanysection(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRAppraisalSection::class, 'pivot_dept_appraisals', 'department_id', 'section_id')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsTo
	public function belongstodepartment(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptDepartment::class, 'group_id');
	}

	public function belongstobranch(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptBranch::class, 'branch_id');
	}

	public function belongstocategory(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptCategory::class, 'category_id');
	}

	public function belongstowhgroup(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptWorkingHour::class, 'wh_group_id', 'group');
	}
}

