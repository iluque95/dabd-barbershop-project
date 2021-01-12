<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['hora']) && isset($_GET['data'])) { 

	include "database.php";

	$q_date = $conn->prepare("UPDATE cita SET presentat = 1 WHERE c_hora = :h AND c_data = :d");
	$q_date->bindParam(':h',$_GET['hora']);
	$q_date->bindParam(':d',$_GET['data']);
	$q_date->execute();
	
	echo "<input type='button' value='GENERA TIQUET' onclick=\"generate_ticket(this.parentNode.parentNode.rowIndex)\">";
}
?>
