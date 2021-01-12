<?php

// $sql  = 'SELECT date_format(`data_intval`,\'%m/%d/%Y\') as mydatefield FROM intval';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$missatge = "";

if (isset($_POST['hora_inici'])) {
	
	
	include "funcions/database.php";
	$inici = new DateTime($_POST['hora_fi']);
	$fi = new DateTime($_POST['hora_inici']);

	// Comprova que l'interval és correcte (hora inici és menor a hora fi).
	if ($inici > $fi)
	{

		$qq = $conn->prepare("SELECT MAX(hora_fi) AS fi FROM intval WHERE data_intval = :d");
		$qq->bindParam(':d',$_POST['data']);
		$qq->execute();
	
		$result = $qq->fetch(PDO::FETCH_ASSOC);

		// Comprova que l'interval i data inserits sigui darrer a l'últim existent.
		if (empty($result['fi']) == true || (empty($result['fi']) == false && new DateTime($_POST['hora_inici'])  > new DateTime($result['fi']))) {
			
			$sql = "INSERT INTO intval VALUES (:hi, :hf, :d, :s)";

			$qq = $conn->prepare($sql);
			$qq->bindParam(':hi',$_POST['hora_inici']);
			$qq->bindParam(':hf',$_POST['hora_fi']);
			$qq->bindParam(':d',$_POST['data']);
			$qq->bindParam(':s',$_POST['slot']);
		
			if ($qq->execute()) {

				// Genera els slots necessaris
				$diff_temps=$fi->diff($inici)->h;
				$slots = ($diff_temps*60) / $_POST['slot'];
				$t_slot = $_POST['slot'] * 60; // Conversió a segons
				$hora_inici = $_POST['hora_inici'];
				$incrementa_slot = date("H:i",strtotime($hora_inici) + $t_slot);

				try {
					// First of all, let's begin a transaction
					$conn->beginTransaction();
				
					// A set of queries; if one fails, an exception should be thrown

					$sql = "INSERT INTO slot VALUES (:hi, :ihi, :ihf, :id, 0, 0, 0)";
	
					$qq = $conn->prepare($sql);

					for ($i=0; $i<$slots; $i++) {

						$qq->bindParam(':hi',$hora_inici);
						$qq->bindParam(':ihi',$_POST['hora_inici']);
						$qq->bindParam(':ihf',$_POST['hora_fi']);
						$qq->bindParam(':id',$_POST['data']);
					
						$qq->execute();
	
						$hora_inici = $incrementa_slot;
						$incrementa_slot = date("H:i",strtotime($hora_inici) + $t_slot);
					}
				
					// If we arrive here, it means that no exception was thrown
					// i.e. no query has failed, and we can commit the transaction
					$conn->commit();
					$missatge = "Jornada registrada correctament.";
				} catch (Exception $e) {
					// An exception has been thrown
					// We must rollback the transaction
					$conn->rollback();
					$missatge = "Error al registrar els slots per al nou interval. ".$e->getMessage();

					$sql = "DELETE FROM intval WHERE hora_inici = :ihi AND hora_fi = :ihf AND data_intval = :id)";
	
					$qq = $conn->prepare($sql);
					
					$qq->bindParam(':ihi',$_POST['hora_inici']);
					$qq->bindParam(':ihf',$_POST['hora_fi']);
					$qq->bindParam(':id',$_POST['data']);
					
					$qq->execute();

					$missatge.="Eliminat interval";
				}

			} else {
				$missatge = "Ha ocorregut un error. Probablement ja està registrat l'interval.";
			}

		}else{
			$missatge = "No es pot afegir un interval dins d'un ja vigent.";
		}

	}else{
		$missatge = "L'hora fi de l'interval és menor a la de l'inici!!.";
	}


}

?>

<form action="afegeix_jornada.php" method="post">
  Hora inici: <input type="time" name="hora_inici" value="08:00">
  Hora fi: <input type="time" name="hora_fi" value="12:00"> <br/><br/>
  Data: <input type="date" name="data" min="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>"> <br/><br/>
  Minuts de l'slot: <input type="number" name="slot" min="1" max="60" value="15"> <br/><br/>
  <input type="submit" value="Envia">
  <input type="reset" value="Neteja">
</form>

<?= $missatge ?>