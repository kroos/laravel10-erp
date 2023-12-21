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

				// looking for leave of each staff
				// $l = $s->belongstostaff->hasmanyleave()
				$l = HRLeave::where('staff_id', $s->staff_id)
						->where(function (Builder $query) {
							$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
						})
						->where(function (Builder $query) use ($s){
							$query->whereDate('date_time_start', '<=', $s->attend_date)
							->whereDate('date_time_end', '>=', $s->attend_date);
						})
						->first();
				// dump($l);

				$o = HROvertime::where([['staff_id', $s->staff_id], ['ot_date', $s->attend_date], ['active', 1]])->first();

				$os = HROutstation::where('staff_id', $s->staff_id)
						->where('active', 1)
						->where(function (Builder $query) use ($s){
							$query->whereDate('date_from', '<=', $s->attend_date)
							->whereDate('date_to', '>=', $s->attend_date);
						})
						->get();

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

				if($hdate->isNotEmpty()) {											// date holiday
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

				// detect all
				if ($os->isNotEmpty()) {																							// outstation |
					if ($dtype) {																									// outstation | working
						if ($l) {																									// outstation | working | leave
							if ($in) {																								// outstation | working | leave | no in
								if ($break) {																						// outstation | working | leave | no in | no break
									if ($resume) {																					// outstation | working | leave | no in | no break | no resume
										if ($out) {																					// outstation | working | leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | leave | no in | no break | resume
										if ($out) {																					// outstation | working | leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | working | leave | no in | break
									if ($resume) {																					// outstation | working | leave | no in | break | no resume
										if ($out) {																					// outstation | working | leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | leave | no in | break | resume
										if ($out) {																					// outstation | working | leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// outstation | working | leave | in
								if ($break) {																						// outstation | working | leave | in | no break
									if ($resume) {																					// outstation | working | leave | in | no break | no resume
										if ($out) {																					// outstation | working | leave | in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | leave | in | no break | resume
										if ($out) {																					// outstation | working | leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | working | leave | in | break
									if ($resume) {																					// outstation | working | leave | in | break | no resume
										if ($out) {																					// outstation | working | leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | leave | in | break | resume
										if ($out) {																					// outstation | working | leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | leave | in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						} else {																									// outstation | working | no leave
							if ($in) {																								// outstation | working | no leave | no in
								if ($break) {																						// outstation | working | no leave | no in | no break
									if ($resume) {																					// outstation | working | no leave | no in | no break | no resume
										if ($out) {																					// outstation | working | no leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | no leave | no in | no break | resume
										if ($out) {																					// outstation | working | no leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | working | no leave | no in | break
									if ($resume) {																					// outstation | working | no leave | no in | break | no resume
										if ($out) {																					// outstation | working | no leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | no leave | no in | break | resume
										if ($out) {																					// outstation | working | no leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												if ($break == $resume) {															// check for break and resume is the same value
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
													} else {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
												}
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// outstation | working | no leave | in
								if ($break) {																						// outstation | working | no leave | in | no break
									if ($resume) {																					// outstation | working | no leave | in | no break | no resume
										if ($out) {																					// outstation | working | no leave | in | no break | no resume | no out
											if (Carbon::parse(now())->gt($s->attend_date)) {
												if (is_null($s->attendance_type_id)) {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
												} else {
													$ll = $s->belongstoopttcms->leave;
												}
											} else {
												if (is_null($s->attendance_type_id)) {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
												} else {
													$ll = $s->belongstoopttcms->leave;
												}
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | no leave | in | no break | resume
										if ($out) {																					// outstation | working | no leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | working | no leave | in | break
									if ($resume) {																					// outstation | working | no leave | in | break | no resume
										if ($out) {																					// outstation | working | no leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | working | no leave | in | break | resume
										if ($out) {																					// outstation | working | no leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												if ($break == $resume) {															// check for break and resume is the same value
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
													} else {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
												}
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | working | no leave | in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						}
					} else {																										// outstation | no working
						if ($l) {																									// outstation | no working | leave
							if ($in) {																								// outstation | no working | leave | no in
								if ($break) {																						// outstation | no working | leave | no in | no break
									if ($resume) {																					// outstation | no working | leave | no in | no break | no resume
										if ($out) {																					// outstation | no working | leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | leave | no in | no break | resume
										if ($out) {																					// outstation | no working | leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | no working | leave | no in | break
									if ($resume) {																					// outstation | no working | leave | no in | break | no resume
										if ($out) {																					// outstation | no working | leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | leave | no in | break | resume
										if ($out) {																					// outstation | no working | leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// outstation | no working | leave | in
								if ($break) {																						// outstation | no working | leave | in | no break
									if ($resume) {																					// outstation | no working | leave | in | no break | no resume
										if ($out) {																					// outstation | no working | leave | in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | leave | in | no break | resume
										if ($out) {																					// outstation | no working | leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | no working | leave | in | break
									if ($resume) {																					// outstation | no working | leave | in | break | no resume
										if ($out) {																					// outstation | no working | leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | leave | in | break | resume
										if ($out) {																					// outstation | no working | leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | leave | in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						} else {																									// outstation | no working | no leave
							if ($in) {																								// outstation | no working | no leave | no in
								if ($break) {																						// outstation | no working | no leave | no in | no break
									if ($resume) {																					// outstation | no working | no leave | no in | no break | no resume
										if ($out) {																					// outstation | no working | no leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | no leave | no in | no break | resume
										if ($out) {																					// outstation | no working | no leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | no working | no leave | no in | break
									if ($resume) {																					// outstation | no working | no leave | no in | break | no resume
										if ($out) {																					// outstation | no working | no leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | no leave | no in | break | resume
										if ($out) {																					// outstation | no working | no leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// outstation | no working | no leave | in
								if ($break) {																						// outstation | no working | no leave | in | no break
									if ($resume) {																					// outstation | no working | no leave | in | no break | no resume
										if ($out) {																					// outstation | no working | no leave | in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | no leave | in | no break | resume
										if ($out) {																					// outstation | no working | no leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// outstation | no working | no leave | in | break
									if ($resume) {																					// outstation | no working | no leave | in | break | no resume
										if ($out) {																					// outstation | no working | no leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// outstation | no working | no leave | in | break | resume
										if ($out) {																					// outstation | no working | no leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// outstation | no working | no leave | in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($os) {
												$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
											} else {
												$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						}
					}
				} else {																											// no outstation
					if ($dtype) {																									// no outstation | working
						if ($l) {																									// no outstation | working | leave
							if ($in) {																								// no outstation | working | leave | no in
								if ($break) {																						// no outstation | working | leave | no in | no break
									if ($resume) {																					// no outstation | working | leave | no in | no break | no resume
										if ($out) {																					// no outstation | working | leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype?->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype?->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | leave | no in | no break | resume
										if ($out) {																					// no outstation | working | leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype?->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype?->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | working | leave | no in | break
									if ($resume) {																					// no outstation | working | leave | no in | break | no resume
										if ($out) {																					// no outstation | working | leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | leave | no in | break | resume
										if ($out) {																					// no outstation | working | leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// no outstation | working | leave | in
								if ($break) {																						// no outstation | working | leave | in | no break
									if ($resume) {																					// no outstation | working | leave | in | no break | no resume
										if ($out) {																					// no outstation | working | leave | in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | leave | in | no break | resume
										if ($out) {																					// no outstation | working | leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | working | leave | in | break
									if ($resume) {																					// no outstation | working | leave | in | break | no resume
										if ($out) {																					// no outstation | working | leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | leave | in | break | resume
										if ($out) {																					// no outstation | working | leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | leave | in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												$ll = $l->belongstooptleavetype->leave_type_code;
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						} else {																									// no outstation | working | no leave
							if ($in) {																								// no outstation | working | no leave | no in
								if ($break) {																						// no outstation | working | no leave | no in | no break
									if ($resume) {																					// no outstation | working | no leave | no in | no break | no resume
										if ($out) {																					// no outstation | working | no leave | no in | no break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(1)->leave.'</a>';					// absent
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | no leave | no in | no break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | no leave | no in | no break | resume
										if ($out) {																					// no outstation | working | no leave | no in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					//  pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | no leave | no in | no break | resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | working | no leave | no in | break
									if ($resume) {																					// no outstation | working | no leave | no in | break | no resume
										if ($out) {																					// no outstation | working | no leave | no in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation |  outstation | working | no leave | no in | break | no resume | out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation |  outstation | working | no leave | no in | break | resume
										if ($out) {																					// no outstation |  outstation | working | no leave | no in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation |  outstation | working | no leave | no in | break | resume | out
											if (is_null($s->attendance_type_id)) {
												if ($break == $resume) {															// check for break and resume is the same value
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
												} else {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
												}
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// no outstation |  outstation | working | no leave | in
								if ($break) {																						// no outstation |  outstation | working | no leave | in | no break
									if ($resume) {																					// no outstation |  outstation | working | no leave | in | no break | no resume
										if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | no resume | no out
											if (Carbon::parse(now())->gt($s->attend_date)) {
												if (is_null($s->attendance_type_id)) {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
												} else {
													$ll = $s->belongstoopttcms->leave;
												}
											} else {
												$ll = false;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation |  outstation | working | no leave | in | no break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation |  outstation | working | no leave | in | no break | resume
										if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation |  outstation | working | no leave | in | no break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation |  outstation | working | no leave | in | break
									if ($resume) {																					// no outstation |  outstation | working | no leave | in | break | no resume
										if ($out) {																					// no outstation |  outstation | working | no leave | in | break | no resume | no out
											if (is_null($s->attendance_type_id)) {
												// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | no leave | in | break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | working | no leave | in | break | resume
										if ($out) {																					// no outstation | working | no leave | in | break | resume | no out
											if (is_null($s->attendance_type_id)) {
												if ($break == $resume) {															// check for break and resume is the same value
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
												} else {
													// $ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
												}
											} else {
												$ll = $s->belongstoopttcms->leave;
											}
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | working | no leave | in | break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													$s->update(['leave_id' => $l->id]);
													// $s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						}
					} else {																										// no outstation | no working
						if ($l) {																									// no outstation | no working | leave
							if ($in) {																								// no outstation | no working | leave | no in
								if ($break) {																						// no outstation | no working | leave | no in | no break
									if ($resume) {																					// no outstation | no working | leave | no in | no break | no resume
										if ($out) {																					// no outstation | no working | leave | no in | no break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | no in | no break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | leave | no in | no break | resume
										if ($out) {																					// no outstation | no working | leave | no in | no break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | no in | no break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | no working | leave | no in | break
									if ($resume) {																					// no outstation | no working | leave | no in | break | no resume
										if ($out) {																					// no outstation | no working | leave | no in | break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | no in | break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | leave | no in | break | resume
										if ($out) {																					// no outstation | no working | leave | no in | break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | no in | break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// no outstation | no working | leave | in
								if ($break) {																						// no outstation | no working | leave | in | no break
									if ($resume) {																					// no outstation | no working | leave | in | no break | no resume
										if ($out) {																					// no outstation | no working | leave | in | no break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | in | no break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | leave | in | no break | resume
										if ($out) {																					// no outstation | no working | leave | in | no break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | in | no break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | no working | leave | in | break
									if ($resume) {																					// no outstation | no working | leave | in | break | no resume
										if ($out) {																					// no outstation | no working | leave | in | break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | in | break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | leave | in | break | resume
										if ($out) {																					// no outstation | no working | leave | in | break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | leave | in | break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						} else {																									// no outstation | no working | no leave
							if ($in) {																								// no outstation | no working | no leave | no in
								if ($break) {																						// no outstation | no working | no leave | no in | no break
									if ($resume) {																					// no outstation | no working | no leave | no in | no break | no resume
										if ($out) {																					// no outstation | no working | no leave | no in | no break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | no in | no break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | no leave | no in | no break | resume
										if ($out) {																					// no outstation | no working | no leave | no in | no break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | no in | no break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | no working | no leave | no in | break
									if ($resume) {																					// no outstation | no working | no leave | no in | break | no resume
										if ($out) {																					// no outstation | no working | no leave | no in | break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | no in | break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | no leave | no in | break | resume
										if ($out) {																					// no outstation | no working | no leave | no in | break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | no in | break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							} else {																								// no outstation | no working | no leave | in
								if ($break) {																						// no outstation | no working | no leave | in | no break
									if ($resume) {																					// no outstation | no working | no leave | in | no break | no resume
										if ($out) {																					// no outstation | no working | no leave | in | no break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | in | no break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | no leave | in | no break | resume
										if ($out) {																					// no outstation | no working | no leave | in | no break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | in | no break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								} else {																							// no outstation | no working | no leave | in | break
									if ($resume) {																					// no outstation | no working | no leave | in | break | no resume
										if ($out) {																					// no outstation | no working | no leave | in | break | no resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | in | break | no resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									} else {																						// no outstation | no working | no leave | in | break | resume
										if ($out) {																					// no outstation | no working | no leave | in | break | resume | no out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										} else {																					// no outstation | no working | no leave | in | break | resume | out
											$ll = false;
											if ($o) {																									// overtime
												if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
													$s->update(['overtime_id' => $o->id]);
												} else {
													$s->update(['overtime_id' => null]);
												}
											}
											if($l) {																									// leave
												if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
													// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
													// $s->update(['leave_id' => $l->id]);
													$s->update(['leave_id' => NULL]);
												} else {															// otherwise just show the leave
													// $lea = $s->belongstoleave->id;
													// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
												}
											} else {
												$lea = NULL;
												$s->update(['leave_id' => NULL]);
											}
										}
									}
								}
							}
						}
					}
				}

				// if($l) {
				// 	if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
						// $lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
				// 		$s->update(['leave_id' => $l->id]);
				// 	} else {															// otherwise just show the leave
				// 		// $lea = $s->belongstoleave->id;
						// $lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
				// 	}
				// } else {
				// 	$lea = NULL;
				// 	$s->update(['leave_id' => NULL]);
				// }
				if(is_null($s->overtime_id)) {
					if ($o) {
						$s->update(['overtime_id' => $o->id]);
					} else {
						$s->update(['overtime_id' => NULL]);
					}
				}
				$attrem = HRAttendanceRemark::where(function(Builder $query) use ($s) {
													$query->whereDate('date_from', '>=', $s->attend_date)
													->whereDate('date_to', '<=', $s->attend_date);
												})
												->where('staff_id', $s->staff_id)
												->get();
				if ($attrem->count()) {
					if($s->daytype_id == 1) {
						$s->update([
							'remarks' => $attrem->first()?->attendance_remarks,
							'hr_remarks' => $attrem->first()?->hr_attendance_remarks,
						]);
					}
				}
				$outatt = HROutstationAttendance::where([['date_attend', $s->attend_date], ['staff_id', $s->staff_id], ['confirm', 1]])->get();
				if($outatt->count()) {
					if ($s->daytype_id == 1 && is_null($s->leave_id)) {
						$s->update([
							'in' => $outatt->first()?->in,
							'out' => $outatt->first()?->out,
						]);
					}
				}
			}
		}
	}
}
