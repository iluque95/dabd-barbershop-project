<?php
function draw_services() {

	include "funcions/database.php";

	$q_service = $conn->prepare("SELECT * FROM servei");
	$q_service->execute();
	$services="";

	while ($service_row = $q_service->fetch(PDO::FETCH_ASSOC)) {

		$services .= "<tr class='calendar-row'>";

		$services.= "<td class=\"calendar-day-past\" style=\"width:200px\"><input type=\"checkbox\" name=\"servei[]\" value=\"".$service_row['id']."\">".$service_row['descripcio']."</td>";
		$services.= "<td class=\"calendar-day-past\" align=\"center\">".$service_row['genere']."</td>";
		$services.= "<td class=\"calendar-day-past\" align=\"center\">+".$service_row['pts_proporciona']."</td>";
		$services.= "<td class=\"calendar-day-past\" align=\"center\">".$service_row['preu']." €/".$service_row['pts_cost']." pts</td>";
		$services.= "<td class=\"calendar-day-past\" align=\"center\">".$service_row['n_slots']."</td>";
		
		$services .= "</tr>";

	}

	return $services;

}
?>
