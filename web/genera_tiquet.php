<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['hora']) && isset($_GET['data'])) { 

	include "funcions/database.php";

	// Punts del usuari
	$u_pts = 0;
	$q_client = $conn->prepare("SELECT cl.pts_acumulats, cl.nom FROM cita ci, client cl WHERE (ci.c_hora = :hc AND ci.c_data = CURRENT_DATE) AND (cl.email = ci.email)");
	$q_client->bindParam(':hc',$_GET['hora']);
	$q_client->execute();
	$res = $q_client->fetch(PDO::FETCH_ASSOC);
	$u_pts = $res['pts_acumulats'];
	$nom = $res['nom'];

	// Recompte del preu total
	$q_service = $conn->prepare("SELECT s.preu, s.pts_proporciona, s.pts_cost FROM cita c, servei s, demanats d WHERE (c.c_hora = :hc AND c.c_data = CURRENT_DATE) AND (d.cita_hora = c.c_hora AND d.cita_data = c.c_data) AND (d.servei = s.id) ORDER BY s.pts_cost DESC");
	$q_service->bindParam(':hc',$_GET['hora']);
	$q_service->execute();
	$price = 0;
	$o_pts = 0;
	$c_pts = 0;

	while ($service_row = $q_service->fetch(PDO::FETCH_ASSOC)) {

		if ($u_pts >= $service_row['pts_cost']) {
			$u_pts -= $service_row['pts_cost'];
			$c_pts += $service_row['pts_cost'];
		}else{
			$price += $service_row['preu'];
		}

		$o_pts += $service_row['pts_proporciona'];	
	}

	$u_pts += $o_pts;

	$q_ticket = $conn->prepare("INSERT INTO ticket VALUES (CURRENT_TIME, :hc, :dc, :p, :pg, :pu)");
	$q_ticket->bindParam(':hc',$_GET['hora']);
	$q_ticket->bindParam(':dc',$_GET['data']);
	$q_ticket->bindParam(':p',$price);
	$q_ticket->bindParam(':pg',$o_pts);
	$q_ticket->bindParam(':pu',$c_pts);
	$q_ticket->execute();

	// Actualitza l'usuari
	$q_client = $conn->prepare("UPDATE client cl, cita ci SET cl.pts_acumulats = :p WHERE (ci.c_hora = :hc AND ci.c_data = CURRENT_DATE) AND (cl.email = ci.email)");
	$q_client->bindParam(':hc',$_GET['hora']);
	$q_client->bindParam(':p',$u_pts);
	$q_client->execute();

	$hora_cita = $_GET['hora'];
	$data_cita = date("d/m/Y",strtotime($_GET['data']));
	$hora_actual = date("H:i");

}

echo "
<table align=\"left\">
<tr>
	<td align=\"center\" style=\"border-style: dotted solid; border-width:2px\">
	NEW FASHION HAIR STYLE SHOP<br/>
	Av. Victor Balaguer, 1<br/>
	Vilanova i la Geltrú<br/>
	</td>
</tr>
<tr>
	<td align=\"center\" style=\"border-style: dotted solid; border-top: 0px; border-width:2px\">
	TICKET SERVEIS<br/><br/>
	|||||||||||||||||<br/>
	|||||||||||||||||<br/><br/>
	<div style='text-align:left;margin-left:20%;'>Hora generació: $hora_actual <br/>
	Client: $nom               <br/>
	Hora cita: $hora_cita        <br/>
	Data cita: $data_cita        <br/>
	Atès per: Itiel              <br/>
    </div>                             <br/>
	<div style='text-align:left;margin-left:20%;'>*********************        <br/>
	Preu: $price €                <br/>
	Punts consumits: $c_pts      <br/>
	Punts guanyats: $o_pts       <br/>
	*********************        <br/> 
	</div>							 <br/>
    Fins la propera visita. 

	</td>
</tr>
</table>
";


?>