<?php

namespace App\Imports;

use App\Models\HumanResources\HRTempPunchTime;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new HRTempPunchTime([
            'EmployeeCode' => $row['employeecode'],
            'Att_Time' => $row['att_time'],
        ]);
    }
}
