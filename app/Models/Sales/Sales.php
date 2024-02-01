<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sales extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'sales';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	// public function hasmanyleavereplacement(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'leave_id');
	// }


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstoordertype(): BelongsTo
	{
		return $this->hasMany(\App\Models\Sales\SalesType::class, 'sales_type_id');
	}

	public function belongstocustomer(): BelongsTo
	{
		return $this->hasMany(\App\Models\Customer::class, 'customer_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasone relationship
}


