<?php

namespace App\Jobs;

// load model
use App\Models\HumanResources\HRAttendance;

// load db facade
use Illuminate\Database\Eloquent\Builder;

// load model
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HRAttendanceRemark;
use App\Models\HumanResources\HROutstationAttendance;

// load batch and queue
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

// load helper
use App\Helpers\UnavailableDateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load lib
use \Carbon\Carbon;

class AttendanceProcessJob implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $dataprocess;

	/**
	 * Create a new job instance.
	 */
	public function __construct($dataprocess)
	{
		$this->dataprocess = $dataprocess;
		// dd($this->dataprocess);
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$i = 1;
		if($this->dataprocess) {
			foreach ($this->dataprocess as $s) {
				/////////////////////////////
				// to determine working hour of each user
				$wh = UnavailableDateTime::workinghourtime($s->attend_date, $s->belongstostaff->id)->first();

				$in = Carbon::parse($s->in)->equalTo('00:00:00');
				$break = Carbon::parse($s->break)->equalTo('00:00:00');
				$resume = Carbon::parse($s->resume)->equalTo('00:00:00');
				$out = Carbon::parse($s->out)->equalTo('00:00:00');

				// looking for RESTDAY, WORKDAY & HOLIDAY
				$sun = Carbon::parse($s->attend_date)->dayOfWeek == 0;		// sunday
				$sat = Carbon::parse($s->attend_date)->dayOfWeek == 6;		// saturday
				$hdate = HRHolidayCalendar::
						where(function (Builder $query) use ($s){
							$query->whereDate('date_start', '<=', $s->attend_date)
							->whereDate('date_end', '>=', $s->attend_date);
						})
						->get();

				////////////////////////////////////////////////////////////////////////////////
				// get leave of each staff
				$l = HRLeave::where('staff_id', $s->staff_id)
						->where(function (Builder $query) {
							$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
						})
						->where(function (Builder $query) use ($s){
							$query->whereDate('date_time_start', '<=', $s->attend_date)
							->whereDate('date_time_end', '>=', $s->attend_date);
						})
						->first();

				// get overtime
				$o = HROvertime::where([['staff_id', $s->staff_id], ['ot_date', $s->attend_date], ['active', 1]])->first();

				// get outstation
				$os = HROutstation::where('staff_id', $s->staff_id)
						->where('active', 1)
						->where(function (Builder $query) use ($s){
							$query->whereDate('date_from', '<=', $s->attend_date)
							->whereDate('date_to', '>=', $s->attend_date);
						})
						->get();

				if($hdate->count()) {												// date holiday
					$dayt = OptDayType::find(3)->daytype;							// show what day: HOLIDAY
					$dtype = false;
					$s->update(['daytype_id' => 3]);
				} elseif($hdate->isEmpty()) {										// date not holiday
					if(Carbon::parse($s->attend_date)->dayOfWeek == 0) {			// sunday
						$dayt = OptDayType::find(2)->daytype;
						$dtype = false;
						$s->update(['daytype_id' => 2]);
					} elseif(Carbon::parse($s->attend_date)->dayOfWeek == 6) {		// saturday
						$sat = $s->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $s->attend_date)->first();
						if($sat) {													// determine if user belongs to sat group restday
							$dayt = OptDayType::find(2)->daytype;					// show what day: RESTDAY
							$dtype = false;
							$s->update(['daytype_id' => 2]);
						} else {
							$dayt = OptDayType::find(1)->daytype;					// show what day: WORKDAY
							$dtype = true;
							$s->update(['daytype_id' => 1]);
						}
					} else {														// all other day is working day
						$dayt = OptDayType::find(1)->daytype;						// show what day: WORKDAY
						$dtype = true;
						$s->update(['daytype_id' => 1]);
					}
				}

				if ($dtype) {

				}






			}
		}
	}
}
