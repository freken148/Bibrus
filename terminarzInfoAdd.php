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
            } else if (isset($_POST['terminarzADD'])) {
                $Tid = $_SESSION['id_nauczyciela'];
                $sql = "SELECT imie, nazwisko, nazwa 
                        FROM nauczyciele 
                        INNER JOIN przedmioty ON nauczyciele.id_przedmiotu = przedmioty.id_przedmiotu
                        WHERE id_nauczyciela = $Tid";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();

                // $rok = $_SESSION['rokDefault'];

                // $miesiac = $_SESSION['miesiacDefault'];
                // if ($miesiac < 10) {
                //     $miesiac = '0' . $miesiac;
                // }

                // $dzien = $_POST['terminarzADD'];
                // if ($dzien < 10) {
                //     $dzien = '0' . $dzien;
                // }

                echo "<form method='POST' action='terminarzAdd.php'>";
                echo "<table border='1'>";
                echo "<tr><th colspan='2'>Dodaj wpis</th></tr>";
                echo "<tr><td>Zakres: </td><td>";
                echo "<input type='datetime-local' name='zakresS' value=''>";
                echo " - <input type='datetime-local' name='zakresE'></tr>";
                echo "<tr><td>Nauczyciel: </td><td>" . $row['imie'] . " " . $row['nazwisko'] . "</td></tr>";
                echo "<tr><td>Przedmiot: </td><td>" . $row['nazwa'] . "</td></tr>";

                echo "<tr><td>Rodzaj: </td><td>";

                echo "<select name='typT'>";
                echo "<option value='Sprawdzian'>Sprawdzian</option>";
                echo "<option value='Kartkówka'>Kartkówka</option>";
                echo "<option value='Nieobecność'>Nieobecność</option>"; 
                echo "<option value='Zastępstwo'>Zastępstwo</option>";
                echo "<option value='Informacja'>Informacja</option>"; 
                echo "<option value='Inne'>Inne</option>"; 
                echo "<option value='Wywiadówka'>Wywiadówka</option>";
                echo "</select>";

                echo "</td></tr>";

                echo "<tr><td>Opis: </td><td><input name='opisT'></td></tr>";
                echo "</table>";
                echo "<input type='submit' name='TerAdd' value='dodaj'>";
                echo "</form>";
            } 
        ?>
    </form>
    
</body>
</html>