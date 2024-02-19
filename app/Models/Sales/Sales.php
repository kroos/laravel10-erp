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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sales extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'sales';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	public function hasmanysalesamend(): HasMany
	{
		return $this->hasMany(\App\Models\Sales\SalesAmend::class, 'sales_id');
	}

	public function hasmanyjobdescription(): HasMany
	{
		return $this->hasMany(\App\Models\Sales\SalesJobDescription::class, 'sales_id');
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanydelivery(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\Sales\OptSalesDeliveryType::class, 'pivot_sales_sales_delivery', 'sales_id', 'sales_delivery_id')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'approved_by');
	}

	public function belongstoordertype(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Sales\SalesType::class, 'sales_type_id');
	}

	public function belongstocustomer(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
	}

	public function belongstosalesby(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Sales\OptSalesBy::class, 'sales_by_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasone relationship
}


