<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* draws a calendar */
function draw_calendar($month,$year){

	include "database.php";

	// Data actual
	//$q_intval = $conn->prepare("SELECT DISTINCT(DAY(data_intval)) as day FROM intval WHERE MONTH(data_intval) = MONTH(CURRENT_DATE()) AND YEAR(data_intval) = YEAR(CURRENT_DATE())");

	// Data per paràmetre
	$q_intval = $conn->prepare("SELECT DISTINCT(DAY(data_intval)) as day FROM intval WHERE MONTH(data_intval) = :m AND YEAR(data_intval) = :y");
	$q_intval->bindParam(':m',$month);
	$q_intval->bindParam(':y',$year);
	$q_intval->execute();

	$working_days = array();

	for ($i=1; $i<=32; $i++) { array_push($working_days,0); }

	while($interval = $q_intval->fetch(PDO::FETCH_ASSOC)) { $working_days[(int)$interval['day']]=true; }

	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar" style="float:left;">';

	/* table headings */
	$headings = array('Dilluns','Dimarts','Dimecres','Dijous','Divendres','Dissabte','Diumenge');
	$calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

	/* days and weeks vars now ... */
	$running_day = date('N',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for($x = 1; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np"> </td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		
		$javascript = date("Y-m-");
		if ($list_day<10) $javascript .= "0$list_day";
		else $javascript .= "$list_day";

		if ( $list_day < date('j') ) {
			$calendar.= '<td class="calendar-day-past">';
		}else {
			if ($working_days[(int)$list_day]) {
				if ($list_day == date('j')) $calendar.= "<td class=\"calendar-day\" id=\"today\" onclick=\"show_day('".trim($javascript)."')\">";
				else $calendar.= "<td class=\"calendar-day\" onclick=\"show_day('".trim($javascript)."')\">";
			}else{
				$calendar.= '<td class="calendar-day-past"><span style="margin: auto;">Festiu</span>';
			}
		}
		
			/* add in the day number */
			$calendar.= '<div class="day-number">'.$list_day.'</div>';

			/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
			//if($running_day == 7) $calendar.= str_repeat('<p>Festiu</p>',1);
			
		$calendar.= '</td>';
		if($running_day == 7):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = 0;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np"> </td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';
	
	/* all done, return result */
	return $calendar;
}

if (isset($_GET['data'])) {
	echo '<h2><b style="color:white">'.date('M',strtotime($_GET['data'])).' '.date('Y',strtotime($_GET['data'])).'</b></h2>';
	echo draw_calendar(date('m',strtotime($_GET['data'])),date('Y',strtotime($_GET['data'])));
}


?>
