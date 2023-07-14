<?php

namespace App\Model\HumanResource\HRSettings;

use App\Model\Model;

class WorkingHour extends Model
{
	protected $connection = 'mysql';
    protected $table = 'working_hours';
}
