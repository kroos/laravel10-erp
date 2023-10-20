<?php

namespace App\Imports;

use App\Models\HumanResources\HRTempPunchTime;
use Maatwebsite\Excel\Concerns\ToModel;

class AttendanceImport implements ToModel
{
    public function model(array $row)
    {
		if (!isset($row[0])) {
			return null;
		}
		
        return new HRTempPunchTime([
           'EmployeeCode' => $row[0],
           'Att_Time' => $row[1], 
        ]);
    }
}
