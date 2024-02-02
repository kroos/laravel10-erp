<?php

namespace App\Models\Sales;

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

class OptMachine extends Model
{
    use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'option_machine_models';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanymachineacc(): HasMany
	{
		return $this->hasMany(\App\Models\Sales\OptMachineAccessories::class, 'race_id');
	}
}
