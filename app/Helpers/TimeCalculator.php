<?php
namespace App\Helpers;

class TimeCalculator
{
	public function __construct()
	{
		$this->middleware(['auth']);
	}

	public static function total_time($times = []): string
	{
		$hh = 0;
		$mm = 0;
		$ss = 0;
		foreach ($times as $time)
		{
			sscanf( $time, '%d:%d:%d', $hours, $mins, $secs);
			$hh += $hours;
			$mm += $mins;
			$ss += $secs;
		}

		$mm += floor( $ss / 60 ); $ss = $ss % 60;
		$hh += floor( $mm / 60 ); $mm = $mm % 60;
		return sprintf('%02d:%02d:%02d', $hh, $mm, $ss);
	}


}
