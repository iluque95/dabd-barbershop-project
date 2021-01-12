<?php	
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function add_slots($hora_in, $hora_fi, $data) {

	include "funcions/database.php";

	$inici = new DateTime($hora_in);
	$fi = new DateTime($hora_fi);
	// Genera els slots necessaris
	$diff_temps=$fi->diff($inici)->h;
	$slots = ($diff_temps*60) / 15;
	$t_slot = 15 * 60; // Conversió a segons
	$hora_inici = $hora_in;
	$incrementa_slot = date("H:i",strtotime($hora_inici) + $t_slot);


	$sql = "INSERT INTO slot VALUES (:hi, :ihi, :ihf, :id, 0, 0, 0)";

	$qq = $conn->prepare($sql);

	for ($i=0; $i<$slots; $i++) {

		$qq->bindParam(':hi',$hora_inici);
		$qq->bindParam(':ihi',$hora_in);
		$qq->bindParam(':ihf',$hora_fi);
		$qq->bindParam(':id',$data);
	
		$qq->execute();

		//echo $qq->debugDumpParams();

		$hora_inici = $incrementa_slot;
		$incrementa_slot = date("H:i",strtotime($hora_inici) + $t_slot);
	}
}

include "funcions/database.php";

$data = new DateTime(date("Y-m-d"));

$interval = $conn->prepare("INSERT INTO intval VALUES (:hi, :hf, :di, 15)");

// INSERTA ELS PRÓXIMS 2 ANYS

for ($i=0; $i<730; $i++) {

	$date=$data->format('Y-m-d');

	// AGOST I CAPS DE SETMANA SÓN FESTIUS!!
	if ($data->format('m') != 8 && $data->format('N') != 6 && $data->format('N') != 7) {

		$jornada = rand(1,3);
		$slots = 0;

		if ($jornada == 1) {		// Treballa de 7:30 a 15:30
			$hora_inici = "07:30";
			$hora_fi = "15:30";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);

		}elseif($jornada == 2) {	// Treballa de 9:00 a 12:00, de 13:00 a 14:00 i de 15:00 a 19:00
			$hora_inici = "09:00";
			$hora_fi = "12:00";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);

			$hora_inici = "13:00";
			$hora_fi = "14:00";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);

			$hora_inici = "15:00";
			$hora_fi = "19:00";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);

		}elseif($jornada == 3) {	// Treballa de 10:00 a 14:00, de 17:00 a 21:00 
			$hora_inici = "10:00";
			$hora_fi = "14:00";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);

			$hora_inici = "17:00";
			$hora_fi = "21:00";
			$interval->bindParam(':hi',$hora_inici);
			$interval->bindParam(':hf',$hora_fi);
			$interval->bindParam(':di',$date);
			$interval->execute();
			add_slots($hora_inici, $hora_fi, $date);
		}


		// ASSIGNA CITES ALEATORIES ALS USUARIS

		$serveis_rnd = rand(1,3);
		
		$q_client = $conn->prepare("SELECT email FROM client ORDER BY RAND() LIMIT 1");
		$q_client->execute();
		$mail = $q_client->fetch(PDO::FETCH_ASSOC)['email'];

		if ($serveis_rnd == 1){
			$qq = $conn->prepare("SELECT id, n_slots FROM servei ORDER BY RAND() LIMIT 1");
			$qq->execute();
		}elseif ($serveis_rnd == 2){
			$qq = $conn->prepare("SELECT id, n_slots FROM servei ORDER BY RAND() LIMIT 2");
			$qq->execute();
		}else{
			$qq = $conn->prepare("SELECT id, n_slots FROM servei ORDER BY RAND() LIMIT 3");
			$qq->execute();
		}
		
		echo "<br/>".$qq->debugDumpParams();

		$n_slots=0;
		$serveis_arr = array();

		while ($res = $qq->fetch(PDO::FETCH_ASSOC)) {

			$n_slots+=$res['n_slots'];
			array_push($serveis_arr, $res['id']);
		}

		if ($n_slots > 8) $msg = "No pot reservar més de dos hores seguides.";
		else{

			$t_slot = 15 * 60;

			// Comprova si es poden fer seguits els serveis
			$hora_inici = $hora_inici;
			$hora_fi = date("H:i",strtotime($hora_inici) + ($t_slot * ($n_slots-1)));

			$qq = $conn->prepare("SELECT count(hora_inici) as total FROM slot WHERE (hora_inici BETWEEN :hi AND :hf) AND int_data = :d AND reservat=0 AND express=0");
			$qq->bindParam(':hi',$hora_fi);
			$qq->bindParam(':hf',$hora_inici);
			$qq->bindParam(':d',$date);
			$qq->execute();

			//echo "<br/>".$qq->debugDumpParams();

			$result = $qq->fetch(PDO::FETCH_ASSOC);

			// Es pot fer la reserva
			if ($result['total'] >= $n_slots) {

				$q_slot = $conn->prepare("UPDATE slot SET reservat=1 WHERE (hora_inici BETWEEN :hi AND :hf) AND int_data = :d AND reservat=0 AND express=0");
				$q_slot->bindParam(':hi',$hora_fi);
				$q_slot->bindParam(':hf',$hora_inici);
				$q_slot->bindParam(':d',$date);
				$q_slot->execute();

				echo "<br/>".$q_slot->debugDumpParams();

				// Afegim la cita
				$qq = $conn->prepare("INSERT INTO cita VALUES (:h, :d, :e, 0)");
				$qq->bindParam(':h',$hora_fi);
				$qq->bindParam(':d',$date);
				$qq->bindParam(':e',$mail);
				if ($qq->execute()) {

					// Afegir serveis que vol l'usuari a la cita

					$q_demanats = $conn->prepare("INSERT INTO demanats VALUES (:h, :d, :s)");

					foreach($serveis_arr as $value) {
						$q_demanats->bindParam(':h',$hora_fi);
						$q_demanats->bindParam(':d',$date);
						$q_demanats->bindParam(':s',$value);
						$q_demanats->execute();
					}

					echo $q_demanats->debugDumpParams() . "<br/>";


					$msg = "Vostè té una cita per el día " . $date . " a les " . $hora_fi . ".<br/>Recordi arribar 5 minuts abans i validar la cita si no la vol perdre.";

				}else{
					$msg = "Error al registrar la cita.";
				}
			

			}else{
				$msg = "No hi ha slots consecutius per poder prestar els serveis sol·licitats.<br/>Consideri en fer dos cites o fer una reserva en un altre rang d'hores.";
			}

			echo "$msg<br>";

		}
		
	}

	$data->add(new DateInterval('P1D'));
}



echo "OK!! <br/>";

?>
