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

?>

<div>
  <h1 id="time" style="color:white"></h1>
</div>

<div style="margin-left:100px; margin-right:100px; width:80%; z-index: 100" id="cites">

</div>

</body>

<script language="javascript" type="text/javascript">

function checkTime(i) {
  if (i < 10) {
    i = "0" + i;
  }
  return i;
}

function startTime() {
	
  var today = new Date();
  var h = today.getHours();
  var m = today.getMinutes();
  var s = today.getSeconds();
  // add a zero in front of numbers<10
  m = checkTime(m);
  s = checkTime(s);

  if (m%5==0 && s=="00") {

	var table = document.getElementsByTagName("table")[0];
	var time = table.rows[1].cells[0].innerHTML;

	var hour = time.substring(0, time.indexOf(":"));
	var minute = time.substring(time.indexOf(":")+1, time.length);
	

	if (hour==h && minute==m) if (table.rows[1].cells[3].childNodes[0].value == "VALIDA CITA") invalidate_date();
	

  }

  document.getElementById('time').innerHTML = "HORA ACTUAL: " + h + ":" + m + ":" + s;
  t = setTimeout(function() {
    startTime()
  }, 500);
}
startTime();

function validate_date(row) {

	var xhttp;
	var table = document.getElementsByTagName("table")[0];

	var now = new Date();
	var day = ("0" + now.getDate()).slice(-2);
	var month = ("0" + (now.getMonth() + 1)).slice(-2);
	var today = now.getFullYear() + "-" + (month) + "-" + (day);

	var hora = table.rows[row].cells[0].innerHTML;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			table.rows[row].cells[3].innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "funcions/valida_cita.php?data="+today+"&hora="+hora, true);
	xhttp.send();

}

function generate_ticket(row) {

	var table = document.getElementsByTagName("table")[0];

	var now = new Date();
	var day = ("0" + now.getDate()).slice(-2);
	var month = ("0" + (now.getMonth() + 1)).slice(-2);
	var today = now.getFullYear() + "-" + (month) + "-" + (day);

	var hora = table.rows[row].cells[0].innerHTML;

	var win = window.open("genera_tiquet.php?hora="+hora+"&data="+today, '_blank');
  	win.focus();
	table.deleteRow(row);
}

function show_dates() {
  var xhttp;

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    	document.getElementById("cites").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "funcions/obtenir_cites_usuaris.php", true);
  xhttp.send();
}

function invalidate_date()
{
	var xhttp;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
		//alert(this.responseText);
	}
	};
	xhttp.open("GET", "funcions/invalida_cita.php", true);
	xhttp.send();
}


</script>

</html>