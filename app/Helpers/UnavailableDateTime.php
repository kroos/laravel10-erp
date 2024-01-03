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
		// get 3 years
		$now = now();
		$nowyear = $now->copy()->year;
		$lastyear = $nowyear - 1;
		$lasttwoyear = $lastyear - 1;
		$nextyear = $nowyear + 1;

		///////////////////////////////////////////////////////////
		// get 3 years date
		$datenowyearstart = $now->copy()->startOfYear();
		$datenowyearend = $now->copy()->endOfYear();

		$datelastyearend = $datenowyearstart->copy()->subDay();
		$datelastyearstart = $datelastyearend->copy()->startOfYear();

		$datenextyearstart = $datenowyearend->copy()->addDay();
		$datenextyearend = $datenextyearstart->copy()->endOfYear();
		// dd($datelastyearstart, $datelastyearend, $datenowyearstart, $datenowyearend, $datenextyearstart, $datenextyearend);

		///////////////////////////////////////////////////////////
		// block next year date till entitlement and working hour were generate
		// block next year if entitlements and working hours not set
		$entitannual = HRLeaveAnnual::where('year', $nextyear)->get();
		$entitmc = HRLeaveMC::where('year', $nextyear)->get();
		$entitmaternity = HRLeaveMaternity::where('year', $nextyear)->get();
		$wh = OptWorkingHour::where('year', $nextyear)->get();
		// dd([$entitannual, $entitmc, $entitmaternity, $wh]);
		// dd([empty($entitannual->count()), empty($entitmc->count()), empty($entitmaternity->count()), empty($wh->count())]);
		// dd([ empty($entitannual->count()) && empty($entitmc->count()) && empty($entitmaternity->count()) && empty($wh->count()) ]);
		// dd(empty($entitannual->count() && $entitmc->count() && $entitmaternity->count()) && empty($wh->count()) );
		if (Setting::find(6)->active == 1) {																								// block next year leave (setting): enable is 1
			foreach ($datenextyearstart->daysUntil($datenextyearend) as $nydate) {															// straight away block next year
				$nextoneyear[] = $nydate->format('Y-m-d');
			}
		} else {																													// no block next year leave (setting):
			if(empty($entitannual->count()) && empty($entitmc->count()) && empty($entitmaternity->count()) && empty($wh->count())) {	// check the tables if its there 1st, endure all are ready
				foreach ($datenextyearstart->daysUntil($datenextyearend) as $nydate) {														// table not ready
					$nextoneyear[] = $nydate->format('Y-m-d');
				}
			} else {																												// table ready
				$nextoneyear = [];
			}
		}

		///////////////////////////////////////////////////////////
		// get date for last year
		if (Setting::find(7)->active == 1) {																							// open last year leave (setting): enable is 1 | block is 1
			foreach ($datelastyearstart->daysUntil($datelastyearend) as $v6) {
				$lastoneyear[] = $v6->format('Y-m-d');
			}
		} else {
			$lastoneyear = [];
		}
		///////////////////////////////////////////////////////////
		// block all dates for last 2 year since 2021
		foreach (Carbon::create($lasttwoyear, 1, 1)->daysUntil($datelastyearstart->copy()->subDay()) as $v5) {
			$last2year[] = $v5->format('Y-m-d');
		}

		///////////////////////////////////////////////////////////
		// block all sunday for 3 consecutives year
		foreach ($datelastyearstart->daysUntil($datenextyearend) as $date) {
			if ($date->dayOfWeek === Carbon::SUNDAY) {
				$sundays[] = $date->format('Y-m-d');
			}
		}

		///////////////////////////////////////////////////////////
		// block all holiday date for 3 consecutives year
		$hdate = HRHolidayCalendar::where(function (Builder $query) use ($lastyear, $nextyear){
										$query->whereYear('date_start', '>=', $lastyear)
										->whereYear('date_end', '<=', $nextyear);
									})
									->orderBy('date_start')
									// ->ddRawSql();
									->get();
		if ($hdate->count()) {
			foreach ($hdate as $nda) {
				$period = (Carbon::parse($nda->date_start))->daysUntil($nda->date_end);
				foreach ($period as $key) {
					$holiday[] = $key->format('Y-m-d');
				}
			}
		} else {
			$holiday = [];
		}

		///////////////////////////////////////////////////////////
		// block saturday according to group for 3 consecutives year
		$sat = Staff::find($id)?->belongstorestdaygroup?->hasmanyrestdaycalendar()
								->where(function (Builder $query) use ($lastyear, $nextyear){
									$query->whereYear('saturday_date', '>=', $lastyear)
									->whereYear('saturday_date', '<=', $nextyear);
								})
								// ->ddRawSql();
								->get();
		// $saturdays = [];
		if(!is_null($sat)) {
			foreach ($sat as $key) {
				$saturdays[] = $key->saturday_date;
			}
		} else {
			$saturdays = [];
		}

		///////////////////////////////////////////////////////////
		// always block 1 or more days leave for 3 consecutives year
		$aleaveday = HRLeave::where('staff_id', $id)
							->where('leave_type_id', '!=', 9)
							->where(function (Builder $query){
								$query->whereIn('leave_status_id', [5,6])
								->orwhereNull('leave_status_id');
							})
							->where(function (Builder $query) use ($lastyear, $nextyear){
								$query->whereYear('date_time_start', '>=', $lastyear)
								->whereYear('date_time_end', '<=', $nextyear);
							})
							// ->ddRawSql();
							->get();

		if($aleaveday->count()) {
			foreach ($aleaveday as $v1) {
				$period1 = CarbonPeriod::create($v1->date_time_start, '1 days', $v1->date_time_end);
				foreach ($period1 as $key1) {
					$leavday1[] = $key1->format('Y-m-d');
				}
			}
		} else {
			$leavday1 = [];
		}

		///////////////////////////////////////////////////////////
		// just unblock half day leave for 3 consecutives year
		$hleaveday = HRLeave::where('staff_id', $id)
							->where(function (Builder $query){
								$query->whereIn('leave_status_id', [5,6])
								->orwhereNull('leave_status_id');
							})
							->where('leave_cat', 2)
							->where(function (Builder $query) use ($lastyear, $nextyear){
								$query->whereYear('date_time_start', '>=', $lastyear)
								->whereYear('date_time_end', '<=', $nextyear);
							})
							// ->ddRawSql();
							->get();

		if($hleaveday->count()) {
			foreach ($hleaveday as $v2) {
				$period2 = CarbonPeriod::create($v2->date_time_start, '1 days', $v2->date_time_end);
				foreach ($period2 as $key2) {
					$leavday2[] = $key2->format('Y-m-d');
				}
			}
		} else {
			$leavday2 = [];
		}

		///////////////////////////////////////////////////////////
		// get TF leave for 3 consecutives year
		$tfleaveday = HRLeave::where('staff_id', $id)
							->where(function (Builder $query){
								$query->whereIn('leave_status_id', [5,6])
								->orwhereNull('leave_status_id');
							})
							->where('leave_type_id', 9)
							->where(function (Builder $query) use ($lastyear, $nextyear){
								$query->whereYear('date_time_start', '>=', $lastyear)
								->whereYear('date_time_end', '<=', $nextyear);
							})
							// ->ddRawSql();
							->get();

		if($tfleaveday->count()) {
			foreach ($tfleaveday as $v3) {
				$period3 = CarbonPeriod::create($v3->date_time_start, '1 days', $v3->date_time_end);
				foreach ($period3 as $key3) {
					$leavday3[] = $key3->format('Y-m-d');
				}
			}
		} else {
			$leavday3 = [];
		}

		///////////////////////////////////////////////////////////
		if(Setting::find(1)->active == 1) {																				// overlapped leave date checking
			$leavday = array_diff($leavday1, $leavday2, $leavday3);														// remove 1 value from array so not blocking date with half day
		} else {
			$leavday = $leavday1;
		}

		///////////////////////////////////////////////////////////
		$unavalabledate = Arr::collapse([$last2year, $holiday, $sundays, $saturdays, $lastoneyear, $nextoneyear, $leavday]);
		return $unavalabledate;
	}

	public static function unblockhalfdayleave($id = '')
	{
		// get 3 years
		$now = now();
		$nowyear = $now->copy()->year;
		$lastyear = $nowyear - 1;
		$lasttwoyear = $lastyear - 1;
		$nextyear = $nowyear + 1;

		// get 3 years date
		$datenowyearstart = $now->copy()->startOfYear();
		$datenowyearend = $now->copy()->endOfYear();
		$datelastyearend = $datenowyearstart->copy()->subDay();
		$datelastyearstart = $datelastyearend->copy()->startOfYear();
		$datenextyearstart = $datenowyearend->copy()->addDay();
		$datenextyearend = $datenextyearstart->copy()->endOfYear();
		// dd($datelastyearstart, $datelastyearend, $datenowyearstart, $datenowyearend, $datenextyearstart, $datenextyearend);

		///////////////////////////////////////////////////////////

		$d = Carbon::now(config('app.timezone'));

		$hleaveday1 = HRLeave::where('staff_id', $id)
							->where(function (Builder $query){
								$query->whereIn('leave_status_id', [5,6])
								->orwhereNull('leave_status_id');
							})
							->whereNotNull('half_type_id')
							->where(function (Builder $query) use ($lastyear, $nextyear){
								$query->whereYear('date_time_start', '<=', $lastyear)
								->whereYear('date_time_end', '>=', $nextyear);
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
