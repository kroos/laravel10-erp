<?php

namespace App\Exports;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROvertimeRange;
// use App\Models\HumanResources\HROvertimeRange;
use App\Models\Staff;
use App\Models\Login;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Concerns\FromCollection;

class PayslipExport implements FromCollection
{
	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	/**
	* @return \Illuminate\Support\Collection
	*/
	public function collection()
	{
		// return HRAttendance::all();
		$req = $this->request;
		// dd($req['from']);
		$hratt = HRAttendance::where(function (Builder $query) use ($req){
					$query->whereDate('attend_date', '>=', $req['from'])
						->whereDate('attend_date', '<=', $req['to']);
				})
				->groupBy('staff_id')
				// ->ddrawsql();
				->get();

		$header = ['Emp No', 'Name', 'AL', 'NRL', 'MC', 'UPL', 'Absent', 'UPMC', 'Lateness', 'Early Out', 'No Pay Hour', 'Maternity', 'Hospitalization', 'Other Leave', 'Compassionate Leave', 'Marriage Leave', 'Day Work', '1.0 OT', '1.5 OT', 'OT'];
		$records = [];

		foreach ($hratt as $k1 => $v1) {
			$login = Login::where('staff_id', $v1->staff_id)->first()?->username;
			$name = Staff::find($v1->staff_id)->name;
			$al = HRAttendance::where(function (Builder $query) use ($req){
					$query->whereDate('attend_date', '>=', $req['from'])
						->whereDate('attend_date', '<=', $req['to']);
				})
				->
				->whereNotNull('leave_id')
				->get();
			if($al->count()) {

			}




















			$records[$k1] = [$login, $name];
		}
		dd($records);


























		$combine = array_combine($header, $records);
		dd($combine);
		// return $$combine;
	}
}
