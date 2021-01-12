<!DOCTYPE html>
<html>
<style>
/* calendar */
table.calendar		{ border-left:1px solid #999; }
tr.calendar-row	{  }
td.calendar-day	{ min-height:80px; font-size:11px; position:relative; } * html div.calendar-day { height:80px; }
td.calendar-day:hover	{ background:#eceff5; }
td.calendar-day-np	{ background:#ccc; min-height:80px; } * html div.calendar-day-np { height:80px; }
td.calendar-day-head { background:#808080; font-weight:bold; text-align:center; width:120px; padding:5px; border-bottom:1px solid #999; border-top:1px solid #999; border-right:1px solid #999; }
div.day-number		{ background:#999; padding:5px; color:#fff; font-weight:bold; float:right; margin:-5px -5px 0 0; width:20px; text-align:center; }
td.calendar-day-past { background:#ccc; min-height:80px; font-size:11px; position:relative; } * html div.calendar-day { height:80px; }
/* shared */
td.calendar-day, td.calendar-day-np, td.calendar-day-past { width:120px; padding:5px; border-bottom:1px sol}
td#today {border: 2px solid red;}


table.availability {
  border-collapse: collapse;
  border: 1px solid black;
}

td.a-td, td.a-td-na, td.a-td-a, td.a-td-ca{
  border: 1px solid black;
}

td.a-td-na {background-color: red;}

td.a-td-a {background-color: green;}

td.a-td-e {background-color: orange;}

td.a-td-ca {background-color: grey;}

span.td {font-size: 12px;}

body {
 background-image: url("https://i.pinimg.com/originals/4d/c2/16/4dc2167b48a44bf17ff926206ea913ff.jpg");
 background-color: #cccccc;
 background-repeat: no-repeat;
 background-size: cover;
}

p {
	color: white;
}



</style>

<body onload="inici()">

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$msg="";

if (isset($_SESSION['email'])) {

	if (isset($_POST['servei']) && isset($_POST['hores'])) {

		include "funcions/database.php";

		// TODO: Validar que la hora sigui major o igual a l'actual en el dia d'avui.

		// Mirem quants serveis ha escollit i comprova si es passa de les 2hores possibles. A més limitem que com a màxim pugui reservar a una distància de 2 setmanes.

		$qq = $conn->prepare("SELECT n_slots FROM servei WHERE id = :id");

		$n_slots=0;

		foreach($_POST['servei'] as $value) {
			$qq->bindParam(':id',$value);
			$qq->execute();
			$result = $qq->fetch(PDO::FETCH_ASSOC);
			$n_slots+=$result['n_slots'];
		}

		if ($n_slots > 8) $msg = "No pot reservar més de dos hores seguides.";
		else{

			$t_slot = 15 * 60;

			// Comprova si es poden fer seguits els serveis
			$hora_inici = $_POST['hores'];
			$hora_fi = date("H:i",strtotime($hora_inici) + ($t_slot * ($n_slots-1)));

			$qq = $conn->prepare("SELECT count(hora_inici) as total FROM slot WHERE (hora_inici BETWEEN :hi AND :hf) AND int_data = :d AND reservat=0 AND express=0");
			$qq->bindParam(':hi',$hora_inici);
			$qq->bindParam(':hf',$hora_fi);
			$qq->bindParam(':d',$_POST['data']);
			$qq->execute();

			//echo "<br/>".$qq->debugDumpParams();

			$result = $qq->fetch(PDO::FETCH_ASSOC);

			// Es pot fer la reserva
			if ($result['total'] >= $n_slots) {

				try {
					// First of all, let's begin a transaction
					$conn->beginTransaction();
				
					// A set of queries; if one fails, an exception should be thrown

					$q_slot = $conn->prepare("UPDATE slot SET reservat=1 WHERE (hora_inici BETWEEN :hi AND :hf) AND int_data = :d AND reservat=0 AND express=0");
					$q_slot->bindParam(':hi',$hora_inici);
					$q_slot->bindParam(':hf',$hora_fi);
					$q_slot->bindParam(':d',$_POST['data']);
					$q_slot->execute();
				
					// If we arrive here, it means that no exception was thrown
					// i.e. no query has failed, and we can commit the transaction
					$conn->commit();

					// Afegim la cita
					$qq = $conn->prepare("INSERT INTO cita VALUES (:h, :d, :e, 0)");
					$qq->bindParam(':h',$hora_inici);
					$qq->bindParam(':d',$_POST['data']);
					$qq->bindParam(':e',$_SESSION['email']);
					if ($qq->execute()) {

						// Afegir serveis que vol l'usuari a la cita
						try {

							// First of all, let's begin a transaction
							$conn->beginTransaction();

							// A set of queries; if one fails, an exception should be thrown

							$q_demanats = $conn->prepare("INSERT INTO demanats VALUES (:h, :d, :s)");

							foreach($_POST['servei'] as $value) {
								$q_demanats->bindParam(':h',$hora_inici);
								$q_demanats->bindParam(':d',$_POST['data']);
								$q_demanats->bindParam(':s',$value);
								$q_demanats->execute();
							}

							//echo $q_demanats->debugDumpParams() . "<br/>";
						
							// If we arrive here, it means that no exception was thrown
							// i.e. no query has failed, and we can commit the transaction
							$conn->commit();

						}catch(Exception $e) {
							// An exception has been thrown
							// We must rollback the transaction
							$conn->rollback();
							$msg = "Error afegint els serveis sol·licitats a la cita ".$e->getMessage();
						}

						$msg = "Vostè té una cita per el día " . $_POST['data'] . " a les " . $hora_inici . ".<br/>Recordi arribar 5 minuts abans i validar la cita si no la vol perdre.";

					}else{
						$msg = "Error al registrar la cita.";
					}

				} catch (Exception $e) {
					// An exception has been thrown
					// We must rollback the transaction
					$conn->rollback();
					$msg = "Error al reservar els slots. ".$e->getMessage();
				}

			}else{
				$msg = "No hi ha slots consecutius per poder prestar els serveis sol·licitats.<br/>Consideri en fer dos cites o fer una reserva en un altre rang d'hores.";
			}

		}
		
	}

}else{
	$msg = "Has d'iniciar sessió per poder realitzar una reserva.";			
}

include "funcions/obtenir_serveis.php";

?>

<div align="right">
  <h3 style="color:white"><a href="usuari.php">Perfil</a> <a href="logout.php">Sortir</a></h3>
</div>

<div style="float: right; right: 50px; position: relative; width:45%; z-index: 100">

<form action="index.php" method="post">


		<h3>Serveis</h3>
		<table style="width:100%" class="calendar" cellpadding="0" cellspacing="0">
		<tr class="calendar-row">
			<td></td>
			<td class="calendar-day-head">Gènere</th>
			<td class="calendar-day-head">Punts regal</th> 
			<td class="calendar-day-head">Preu</th>
			<td class="calendar-day-head">Slots</th>
		</tr>
		<?= draw_services() ?>
		</table>
	
	<div style="clear:left; top: 25px; position: relative;">
	<div>
	  <p><b>Hora:</b></p>
	  <select id="hores" name="hores">
		  <?= $GLOBALS['available_slots'] ?>
	  </select>
	</div>
	
	<div>
	  <p><b>Data:</b></p>
	  <input type="date" name="data" id="camp_data" min="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" onchange="changeCalendar()"> <br/><br/>
	</div>

  <input type="submit" value="Reserva">
  <input type="reset" value="Neteja">
	</div>
</form>
</div>

<div id="calendari">
</div>

<div style="clear:left; top: 25px; position: relative;">
<h3><b style="color:white">Disponibilitat</b></h3>

<table class="availability" id="taula_disponibilitat">
  <tbody>
    <tr>

  </tbody>
</table>
</div>

<div style="clear:left; top: 50px; position: relative; width: 1000px;">
 <b><?= $msg ?></b>
</div>

<br/>
</body>

<script language="javascript" type="text/javascript">

function inici()
{
	var now = new Date();
	var day = ("0" + now.getDate()).slice(-2);
	var month = ("0" + (now.getMonth() + 1)).slice(-2);
	var today = now.getFullYear() + "-" + (month) + "-" + (day);

	show_calendar(today);
	show_day(today);
	show_hours(today);
	
}

function show_day(date) {
  var xhttp;

	//var elem = document.getElementById("taula_disponibilitat");
	//elem.parentNode.removeChild(elem);

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    	document.getElementById("taula_disponibilitat").innerHTML = this.responseText;
			show_hours(date);
			document.getElementById("camp_data").value = date;
    }
  };
  xhttp.open("GET", "funcions/obtenir_disponibilitat.php?data="+date, true);
  xhttp.send();
}


function show_calendar(date) {
  var xhttp;

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    	document.getElementById("calendari").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "funcions/obtenir_calendari.php?data="+date, true);
  xhttp.send();
}

function show_hours(date) {
  var xhttp;

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    	document.getElementById("hores").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "funcions/obtenir_hores.php?data="+date, true);
  xhttp.send();
}

function changeCalendar() {

	var date = new Date(document.getElementById('camp_data').value);
	var month_date = ("0" + (date.getMonth() + 1)).slice(-2);

	show_day(date);

	var now = new Date();
	var month_now = ("0" + (now.getMonth() + 1)).slice(-2);

	if (month_date-month_now > 0) {
		
		var day = ("0" + date.getDate()).slice(-2);
		var month = ("0" + (date.getMonth() + 1)).slice(-2);
		var date = date.getFullYear() + "-" + (month) + "-" + (day);

		show_calendar(date);

	}
}

function book_a_date() {
	var xhttp;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			alert(this.responseText);
		}
	};
	xhttp.open("POST", "funcions/obtenir_hores.php?data="+date, true);
	xhttp.send();
}

</script>

</html>