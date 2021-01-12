<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function get_time($date) {

	include "database.php";

	$q_slots="";

	if (new DateTime(date('Y-m-d')) == new DateTime($date)) {
		$q_slots = $conn->prepare("SELECT TIME_FORMAT(hora_inici,\"%H:%i\") as temps FROM slot WHERE (CURRENT_TIME <= (hora_inici + INTERVAL 5 MINUTE)) AND NOT reservat");
		$q_slots->execute();
	}else if(new DateTime(date('Y-m-d')) < new DateTime($date)) {
		$q_slots = $conn->prepare("SELECT TIME_FORMAT(hora_inici,\"%H:%i\") as temps FROM slot WHERE int_data = :d AND NOT reservat");
		$q_slots->bindParam(':d',$date);
		$q_slots->execute();
	}

	$time="";

	while ($slots_row = $q_slots->fetch(PDO::FETCH_ASSOC)) {
		$time .= "<option value=\"".$slots_row['temps']."\">".$slots_row['temps']."</option>";
	}

	return $time;
}

if (isset($_GET['data'])) echo get_time($_GET['data']);

?>
