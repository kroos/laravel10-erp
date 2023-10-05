<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Model
{
	use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'customers';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanyleavereplacement(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'customer_id');
	}

	public function hasmanyoutstation(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HROutstation::class, 'customer_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	// belongsto relationship
}
