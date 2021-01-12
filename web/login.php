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

div.centrao{
  position: absolute;
  margin: auto;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 300px;
  height: 300px;
	background-color: white;
	opacity: 0.8;
	border: 1px solid black;
}​

div.content {

  text-align:center;
}

</style>

<body onload="show_dates()">

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>



<div class="centrao">
	<div class="content" align="center">
	<form action="login.php" method="post" class="partners">
		Correu electrònic: <input type="text" name="correu"><br/>
		Contrasenya: <input type="password" name="clau"> <br/><br/>

		<input type="submit" value="Accedeix"> <input type="reset" value="Neteja">
	</form>

	<?php
		if (isset($_POST['correu']) && isset($_POST['clau'])) {

			include "funcions/database.php";

			$query = $conn->prepare("SELECT clau, nom, cognoms, pts_acumulats FROM client WHERE email = :e");
			$query->bindParam(':e',$_POST['correu']);
			$query->execute();

			$res = $query->fetch(PDO::FETCH_ASSOC);

			if ($query->rowCount()>0) {
				if ($_POST['clau'] == $res['clau']) {
					session_start();
					$_SESSION["email"] = $_POST['correu'];
					$_SESSION["nom"] = $res['nom'];
					$_SESSION["cognoms"] = $res['cognoms'];
					$_SESSION["pts_acumulats"] = $res['pts_acumulats'];
					header("Location: usuari.php");
				}else{
					echo "<br/><br/>Correu o contrasenya equivocats.<br>";
				}
			}else{
				echo "<br/><br/>No s'ha trobat cap registre<br>";
			}

		}
	?>

	</div>
</div>

</body>

<script language="javascript" type="text/javascript">

</script>

</html>