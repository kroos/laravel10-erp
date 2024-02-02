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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OptMachineAccessory extends Model
{
    use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'option_machine_accessories';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// {
	// 	return $this->hasMany(\App\Models\Staff::class, 'race_id');
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function belongstomachine(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Sales\OptMachine::class, 'machine_id');
	}
}
