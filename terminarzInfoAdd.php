<?php
    // Plan to make this as iframe in future
    require "core/idk.php";
    require "functions/terminarz_functions.php";

    // handle inline add (TerAdd is pressed inside the add form)
    if (isset($_POST['TerAdd'])) {
        terminarzDodaj();
        header('Location: terminarz.php');
        exit;
    }

    // Store class in session when coming from calendar
    if (isset($_POST['wybrana_klasa'])) {
        $_SESSION['klasaDefault'] = intval($_POST['wybrana_klasa']);
    }

    // Pre-fill date from calendar day selection
    $prefillDate = '';
    if (isset($_POST['terminarzAdd'])) {
        $year = $_POST['wybrany_rok'] ?? date('Y');
        $month = str_pad($_POST['wybrany_miesiac'] ?? date('m'), 2, '0', STR_PAD_LEFT);
        $dayOffset = intval($_POST['terminarzAdd']);
        $prefillDateObj = new DateTime("$year-$month-01");
        $prefillDateObj->modify("+$dayOffset days");
        $prefillDate = $prefillDateObj->format('Y-m-d\TH:i');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminarz</title>
    <?php require "core/head.php"; ?>
</head>
<body class="bodyGlobal bodyTerminarz">
    <div class="formGlobal formTerminarz">
        <a href="terminarz.php" class="buttonGlobal buttonTerminarz glownaButton backButton">&larr; Wróć</a>
        <?php
            if (isset($_POST['terminarzINFO'])) {
                $Wid = intval($_POST['terminarzINFO']);
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

                echo "<table class='tableGlobal tableTerminarz tableTerminarzDetails' border='1'>";
                echo "<tr><th colspan='2'>Szczegóły</th></tr>";
                echo "<tr>";
                if ($row['check1'] == 0) {
                    echo "<td>Data: </td><td>" . $row['data1'] . " " . $row['time_start'] . " - " . $row['time_end'];
                } else {
                    echo "<td>Zakres: </td><td>" . $row['zakres_start'] . ' - ' . $row['zakres_end'] . "</td>";
                }
                echo "</tr>";
                echo "<tr><td>Nauczyciel: </td><td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td></tr>";
                echo "<tr><td>Przedmiot: </td><td>" . htmlspecialchars($row['nazwa']) . "</td></tr>";
                echo "<tr><td>Rodzaj: </td><td>" . $row['typ_wydarzenia'] . "</td></tr>";
                echo "<tr><td>Opis: </td><td>" . htmlspecialchars($row['opis']) . "</td></tr>";
                echo "<tr><td>Dodano: </td><td>" . $row['data_dodania'] . "</td></tr>";
                echo "</table>";
            } else {
                // show the add form
                $Tid = $_SESSION['id_nauczyciela'];
                $sql = "SELECT imie, nazwisko, nazwa
                        FROM nauczyciele
                        INNER JOIN przedmioty ON nauczyciele.id_przedmiotu = przedmioty.id_przedmiotu
                        WHERE id_nauczyciela = $Tid";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();

                echo "<form class='formGlobal formTerminarz formTerminarzAdd' method='POST' action='terminarzInfoAdd.php'>";
                echo "<table class='tableGlobal tableTerminarz tableTerminarzDetails' border='1'>";
                echo "<tr><th colspan='2'>Dodaj wpis</th></tr>";
                echo "<tr><td>Zakres: </td><td>";
                echo "<input class='inputGlobal inputTerminarz inputTerminarzAdd' type='datetime-local' name='zakresS' value='$prefillDate'>";
                echo " - <input class='inputGlobal inputTerminarz inputTerminarzAdd' type='datetime-local' name='zakresE'></td></tr>";
                echo "<tr><td>Nauczyciel: </td><td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td></tr>";
                echo "<tr><td>Przedmiot: </td><td>" . htmlspecialchars($row['nazwa']) . "</td></tr>";

                echo "<tr><td>Rodzaj: </td><td>";
                echo "<select class='selectGlobal selectTerminarz selectTerminarzAdd' name='typT'>";
                echo "<option class='optionGlobal optionTerminarz' value='Sprawdzian'>Sprawdzian</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Kartkówka'>Kartkówka</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Nieobecność'>Nieobecność</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Zastępstwo'>Zastępstwo</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Informacja'>Informacja</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Inne'>Inne</option>";
                echo "<option class='optionGlobal optionTerminarz' value='Wywiadówka'>Wywiadówka</option>";
                echo "</select>";
                echo "</td></tr>";

                echo "<tr><td>Opis: </td><td><input class='inputGlobal inputTerminarz inputTerminarzAdd' name='opisT'></td></tr>";
                echo "</table>";
                echo "<button class='buttonGlobal buttonTerminarz dodajButton' type='submit' name='TerAdd' value='dodaj'>dodaj</button>";
                echo "</form>";
            }
        ?>
    </div>
</body>
</html>
