<?php
ini_set('max_execution_time', 3000);
// header('Content-type: application/pdf');

// use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\UnavailableDateTime;
use App\Helpers\TimeCalculator;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;

use Crabbly\Fpdf\Fpdf;

class Pdf extends Fpdf
{
	// Page header
	function Header()
	{
		// Logo
		// $this->Image('images/logo.png',150,10,20);
		$this->Image('images/logo.png',90,10,20);
		// Arial bold 15
		$this->SetFont('Arial','B',15);

		// set margin
		$this->SetX(10);
		$this->SetRightMargin(10);
		$this->SetLeftMargin(10);

		$this->SetTextColor(128);
		$this->Cell(0, 5, 'IPMA Industry Sdn Bhd', 0, 1, 'C');
		$this->SetFont('arial','B',10);
		$this->Cell(0, 5, 'Attendance Report', 0, 1, 'C');
		$this->SetFont('arial',NULL,7);
		$this->Cell(0, 5, 'Phone : +604 917 8799 / 917 1799 Email : ipma@ipmaindustry.com', 0, 1, 'C');

		// reset again for content
		$this->SetX(10);
		$this->SetRightMargin(10);
		$this->SetLeftMargin(10);
		// Line break
		$this->Ln(1);
	}

	// Page footer
	function Footer()
	{
		// due to multicell setLeftMargin from the body of the page
		// $this->SetLeftMargin(10);
		$this->SetTextColor(128);
		// Position at 3.0 cm from bottom
		$this->SetY(-18);
		$this->SetFont('Arial','I',6);
		$this->Cell(0, 4, 'Lot 1266, Bandar DarulAman Industrial Park, 06000, Jitra, Kedah Darul Aman', 0, 1, 'C');
		// $this->Cell(0, 4, 'Lot 1266, Bandar DarulAman Industrial Park, 06000, Jitra, Kedah Darul Aman '.$this->GetY(), 0, 1, 'C');	// just to check the position
		// Arial italic 5
		$this->SetFont('Arial', 'I', 5);
		// Page number
		$this->Cell(0,4,'Page '.$this->PageNo().'of {nb}', 0, 1, 'C');
		// $this->Cell(0,4,'Page '.$this->PageNo().'of {nb} '.$this->GetY(), 0, 1, 'C');	// just to check the position
	}
}

