<?php

$data = date("Y-m-d", time());
$fecha = new DateTime(date("Y-m-d"));
echo "$data<br/>";
for ($i=0; $i<12; $i++) {
	
	//$final = date("Y-m-d", strtotime("+1 month", $data));
	//$data = $final;


	$fecha->add(new DateInterval('P1M'));
	echo $fecha->format('Y-m-d') . " DIA DE LA SEMANA: " . $fecha->format('N') . "<br/>";

	if ($fecha->format('m') == 8) echo "FIESTAAAAAAAAAAAA";
}

?>
