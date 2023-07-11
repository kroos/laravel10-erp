<?php

namespace App\Models;

// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Staff extends Model
{
    use Notifiable, HasFactory, SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'staffs';
    
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    // this is important for sending email
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation hasMany/hasOne

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsTo
    public function hasmanylogin(): HasMany
    {
        return $this->hasMany(App\Models\Login, 'staff_id');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
}
