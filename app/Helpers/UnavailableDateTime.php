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
		// dd([empty($entit->count()), empty($wh->count()), $entit->count()]);
		$nextyear = [];
		if(empty($entitannual->count() && $entitmc->count() && $entitmaternity->count()) || empty($wh->count())){
			foreach ($nystart_date->daysUntil($nyend_date) as $nydate) {
				$nextyear[] = $nydate->format('Y-m-d');
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
		foreach ($hdate as $nda) {
			$period = \Carbon\CarbonPeriod::create($nda->date_start, '1 days', $nda->date_end);
			foreach ($period as $key) {
				// echo 'moment("'.$key->format('Y-m-d').'"),';
				$holiday[] = $key->format('Y-m-d');
			}
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

		if(Setting::find(1)->active == 1) {		// double date checking
			// block self leave
			// make sure $request->id comes from table staff
			// $leaveday = HRLeave::where('staff_id', $id)->whereIn('leave_status_id', [4,5,6])->whereNull('leave_status_id')->whereRaw('"'.$d->copy()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->orwhereRaw('"'.$d->copy()->addYear()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->get();
			$leaveday = HRLeave::where('staff_id', $id)->where(function (Builder $query){
				$query->whereIn('leave_status_id', [4,5,6])
				->orwhereNull('leave_status_id');
			})
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
			// dd($leaveday);
			if(!is_null($leaveday)) {
				$leavday = [];
				foreach ($leaveday as $key) {
					$period1 = \Carbon\CarbonPeriod::create($key->date_time_start, '1 days', $key->date_time_end);
					foreach ($period1 as $key1) {
						$leavday[] = $key1->format('Y-m-d');
					}
				}
			} else {
				$leavday = [];
			}
		} else {
			$leavday = [];
		}

		$unavailableleave = Arr::collapse([$holiday, $sundays, $leavday, $saturdays, $nextyear]);
		return $unavailableleave;
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
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				} else {								// friday | geng maintenance | not in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				}
			} else {									// not geng maintenance
				if($dty == date('Y')){					// friday | not geng maintenance | in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 3])->get();
				} else {								// friday | not geng maintenance | not in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 3])->get();
				}
			}
		} else {										// not on friday
			if($gwh == 1){								// not on friday | geng maintenance
				if($dty == date('Y')){					// not on friday | geng maintenance | in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				} else {								// not on friday | geng maintenance | not in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh, 'category' => 8])->get();
				}
			} else {									// not on friday | not geng maintenance
				if($dty == date('Y')){					// not on friday | not geng maintenance | in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh])->whereIn('category', [1,2,4])->get();
				} else {								// not on friday | not geng maintenance | not in same year
					$time = OptWorkingHour::whereRaw('"'.$date.'" BETWEEN effective_date_start AND effective_date_end')->where(['year' => $dty, 'group' => $gwh])->whereIn('category', [1,2,4])->get();
				}
			}
		}
		return $time;
	}
}
