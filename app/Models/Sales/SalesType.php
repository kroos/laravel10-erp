<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;
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

class SalesType extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'sales_order_types';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	public function hasmanysales(): HasMany
	{
		return $this->hasMany(\App\Models\Sales\Sales::class, 'sales_type_id');
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship

	/////////////////////////////////////////////////////////////////////////////////////////
}


