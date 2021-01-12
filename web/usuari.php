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

<body onload="show_dates()">

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
?>

<div align="left" style="margin-right: 500px">
  <h2 style="color:white"><?= $_SESSION['nom']." ".$_SESSION['cognoms'] ?></h2><h3 id="time" style="color:white"><?= $_SESSION['email'] ?>. Vostè té <?= $_SESSION['pts_acumulats'] ?> punts acumulats</h3>
</div>

<div align="right">
  <h3 style="color:white"><a href="logout.php">Sortir</a></h3>
</div>

<div align="center">
  <h1 style="color:white"><b>PROPERES CITES</b></h1>
</div>

<div style="margin-left:100px; margin-right:100px; width:80%; z-index: 100" id="cites">
<?= include "funcions/obtenir_cites_usuari.php" ?>
</div>

</body>

<script language="javascript" type="text/javascript">

</script>

</html>