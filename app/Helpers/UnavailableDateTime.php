<?php
namespace App\Helpers;

use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\OptWorkingHour;
use App\Models\Staff;
use App\Models\Setting;

// use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;


class UnavailableDateTime
{
	public function __construct()
	{
		$this->middleware(['auth']);
	}

	public static function blockDate($id = '')
	{
		// globally mark date for weekend and holiday as unavailable to choose
		// 1st, check what year is now and disable every public holiday on that year
		$d = Carbon::now(config('app.timezone'));
		// echo $d->year;					// this year
		// echo $d->addYear()->year;		// next year

		// block every sunday
		$today = Carbon::now();
		$start_date = Carbon::create($today->year, 1, 1);
		$end_date = Carbon::create($today->year + 1, 1, 1);
		$sundays = [];
		foreach ($start_date->daysUntil($end_date) as $date) {
			if ($date->dayOfWeek === Carbon::SUNDAY) {
				$sundays[] = $date->format('Y-m-d');
			}
		}

		// block next year date till entitlement and working hour were generate
			$nystart_date = $start_date->copy()->addYear();
			$nyend_date = $end_date->copy()->addYears(1)->subDay();
			// block next year if entitlements and working hours not set
			$entitannual = HRLeaveAnnual::where('year', $nystart_date->copy()->year)->get();
			$entitmc = HRLeaveMC::where('year', $nystart_date->copy()->year)->get();
			$entitmaternity = HRLeaveMaternity::where('year', $nystart_date->copy()->year)->get();
			$wh = OptWorkingHour::where('year', $nystart_date->copy()->year)->get();
			// dd([$entitannual, $entitmc, $entitmaternity, $wh]);
			// dd([empty($entitannual->count()), empty($entitmc->count()), empty($entitmaternity->count()), empty($wh->count())]);
			// dd([ empty($entitannual->count()) && empty($entitmc->count()) && empty($entitmaternity->count()) && empty($wh->count()) ]);
			// dd(empty($entitannual->count() && $entitmc->count() && $entitmaternity->count()) && empty($wh->count()) );
			$nextyear = [];
		if (Setting::find(6)->active == 1) {																					// block next year leave (setting): enable is 1
			foreach ($nystart_date->daysUntil($nyend_date) as $nydate) {															// straight away block next year
				$nextyear[] = $nydate->format('Y-m-d');
			}
		} else {																													// no block next year leave (setting):
			if(empty($entitannual->count()) && empty($entitmc->count()) && empty($entitmaternity->count()) && empty($wh->count())) {	// check the tables if its there 1st, endure all are ready
				foreach ($nystart_date->daysUntil($nyend_date) as $nydate) {														// table not ready
					$nextyear[] = $nydate->format('Y-m-d');
				}
			} else {																												// table ready
				$nextyear = [];
			}
		}

		// list all holiday date based on this year and next year
		$hdate = HRHolidayCalendar::
		where(function (Builder $query) use ($d){
			$query->whereYear('date_start', '<=', $d->copy()->year)
			->whereYear('date_end', '>=', $d->copy()->year);
		})
		->orwhere(function (Builder $query) use ($d){
			$query->whereYear('date_start', '<=', $d->copy()->addYear()->year)
			->whereYear('date_end', '>=', $d->copy()->addYear()->year);
		})
		// ->ddRawSql();
		->get();

		$holiday = [];
		if ($hdate->count()) {
			foreach ($hdate as $nda) {
				$period = \Carbon\CarbonPeriod::create($nda->date_start, '1 days', $nda->date_end);
				foreach ($period as $key) {
					// echo 'moment("'.$key->format('Y-m-d').'"),';
					$holiday[] = $key->format('Y-m-d');
				}
			}
		} else {
			$holiday = [];
		}

		// block saturday according to group
		// make sure $request->id comes from table staff
		// ->whereYear('saturday_date', $d->copy()->year)->whereYear('saturday_date', $d->copy()->addYear()->year)->get()
		$sat = Staff::find($id)?->belongstorestdaygroup?->hasmanyrestdaycalendar()
		->where(function (Builder $query) use ($d){
			$query->whereYear('saturday_date', $d->copy()->year)
			->orwhereYear('saturday_date', $d->copy()->addYear()->year);
		})
		// ->ddRawSql();
		->get();
		if(!is_null($sat)) {
			$saturdays = [];
			foreach ($sat as $key) {
				$saturdays[] = $key->saturday_date;
			}
		} else {
			$saturdays = [];
		}

		// always block self leave
		// make sure $request->id comes from table staff
		// $leaveday = HRLeave::where('staff_id', $id)->whereIn('leave_status_id', [4,5,6])->whereNull('leave_status_id')->whereRaw('"'.$d->copy()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->orwhereRaw('"'.$d->copy()->addYear()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->get();
		$aleaveday = HRLeave::where('staff_id', $id)
		->where(function (Builder $query){
			$query->whereIn('leave_status_id', [5,6])
			->orwhereNull('leave_status_id');
		})
		// ->where('leave_cat', 2)
		->where(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->year)
			->whereYear('date_time_end', '>=', $d->copy()->year);
		})
		->orwhere(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->addYear()->year)
			->whereYear('date_time_end', '>=', $d->copy()->addYear()->year);
		})
		// ->ddRawSql();
		->get();

		// just unblock half day leave
		$hleaveday = HRLeave::where('staff_id', $id)
		->where(function (Builder $query){
			$query->whereIn('leave_status_id', [5,6])
			->orwhereNull('leave_status_id');
		})
		->where('leave_cat', 2)
		->where(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->year)
			->whereYear('date_time_end', '>=', $d->copy()->year);
		})
		->orwhere(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->addYear()->year)
			->whereYear('date_time_end', '>=', $d->copy()->addYear()->year);
		})
		// ->ddRawSql();
		->get();

		// echo $leaveday;
		// dd([$aleaveday->count(), $hleaveday->count()]);
		// dd($aleaveday, $hleaveday);

		$leavday = [];
		// if(!is_null($aleaveday)) {
		if($aleaveday->count()) {
			foreach ($aleaveday as $v1) {
				$period1 = \Carbon\CarbonPeriod::create($v1->date_time_start, '1 days', $v1->date_time_end);
				foreach ($period1 as $key1) {
					$leavday1[] = $key1->format('Y-m-d');
				}
			}
		} else {
			$leavday1 = [];
		}
		// dd($leavday1);

		$leavday2 = [];
		if($hleaveday->count()) {
			foreach ($hleaveday as $v2) {
				$period2 = \Carbon\CarbonPeriod::create($v2->date_time_start, '1 days', $v2->date_time_end);
				foreach ($period2 as $key2) {
					$leavday2[] = $key2->format('Y-m-d');
				}
			}
		} else {
			$leavday2 = [];
		}
		// dd(array_diff($leavday1, $leavday2), $leavday1, $leavday2);

		if(Setting::find(1)->active == 1) {																				// overlapped leave date checking
			$leavday = array_diff($leavday1, $leavday2);																// remove 1 value from array so not blocking date with half day
		} else {
			$leavday = $leavday1;
		}
		// dd($leavday);

		$unavailableleave = Arr::collapse([$holiday, $sundays, $leavday, $saturdays, $nextyear]);
		return $unavailableleave;
	}

	public static function unblockhalfdayleave($id = '')
	{
		$d = Carbon::now(config('app.timezone'));
		$hleaveday1 = HRLeave::where('staff_id', $id)
		->where(function (Builder $query){
			$query->whereIn('leave_status_id', [5,6])
			->orwhereNull('leave_status_id');
		})
		->whereNotNull('half_type_id')
		->where(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->year)
			->whereYear('date_time_end', '>=', $d->copy()->year);
		})
		->orwhere(function (Builder $query) use ($d){
			$query->whereYear('date_time_start', '<=', $d->copy()->addYear()->year)
			->whereYear('date_time_end', '>=', $d->copy()->addYear()->year);
		})
		// ->ddRawSql();
		->get();

		// get the days of half day leave
		if($hleaveday1->count()) {
			foreach ($hleaveday1 as $v2) {
				$timeuhd[] = [
								'date_half_leave' => \Carbon\Carbon::parse($v2->date_time_start)->format('Y-m-d'),
								'time_start' => \Carbon\Carbon::parse($v2->date_time_start)->format('H:i:s'),
								'time_end' => \Carbon\Carbon::parse($v2->date_time_end)->format('H:i:s')
							];
			}
		} else {
			$timeuhd = [];
		}
		// 		$date = \Carbon\Carbon::parse($hleaveday1->date_time_start)->format('Y-m-d');
		// 		$timeuhds = \Carbon\Carbon::parse($hleaveday1->date_time_start)->format('G:i:s');
		// 		$timeuhde = \Carbon\Carbon::parse($hleaveday1->date_time_end)->format('G:i:s');
		// $timeuhd = [
		// 				'date_half_leave' => $date,
		// 				'time_start' => $timeuhds,
		// 				'time_end' => $timeuhde
		// 			];
		return $timeuhd;
	}

	public static function workinghourtime($date = '', $user = '')
	{
		// dd($date);
		// get year from leave date
		// $date = $request->date;
		$dt = \Carbon\Carbon::parse($date);
		// echo $dt->year;
		// echo $dt->dayOfWeek;	// if = 5, meaning its friday so need to look at category 3

		$dty = $dt->copy()->year;

		// get group working hour from department
		$gwh = Staff::find($user)->belongstomanydepartment()->wherePivot('main', 1)->first()->wh_group_id;

		// pls be remind, this is for leave application, so if maintenance (group=1/$gwh=1) apply leave, we should give user category 8

		if($dt->copy()->dayOfWeek == 5) {				// friday
			if($gwh == 1){								// friday | geng maintenance
				if($dty == date('Y')){					// friday | geng maintenance | in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 8])
					->get();
					// ->ddRawSql();
				} else {								// friday | geng maintenance | not in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 8])
					->get();
					// ->ddRawSql();
				}
			} else {									// not geng maintenance
				if($dty == date('Y')){					// friday | not geng maintenance | in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 3])
					->get();
					// ->ddRawSql();
				} else {								// friday | not geng maintenance | not in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 3])
					->get();
					// ->ddRawSql();
				}
			}
		} else {										// not on friday
			if($gwh == 1){								// not on friday | geng maintenance
				if($dty == date('Y')){					// not on friday | geng maintenance | in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 8])
					->get();
					// ->ddRawSql();
				} else {								// not on friday | geng maintenance | not in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh, 'category' => 8])
					->get();
					// ->ddRawSql();
				}
			} else {									// not on friday | not geng maintenance
				if($dty == date('Y')){					// not on friday | not geng maintenance | in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh])
					->whereIn('category', [1,2,4])
					->get();
					// ->ddRawSql();
				} else {								// not on friday | not geng maintenance | not in same year
					$time = OptWorkingHour::
					// whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')
					where(function (Builder $query) use ($date){
						$query->whereDate('effective_date_start', '<=', $date)
						->whereDate('effective_date_end', '>=', $date);
					})
					->where(['year' => $dty, 'group' => $gwh])
					->whereIn('category', [1,2,4])
					->get();
					// ->ddRawSql();
				}
			}
		}
		return $time;
	}

	public static function unavailableworkinghourtime($date = '', $user = '')
	{
		// get year from leave date
		$dt = \Carbon\Carbon::parse($date);
		// echo $dt->year;
		// echo $dt->dayOfWeek;	// if = 5, meaning its friday so need to look at category 3

		$dty = $dt->copy()->year;

		// get group working hour from department
		$gwh = Staff::find($user)->belongstomanydepartment()->first()->wh_group_id;

		// pls be remind, this is for leave application, so if maintenance (group=1/$gwh=1) apply leave, we should give user category 8

		if($dt->copy()->dayOfWeek == 5) {				// friday
			if($gwh == 1){								// friday | geng maintenance
				if($dty == date('Y')){					// friday | geng maintenance | in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				} else {								// friday | geng maintenance | not in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				}
			} else {									// not geng maintenance
				if($dty == date('Y')){					// friday | not geng maintenance | in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 3])->get();
				} else {								// friday | not geng maintenance | not in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 3])->get();
				}
			}
		} else {										// not on friday
			if($gwh == 1){								// not on friday | geng maintenance
				if($dty == date('Y')){					// not on friday | geng maintenance | in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				} else {								// not on friday | geng maintenance | not in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				}
			} else {									// not on friday | not geng maintenance
				if($dty == date('Y')){					// not on friday | not geng maintenance | in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh])->whereIn('category', [1,2,4])->get();
				} else {								// not on friday | not geng maintenance | not in same year
					$time = OptWorkingHour::where(function (Builder $query) use ($date){
												$query->whereDate('effective_date_start', '<=', $date)
												->whereDate('effective_date_end', '>=', $date);
											})
											->where(['year' => $dty, 'group' => $gwh])->whereIn('category', [1,2,4])->get();
				}
			}
		}
		return $time;
	}
}
