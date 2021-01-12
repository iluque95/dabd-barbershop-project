<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    function get_client_services($date_hour)
    {
        include "database.php";

        $q_service = $conn->prepare("SELECT s.descripcio FROM cita c, servei s, demanats d WHERE (c.c_hora = :dh AND c.c_data = CURRENT_DATE) AND (d.cita_hora = c.c_hora AND d.cita_data = c.c_data) AND (d.servei = s.id)");
        $q_service->bindParam(':dh',$date_hour);
        $q_service->execute();
        $services="";
        $i=1;

        while ($service_row = $q_service->fetch(PDO::FETCH_ASSOC)) {

            $services .= $service_row['descripcio'];
            if ($i < $q_service->rowCount()) $services .= "</br>";
            $i++;

        }

        return $services;
    }

    function draw_dates() {

        include "database.php";

        $q_date = $conn->prepare("SELECT cl.nom, cl.cognoms, cl.email, TIME_FORMAT(ci.c_hora,\"%H:%i\") as hora FROM client cl, cita ci WHERE cl.email = ci.email AND ci.c_data = CURRENT_DATE AND ci.c_hora >= CURRENT_TIME AND NOT ci.presentat");
        $q_date->execute();
        $dates="";

        while ($date_row = $q_date->fetch(PDO::FETCH_ASSOC)) {

            $dates .= "<tr class='calendar-row'>";

            $dates.= "<td class=\"calendar-day-past\" align=\"center\">".$date_row['hora']."</td>";
            $dates.= "<td class=\"calendar-day-past\" align=\"center\">".$date_row['nom']." ".$date_row['cognoms']." (".$date_row['email'].")</td>";
            $dates.= "<td class=\"calendar-day-past\" align=\"center\">".get_client_services($date_row['hora'])."</td>";
            $dates.= "<td class=\"calendar-day-past\" style=\"width:200px\" align='center'><input type='button' value='VALIDA CITA' onclick=\"validate_date(this.parentNode.parentNode.rowIndex)\"></td>";
            
            $dates .= "</tr>";

        }

        return $dates;
    }

?>

<table style="width:100%" class="calendar" cellpadding="0" cellspacing="0">
    <tr class="calendar-row">
        <td class="calendar-day-head">HORA</td>
        <td class="calendar-day-head">CLIENT</td>
        <td class="calendar-day-head">SERVEIS</td>
        <td class="calendar-day-head"></td>
    </tr>
    <?= draw_dates() ?>
</table>