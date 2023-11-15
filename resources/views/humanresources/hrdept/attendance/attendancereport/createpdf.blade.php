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
$pdf->SetFont('Arial', NULL, 9);

if ($sa) {
	$i = 0;
	foreach ($sa as $v) {
		$n = 0;
		$ha = HRAttendance::// ->whereIn('staff_id', $request->staff_id)
					whereIn('staff_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 52, 53, 54, 55, 56, 57, 58, 59, 65, 67, 68, 69, 71])
					->where(function (Builder $query) use ($request){
						// $query->whereDate('attend_date', '>=', $request->from)
						$query->whereDate('attend_date', '>=', '2023-11-01')
						// ->whereDate('attend_date', '<=', $request->to);
						->whereDate('attend_date', '<=', '2023-11-14');
					})
					->groupBy('hr_attendances.staff_id')
					->get();
		$pdf->Cell(30, 5, 'Order Date :', 0, 0, 'L');
		$pdf->SetFont('Arial', 'B', 9);
		$pdf->Cell(30, 5, 'test', 0, 0, 'L');
	}
}










/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$filename = 'Attendance Report.pdf';
	// use ob_get_clean() to make sure that the correct header is sent to the server
	ob_get_clean();
	$pdf->Output('I', $filename);											// <-- kalau nak bukak secara direct saja
	// $pdf->Output('D', $filename);										// <-- semata mata 100% download
	// $pdf->Output('F', storage_path().'/uploads/pdf/'.$filename);			// <-- send through email
