<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['available_slots']="";

function draw_avalaibility($date) {

	include "database.php";

	$q_intval = $conn->prepare("SELECT * FROM intval WHERE data_intval = :d");
	$q_intval->bindParam(':d',$date);
	$q_intval->execute();

	$CURRENTTIME = new DateTime(date("H:i y-m-d"));
	$avalaibility="";

	while ($interval_row = $q_intval->fetch(PDO::FETCH_ASSOC)) {

		// Temps en segons
		$t_slot = $interval_row['temps_slot'] * 60;
		$files=0;
		
		$q_slot = $conn->prepare("SELECT hora_inici, express, reservat FROM slot WHERE int_hora_inici = :ihi AND int_hora_fi = :ihf AND int_data = :id");
		$q_slot->bindParam(':ihi',$interval_row['hora_inici']);
		$q_slot->bindParam(':ihf',$interval_row['hora_fi']);
		$q_slot->bindParam(':id',$interval_row['data_intval']);
		$q_slot->execute();

		while ($slot_row = $q_slot->fetch(PDO::FETCH_ASSOC)) {

			$files = (($files+1) % 8);
	
			$hora_inici = substr($slot_row['hora_inici'], 0, -3);
			$margin_slot = date("H:i",strtotime($hora_inici) + 300);			// Als cinc minuts de començar un nou slot es marca com expirat.
			$incrementa_slot = date("H:i",strtotime($hora_inici) + $t_slot);

			$OFFICETIME  = new DateTime($margin_slot." ".$interval_row['data_intval']);

			if ($CURRENTTIME  > $OFFICETIME) $avalaibility.= "<td class=\"a-td-ca\" align=\"middle\"><strong>".$hora_inici." - ".$incrementa_slot."<br/><span class=\"td\">Expirat</span></td>";
			elseif ($slot_row['reservat']) $avalaibility.= "<td class=\"a-td-na\" align=\"middle\"><strong>".$hora_inici." - ".$incrementa_slot."<br/><span class=\"td\">Reservat</span></td>";
			elseif ($slot_row['express']) $avalaibility.= "<td class=\"a-td-e\" align=\"middle\"><strong>".$hora_inici." - ".$incrementa_slot."<br/><span class=\"td\">Express</span></td>";
			else $avalaibility.= "<td class=\"a-td-a\" align=\"middle\"><strong>".$hora_inici." - ".$incrementa_slot."<br/><span class=\"td\">Lliure</span></td>";

			if (($CURRENTTIME  < $OFFICETIME) && !$slot_row['reservat']) $GLOBALS['available_slots'].="<option value=\"$hora_inici\">$hora_inici</option>";

			if ($files==0) $avalaibility.= "</tr><tr>";


		}

		$avalaibility.= "</tr><tr>";

	}

	return $avalaibility;
}

if (isset($_GET['data'])) echo draw_avalaibility($_GET['data']);

?>