class PDF_MC_Table extends Pdf {
// variable to store widths and aligns of cells, and line height
	var $widths;
	var $aligns;
	var $lineHeight;
//Set the array of column widths
	function SetWidths($w){
		$this->widths=$w;
	}
//Set the array of column alignments
	function SetAligns($a){
		$this->aligns=$a;
	}
//Set line height
	function SetLineHeight($h){
		$this->lineHeight=$h;
	}
//Calculate the height of the row
	function Row($data)
	{
// number of line
		$nb=0;
// loop each data to find out greatest line number in a row.
		for($i=0;$i<count($data);$i++){
// NbLines will calculate how many lines needed to display text wrapped in specified width.
// then max function will compare the result with current $nb. Returning the greatest one. And reassign the $nb.
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		}

//multiply number of line with line height. This will be the height of current row
		$h=$this->lineHeight * $nb;
//Issue a page break first if needed
		$this->CheckPageBreak($h);
//Draw the cells of current row
		for($i=0;$i<count($data);$i++)
		{
// width of the current col
			$w=$this->widths[$i];
// alignment of the current col. if unset, make it left.
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
//Draw the border
			$this->Rect($x,$y,$w,$h);
//Print the text
			$this->MultiCell($w,5,$data[$i],0,$a);
//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
//Go to the next line
		$this->Ln($h);
	}
	function CheckPageBreak($h)
	{
//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}
	function NbLines($w,$txt)
	{
//calculate the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}
}

// Instanciation of inherited class
$pdf = new PDF_MC_Table('L','mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Attendance Report');

// reset font
$pdf->SetFont('Arial', NULL, 8);
// dd($sa);
if ($sa) {
foreach ($sa as $me) {
	$t[] = $me->staff_id;
}

$log = Login::whereIn('staff_id', $t)->orderBy('username')->get();
$i = 0;
$p = [];
	foreach ($log as $v) {
		$n = 0;
		$ha = HRAttendance::where('staff_id', $v->staff_id)
				->where(function (Builder $query) use ($request){
					$query->whereDate('attend_date', '>=', $request->from)
					->whereDate('attend_date', '<=', $request->to);
				})
				->get();

		$pdf->SetFont('Arial', 'B', 8);
		$pdf->Cell(20, 5, Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username, 0, 0, 'R');
		$pdf->Cell(50, 5, Staff::find($v->staff_id)->name, 0, 0, 'L');
		$pdf->SetFont('Arial', null, 8);
		$pdf->Cell(20, 5, 'Department :', 0, 0, 'R');
		$pdf->Cell(50, 5, Staff::find($v->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->department, 0, (!is_null(Staff::find($v->staff_id)->restday_group_id))?0:1, 'L');
		if (!is_null(Staff::find($v->staff_id)->restday_group_id)) {
			$pdf->Cell(20, 5, 'Group :', 0, 0, 'R');
			$pdf->Cell(50, 5, Staff::find($v->staff_id)->belongstorestdaygroup?->group, 0, 1, 'L');
		}

		/////////////////////////////
		// 14 columns
		$pdf->SetFont('Arial', null, 6);
		$pdf->Cell(10, 5, 'ID', 1, 0, 'C');
		$pdf->Cell(34, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(20, 5, 'Type', 1, 0, 'C');
		$pdf->Cell(13, 5, 'Cause', 1, 0, 'C');
		$pdf->Cell(17, 5, 'Leave', 1, 0, 'C');
		$pdf->Cell(21, 5, 'Date', 1, 0, 'C');
		$pdf->Cell(13, 5, 'In', 1, 0, 'C');
		$pdf->Cell(13, 5, 'Break', 1, 0, 'C');
		$pdf->Cell(13, 5, 'Resume', 1, 0, 'C');
		$pdf->Cell(13, 5, 'Out', 1, 0, 'C');
		$pdf->Cell(16, 5, 'Duration', 1, 0, 'C');
		$pdf->Cell(14, 5, 'Overtime', 1, 0, 'C');
		$pdf->Cell(15, 5, 'Outstation', 1, 0, 'C');
		$pdf->Cell(50, 5, 'Remarks', 1, 0, 'C');
		$pdf->Cell(15, 5, 'Exception', 1, 1, 'C');

		// starting PDF_MC_Table
		// set width for each column (5 columns)
		$pdf->SetWidths([10, 34, 20, 13, 17, 21, 13, 13, 13, 13, 16, 14, 15, 50, 15]);

		// set alignment
		$pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);

		// set line heights. This is the height of each lines, not rows.
		$pdf->SetLineHeight(5);
		/////////////////////////////

		// loop attendance for each staff
		foreach ($ha as $v1) {

			/////////////////////////////
			// to determine working hour of each user
			$wh = UnavailableDateTime::workinghourtime($v1->attend_date, $v->belongstostaff->id)->first();

			// looking for leave of each staff
			// $l = $v->belongstostaff->hasmanyleave()
			$l = HRLeave::where('staff_id', $v->staff_id)
					->where(function (Builder $query) {
						$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
					})
					->where(function (Builder $query) use ($v1){
						$query->whereDate('date_time_start', '<=', $v1->attend_date)
						->whereDate('date_time_end', '>=', $v1->attend_date);
					})
					->first();

			$o = HROvertime::where([['staff_id', $v->staff_id], ['ot_date', $v1->attend_date], ['active', 1]])->first();

			$os = HROutstation::where('staff_id', $v->staff_id)
					->where(function (Builder $query) use ($v1){
						$query->whereDate('date_from', '<=', $v1->attend_date)
						->whereDate('date_to', '>=', $v1->attend_date);
					})
					->get();

			$in = Carbon::parse($v1->in)->equalTo('00:00:00');
			$break = Carbon::parse($v1->break)->equalTo('00:00:00');
			$resume = Carbon::parse($v1->resume)->equalTo('00:00:00');
			$out = Carbon::parse($v1->out)->equalTo('00:00:00');

			// looking for RESTDAY, WORKDAY & HOLIDAY
			$sun = Carbon::parse($v1->attend_date)->dayOfWeek == 0;		// sunday
			$sat = Carbon::parse($v1->attend_date)->dayOfWeek == 6;		// saturday

			$hdate = HRHolidayCalendar::
					where(function (Builder $query) use ($v1){
						$query->whereDate('date_start', '<=', $v1->attend_date)
						->whereDate('date_end', '>=', $v1->attend_date);
					})
					->get();

			if($hdate->isNotEmpty()) {											// date holiday
				$dayt = OptDayType::find(3)->daytype;							// show what day: HOLIDAY
				$dtype = false;
			} elseif($hdate->isEmpty()) {										// date not holiday
				if(Carbon::parse($v1->attend_date)->dayOfWeek == 0) {			// sunday
					$dayt = OptDayType::find(2)->daytype;
					$dtype = false;
				} elseif(Carbon::parse($v1->attend_date)->dayOfWeek == 6) {		// saturday
					$sat = $v->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $v1->attend_date)->first();
					if($sat) {													// determine if user belongs to sat group restday
						$dayt = OptDayType::find(2)->daytype;					// show what day: RESTDAY
						$dtype = false;
					} else {
						$dayt = OptDayType::find(1)->daytype;					// show what day: WORKDAY
						$dtype = true;
					}
				} else {														// all other day is working day
					$dayt = OptDayType::find(1)->daytype;						// show what day: WORKDAY
					$dtype = true;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | leave | no in | no break | resume
									if ($out) {																					// outstation | working | leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | working | leave | no in | break
								if ($resume) {																					// outstation | working | leave | no in | break | no resume
									if ($out) {																					// outstation | working | leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | leave | no in | break | resume
									if ($out) {																					// outstation | working | leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							}
						} else {																								// outstation | working | leave | in
							if ($break) {																						// outstation | working | leave | in | no break
								if ($resume) {																					// outstation | working | leave | in | no break | no resume
									if ($out) {																					// outstation | working | leave | in | no break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | leave | in | no break | resume
									if ($out) {																					// outstation | working | leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | working | leave | in | break
								if ($resume) {																					// outstation | working | leave | in | break | no resume
									if ($out) {																					// outstation | working | leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | leave | in | break | resume
									if ($out) {																					// outstation | working | leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | leave | in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | no leave | no in | no break | resume
									if ($out) {																					// outstation | working | no leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | working | no leave | no in | break
								if ($resume) {																					// outstation | working | no leave | no in | break | no resume
									if ($out) {																					// outstation | working | no leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | no leave | no in | break | resume
									if ($out) {																					// outstation | working | no leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = OptTcms::find(4)->leave;					// outstation
											} else {
												$ll = null;					// pls check
											}
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							}
						} else {																								// outstation | working | no leave | in
							if ($break) {																						// outstation | working | no leave | in | no break
								if ($resume) {																					// outstation | working | no leave | in | no break | no resume
									if ($out) {																					// outstation | working | no leave | in | no break | no resume | no out
										if (Carbon::parse(now())->gt($v1->attend_date)) {
											if (is_null($v1->attendance_type_id)) {
												$ll = OptTcms::find(4)->leave;					// outstation
											} else {
												$ll = OptTcms::find($v1->attendance_type_id)->leave;
											}
										} else {
											if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
										}
									} else {																					// outstation | working | no leave | in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | no leave | in | no break | resume
									if ($out) {																					// outstation | working | no leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | working | no leave | in | break
								if ($resume) {																					// outstation | working | no leave | in | break | no resume
									if ($out) {																					// outstation | working | no leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | working | no leave | in | break | resume
									if ($out) {																					// outstation | working | no leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = OptTcms::find(4)->leave;					// outstation
											} else {
												$ll = null;					// pls check
											}
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | working | no leave | in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | leave | no in | no break | resume
									if ($out) {																					// outstation | no working | leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | no working | leave | no in | break
								if ($resume) {																					// outstation | no working | leave | no in | break | no resume
									if ($out) {																					// outstation | no working | leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | leave | no in | break | resume
									if ($out) {																					// outstation | no working | leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							}
						} else {																								// outstation | no working | leave | in
							if ($break) {																						// outstation | no working | leave | in | no break
								if ($resume) {																					// outstation | no working | leave | in | no break | no resume
									if ($out) {																					// outstation | no working | leave | in | no break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | leave | in | no break | resume
									if ($out) {																					// outstation | no working | leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | no working | leave | in | break
								if ($resume) {																					// outstation | no working | leave | in | break | no resume
									if ($out) {																					// outstation | no working | leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | leave | in | break | resume
									if ($out) {																					// outstation | no working | leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | leave | in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | no leave | no in | no break | resume
									if ($out) {																					// outstation | no working | no leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | no working | no leave | no in | break
								if ($resume) {																					// outstation | no working | no leave | no in | break | no resume
									if ($out) {																					// outstation | no working | no leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | no leave | no in | break | resume
									if ($out) {																					// outstation | no working | no leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							}
						} else {																								// outstation | no working | no leave | in
							if ($break) {																						// outstation | no working | no leave | in | no break
								if ($resume) {																					// outstation | no working | no leave | in | no break | no resume
									if ($out) {																					// outstation | no working | no leave | in | no break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | no leave | in | no break | resume
									if ($out) {																					// outstation | no working | no leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// outstation | no working | no leave | in | break
								if ($resume) {																					// outstation | no working | no leave | in | break | no resume
									if ($out) {																					// outstation | no working | no leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// outstation | no working | no leave | in | break | resume
									if ($out) {																					// outstation | no working | no leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// outstation | no working | no leave | in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(4)->leave;					// outstation
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								} else {																						// no outstation | working | leave | no in | no break | resume
									if ($out) {																					// no outstation | working | leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								}
							} else {																							// no outstation | working | leave | no in | break
								if ($resume) {																					// no outstation | working | leave | no in | break | no resume
									if ($out) {																					// no outstation | working | leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								} else {																						// no outstation | working | leave | no in | break | resume
									if ($out) {																					// no outstation | working | leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								}
							}
						} else {																								// no outstation | working | leave | in
							if ($break) {																						// no outstation | working | leave | in | no break
								if ($resume) {																					// no outstation | working | leave | in | no break | no resume
									if ($out) {																					// working | leave | in | no break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								} else {																						// no outstation | working | leave | in | no break | resume
									if ($out) {																					// no outstation | working | leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								}
							} else {																							// no outstation | working | leave | in | break
								if ($resume) {																					// no outstation | working | leave | in | break | no resume
									if ($out) {																					// no outstation | working | leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									}
								} else {																						// no outstation | working | leave | in | break | resume
									if ($out) {																					// no outstation | working | leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
										}
									} else {																					// no outstation | working | leave | in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = $v1->belongstoopttcms->leave;
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
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(1)->leave;					// absent
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation | working | no leave | no in | no break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(2)->leave;					// half absent
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// no outstation | working | no leave | no in | no break | resume
									if ($out) {																					// no outstation | working | no leave | no in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					//  pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation | working | no leave | no in | no break | resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(2)->leave;					// half absent
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							} else {																							// no outstation | working | no leave | no in | break
								if ($resume) {																					// no outstation | working | no leave | no in | break | no resume
									if ($out) {																					// no outstation | working | no leave | no in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation |  outstation | working | no leave | no in | break | no resume | out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								} else {																						// no outstation |  outstation | working | no leave | no in | break | resume
									if ($out) {																					// no outstation |  outstation | working | no leave | no in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation |  outstation | working | no leave | no in | break | resume | out
										if (is_null($v1->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = OptTcms::find(2)->leave;					// half absent
											} else {
												$ll = null;					// pls check
											}
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									}
								}
							}
						} else {																								// no outstation |  outstation | working | no leave | in
							if ($break) {																						// no outstation |  outstation | working | no leave | in | no break
								if ($resume) {																					// no outstation |  outstation | working | no leave | in | no break | no resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | no resume | no out
										if (Carbon::parse(now())->gt($v1->attend_date)) {
											if (is_null($v1->attendance_type_id)) {
												$ll = OptTcms::find(2)->leave;					// half absent
											} else {
												$ll = OptTcms::find($v1->attendance_type_id)->leave;
											}
										} else {
											$ll = false;
										}
									} else {																					// no outstation |  outstation | working | no leave | in | no break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation |  outstation | working | no leave | in | no break | resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = null;					// pls check
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation |  outstation | working | no leave | in | no break | resume | out
										$ll = false;
									}
								}
							} else {																							// no outstation |  outstation | working | no leave | in | break
								if ($resume) {																					// no outstation |  outstation | working | no leave | in | break | no resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | break | no resume | no out
										if (is_null($v1->attendance_type_id)) {
											$ll = OptTcms::find(2)->leave;					// half absent
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation | working | no leave | in | break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | working | no leave | in | break | resume
									if ($out) {																					// no outstation | working | no leave | in | break | resume | no out
										if (is_null($v1->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = OptTcms::find(2)->leave;					// half absent
											} else {
												$ll = null;					// pls check
											}
										} else {
											$ll = OptTcms::find($v1->attendance_type_id)->leave;
										}
									} else {																					// no outstation | working | no leave | in | break | resume | out
										$ll = false;
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
									} else {																					// no outstation | no working | leave | no in | no break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | leave | no in | no break | resume
									if ($out) {																					// no outstation | no working | leave | no in | no break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | no in | no break | resume | out
										$ll = false;
									}
								}
							} else {																							// no outstation | no working | leave | no in | break
								if ($resume) {																					// no outstation | no working | leave | no in | break | no resume
									if ($out) {																					// no outstation | no working | leave | no in | break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | no in | break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | leave | no in | break | resume
									if ($out) {																					// no outstation | no working | leave | no in | break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | no in | break | resume | out
										$ll = false;
									}
								}
							}
						} else {																								// no outstation | no working | leave | in
							if ($break) {																						// no outstation | no working | leave | in | no break
								if ($resume) {																					// no outstation | no working | leave | in | no break | no resume
									if ($out) {																					// no outstation | no working | leave | in | no break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | in | no break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | leave | in | no break | resume
									if ($out) {																					// no outstation | no working | leave | in | no break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | in | no break | resume | out
										$ll = false;
									}
								}
							} else {																							// no outstation | no working | leave | in | break
								if ($resume) {																					// no outstation | no working | leave | in | break | no resume
									if ($out) {																					// no outstation | no working | leave | in | break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | in | break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | leave | in | break | resume
									if ($out) {																					// no outstation | no working | leave | in | break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | leave | in | break | resume | out
										$ll = false;
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
									} else {																					// no outstation | no working | no leave | no in | no break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | no leave | no in | no break | resume
									if ($out) {																					// no outstation | no working | no leave | no in | no break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | no in | no break | resume | out
										$ll = false;
									}
								}
							} else {																							// no outstation | no working | no leave | no in | break
								if ($resume) {																					// no outstation | no working | no leave | no in | break | no resume
									if ($out) {																					// no outstation | no working | no leave | no in | break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | no in | break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | no leave | no in | break | resume
									if ($out) {																					// no outstation | no working | no leave | no in | break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | no in | break | resume | out
										$ll = false;
									}
								}
							}
						} else {																								// no outstation | no working | no leave | in
							if ($break) {																						// no outstation | no working | no leave | in | no break
								if ($resume) {																					// no outstation | no working | no leave | in | no break | no resume
									if ($out) {																					// no outstation | no working | no leave | in | no break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | in | no break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | no leave | in | no break | resume
									if ($out) {																					// no outstation | no working | no leave | in | no break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | in | no break | resume | out
										$ll = false;
									}
								}
							} else {																							// no outstation | no working | no leave | in | break
								if ($resume) {																					// no outstation | no working | no leave | in | break | no resume
									if ($out) {																					// no outstation | no working | no leave | in | break | no resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | in | break | no resume | out
										$ll = false;
									}
								} else {																						// no outstation | no working | no leave | in | break | resume
									if ($out) {																					// no outstation | no working | no leave | in | break | resume | no out
										$ll = false;
									} else {																					// no outstation | no working | no leave | in | break | resume | out
										$ll = false;
									}
								}
							}
						}
					}
				}
			}

			if($l) {
				$lea = 'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year;
			} else {
				$lea = NULL;
			}

			if ($in == '00:00:00') {
				$in1 = null;
			} else {
				if (Carbon::parse($v1->in)->gt($wh?->time_start_am)) {
					$in1 = Carbon::parse($v1->in)->format('g:i a');
				} else {
					$in1 = Carbon::parse($v1->in)->format('g:i a');
				}
			}
			if ($break == '00:00:00') {
				$break1 = null;
			} else {
				$break1 = Carbon::parse($v1->break)->format('g:i a');
			}
			if ($resume == '00:00:00') {
				$resume1 = null;
			} else {
				$resume1 = Carbon::parse($v1->resume)->format('g:i a');
			}
			if ($out == '00:00:00') {
				$out1 = null;
			} else {
				$out1 = Carbon::parse($v1->out)->format('g:i a');
			}
			if ($v1->time_work_hour == '00:00:00') {
				$workhour = null;
			} else {
				$workhour = $v1->time_work_hour;
			}
			if (!is_null($os)) {
				// $cust = $os?->belongstocustomer?->customer;
			} else {
				$cust = null;
			}

			$ort = $o?->belongstoovertimerange?->where('active', 1)->first()?->total_time;
			if (!is_null($ort)) {
				$p[$i][$n] = Carbon::parse($o?->belongstoovertimerange?->where('active', 1)->first()?->total_time)->format('H:i:s');
			} else {
				$p[$i][$n] = Carbon::parse('00:00:00')->format('H:i:s');
			}

			$pdf->Row([
				Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username,
				Staff::find($v->staff_id)->name,
				$dayt,
				$ll,
				$lea,
				Carbon::parse($v1->attend_date)->format('j M Y'),
				$in1,
				$break1,
				$resume1,
				$out1,
				$workhour,
				$ort,
				null,
				$v1->remarks.'    '.$v1->hr_remarks,
				$v1->exception,
			]);
			$n++;
		}
		$pdf->Cell(183, 5, 'Total Overtime :', 1, 0, 'R');
		$pdf->Cell(14, 5, TimeCalculator::total_time($p[$i]), 1, 0, 'C');
		$pdf->Cell(80, 5, null, 1, 1, 'C');
		$pdf->Ln();
		$i++;
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$filename = 'Attendance Report '.Carbon::parse($request->from)->format('j M Y').' - '.Carbon::parse($request->to)->format('j M Y').'.pdf';
	// use ob_get_clean() to make sure that the correct header is sent to the server
	ob_get_clean();
	$pdf->Output('I', $filename);											// <-- kalau nak bukak secara direct saja
	// $pdf->Output('D', $filename);										// <-- semata mata 100% download
	// $pdf->Output('F', storage_path().'/uploads/pdf/'.$filename);			// <-- send through email
