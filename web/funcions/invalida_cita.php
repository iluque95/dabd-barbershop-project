<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "database.php";

$CURRENTTIME = date("H:i");

// Incrementa cops cops fallits de l'usuari
$q_client = $conn->prepare("UPDATE client cl, cita ci SET cl.cops_fallida = cl.cops_fallida + 1 WHERE (ci.c_hora = :ct AND ci.c_data = CURRENT_DATE) AND (ci.email = cl.email)");
$q_client->bindParam(':ct',$CURRENTTIME);
$q_client->execute();

// Recupera quantitat d'slots consecutius
$q_slot = $conn->prepare("SELECT SUM(s.n_slots) as total FROM cita c, demanats d, servei s WHERE (c.c_hora = :ct AND c.c_data = CURRENT_DATE) AND ((d.cita_hora = c.c_hora AND d.cita_data = c.c_data) AND (d.servei = s.id))");
$q_slot->bindParam(':ct',$CURRENTTIME);
$q_slot->execute();
$res = $q_slot->fetch(PDO::FETCH_ASSOC);

$n_slot = $res['total'];

// Marca tots els slots de la cita lliures i el primer com express
$t_slot=300;

$q_slot = $conn->prepare("UPDATE slot SET reservat=0, express=0 WHERE hora_inici = :hi AND int_data = CURRENT_DATE");

$incrementa_slot = date("H:i",strtotime($CURRENTTIME) + $t_slot);

for ($i=1; $i < $n_slot; $i++) {
	$incrementa_slot = date("H:i",strtotime($incrementa_slot) + $t_slot);
	$q_slot->bindParam(':hi',$incrementa_slot);
	$q_slot->execute();
}

// Marca el primer com express
$q_slot = $conn->prepare("UPDATE slot SET reservat=0, express=1, descompte=100 WHERE hora_inici = :ct AND int_data = CURRENT_DATE");
$q_slot->bindParam(':ct',$CURRENTTIME);
$q_slot->execute();

?>
