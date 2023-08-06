<?php
$link = mysql_connect('localhost', 'root', 'root', 'testweb') or die(mysql_error());
mysql_select_db('testweb') or die(mysql_error());
$s1 = 'SELECT * FROM `hr_leave_entitlements`';
$q1 = mysql_query($s1) or die(mysql_error());

$i = 1;
while($r1 = mysql_fetch_assoc($q1) or die(mysql_error()) ) {
	echo $i++.' | '.$r1['staff_id'].' | '.$r1['year'].' | '.$r1['al_initialise'].' | '.$r1['al_adjustment'].' | '.$r1['al_balance'].' | '.$r1['mc_initialise'].' | '.$r1['mc_adjustment'].' | '.$r1['mc_balance'].' | '.$r1['maternity_initialise'].' | '.$r1['maternity_adjustment'].' | '.$r1['maternity_balance'].'<br/>';

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (is_null($r1['al_adjustment'])) {
		$r1['al_adjustment'] = 0;
	}
	if (is_null($r1['al_initialise'])) {
		$r1['al_initialise'] = 0;
	}
	$r1_annual_utilise = $r1['al_initialise'] + $r1['al_adjustment'] - $r1['al_balance'];
	echo "INSERT INTO `testweb`.`hr_leave_annuals` (`staff_id`, `year`, `annual_leave`, `annual_leave_adjustment`, `annual_leave_utilize`, `annual_leave_balance`, `created_at`, `updated_at`) VALUES (
	".$r1['staff_id'].",
	".$r1['year'].",
	".$r1['al_initialise'].",
	".$r1['al_adjustment'].",
	".$r1_annual_utilise.",
	".$r1['al_balance'].",
	'".date("Y-m-d H:i:s")."',
	'".date("Y-m-d H:i:s")."'
	)".'<br/>';
	$s2 = "INSERT INTO `testweb`.`hr_leave_annuals` (`staff_id`, `year`, `annual_leave`, `annual_leave_adjustment`, `annual_leave_utilize`, `annual_leave_balance`, `created_at`, `updated_at`) VALUES (".$r1['staff_id'].",".$r1['year'].",".$r1['al_initialise'].",".$r1['al_adjustment'].",".$r1_annual_utilise.",".$r1['al_balance'].",'".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
	// $q2 = mysql_query($s2) or die(mysql_error());

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (is_null($r1['mc_adjustment'])) {
		$r1['mc_adjustment'] = 0;
	}
	if (is_null($r1['mc_initialise'])) {
		$r1['mc_initialise'] = 0;
	}
	$r1_mc_utilise = $r1['mc_initialise'] + $r1['mc_adjustment'] - $r1['mc_balance'];

	echo "INSERT INTO `testweb`.`hr_leave_mc` (`staff_id`, `year`, `mc_leave`, `mc_leave_adjustment`, `mc_leave_utilize`, `mc_leave_balance`, `created_at`, `updated_at`) VALUES (
	".$r1['staff_id'].",
	".$r1['year'].",
	".$r1['mc_initialise'].",
	".$r1['mc_adjustment'].",
	".$r1_mc_utilise.",
	".$r1['mc_balance'].",
	'".date("Y-m-d H:i:s")."',
	'".date("Y-m-d H:i:s")."'
	)".'<br/>';
	$s3 = "INSERT INTO `testweb`.`hr_leave_mc` (`staff_id`, `year`, `mc_leave`, `mc_leave_adjustment`, `mc_leave_utilize`, `mc_leave_balance`, `created_at`, `updated_at`) VALUES (
	".$r1['staff_id'].",".$r1['year'].",".$r1['mc_initialise'].",".$r1['mc_adjustment'].",".$r1_mc_utilise.",".$r1['mc_balance'].",'".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
	// $q3 = mysql_query($s3) or die(mysql_error());

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// maternity. only insert if user is female (gender id = 2)
	echo "SELECT staffs.gender_id, staffs.`name`, staffs.id FROM hr_leave_maternities RIGHT JOIN staffs ON hr_leave_maternities.staff_id = staffs.id WHERE staffs.id = ".$r1['staff_id']." AND staffs.gender_id = 2".'<br/>';
	$s4 = "SELECT staffs.gender_id, staffs.`name`, staffs.id FROM hr_leave_maternities RIGHT JOIN staffs ON hr_leave_maternities.staff_id = staffs.id WHERE staffs.id = ".$r1['staff_id']." AND staffs.gender_id = 2";
	$q4 = mysql_query($s4) or die(mysql_error());
	if (mysql_num_rows($q4)) {
		echo "this is valid<br/>";

		if (is_null($r1['maternity_adjustment'])) {
			$r1['maternity_adjustment'] = 0;
		}
		if (is_null($r1['maternity_initialise'])) {
			$r1['maternity_initialise'] = 0;
		}
		if (is_null($r1['maternity_balance'])) {
			$r1['maternity_balance'] = 0;
		}
		$r1_maternity_utilise = $r1['maternity_initialise'] + $r1['maternity_adjustment'] - $r1['maternity_balance'];

		$s4 = "INSERT INTO `testweb`.`hr_leave_maternities` (`staff_id`, `year`, `maternity_leave`, `maternity_leave_adjustment`, `maternity_leave_utilize`, `maternity_leave_balance`, `created_at`, `updated_at`) VALUES (
	".$r1['staff_id'].",".$r1['year'].",".$r1['maternity_initialise'].",".$r1['maternity_adjustment'].",".$r1_maternity_utilise.",".$r1['maternity_balance'].",'".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
		echo $s4.'<br/>';
		$q4 = mysql_query($s4) or die(mysql_error());
	}

}
mysql_free_result($q1);

?>