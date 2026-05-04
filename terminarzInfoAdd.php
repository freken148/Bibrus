<?php
    // Plan to make this as iframe in future 
    require "core/idk.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminarz</title>
</head>
<body>
    <form id="forma10" method="POST" action="terminarz.php">
        <?php  
            if (isset($_POST['terminarzINFO'])) {
                $Wid = $_POST['terminarzINFO'];
                $sql = "SELECT 
                            zakres_start, 
                            zakres_end, 
                            imie,
                            nazwisko, 
                            typ_wydarzenia, 
                            nazwa, 
                            opis, 
                            data_dodania, 
                            DATEDIFF(zakres_end, zakres_start) AS check1, 
                            DATE_FORMAT(zakres_start, '%Y-%m-%d') AS data1,
                            DATE_FORMAT(zakres_start, '%H:%i') AS time_start,
                            DATE_FORMAT(zakres_end, '%H:%i') AS time_end
                        FROM terminarz
                        INNER JOIN nauczyciele ON terminarz.id_nauczyciela = nauczyciele.id_nauczyciela
                        INNER JOIN przedmioty ON terminarz.id_przedmiotu = przedmioty.id_przedmiotu
                        WHERE id_wydarzenia = $Wid";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();

                echo "<table border='1'>";
                echo "<tr><th colspan='2'>Szczegóły</th></tr>";
                echo "<tr>";

                if ($row['check1'] == 0) {
                    echo "<td>Data: </td><td>" . $row['data1'] . " " . $row['time_start'] . " - " . $row['time_end'];  
                } else {
                    echo "<td>Zakres: </td><td>" . $row['zakres_start'] . ' - ' . $row['zakres_end'] . "</td>";
                }

                echo "<tr>";
                echo "<tr><td>Nauczyciel: </td><td>" . $row['imie'] . " " . $row['nazwisko'] . "</td></tr>";
                echo "<tr><td>Przedmiot: </td><td>" . $row['nazwa'] . "</td></tr>";
                echo "<tr><td>Rodzaj: </td><td>" . $row['typ_wydarzenia'] . "</td></tr>";
                echo "<tr><td>Opis: </td><td>" . $row['opis'] . "</td></tr>";
                echo "<tr><td>Dodano: </td><td>" . $row['data_dodania'] . "</td></tr>";
                echo "</table>";
            }
        ?>
    </form>
    
</body>
</html>