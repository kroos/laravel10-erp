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
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalesJobDescriptionGetItem extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'sales_job_description_get_items';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	// public function hasmanysales(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\Sales\Sales::class, 'deliveryby_id');
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	// public function belongstomanysales(): BelongsToMany
	// {
	// 	return $this->belongsToMany(\App\Models\Sales\SalesDeliveryType::class, 'pivot_sales_sales_delivery', 'sales_delivery_id', 'sales_id')->withTimestamps();
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstosales(): HasMany
	{
		return $this->hasMany(\App\Models\Sales\SalesJobDescription::class, 'sales_job_description_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
}


