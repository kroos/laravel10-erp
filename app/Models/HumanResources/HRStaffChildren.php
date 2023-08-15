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
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRStaffChildren extends Model
{
	use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'hr_children';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation hasMany/hasOne

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation BelongsTo
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstogender(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptGender::class, 'gender_id');
	}

	public function belongstoeducationlevel(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptEducationLevel::class, 'education_level_id');
	}

	public function belongstotaxexemptionpercentage(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptTaxExemptionPercentage::class, 'tax_exemption_percentage_id');
	}

}

