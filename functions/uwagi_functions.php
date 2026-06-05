<?php
    function GeneralKlasaInfo() {
        global $conn;
        $fetchKlasa = $_POST['wybrana_klasa'] ?? '';
        $klasa = "klasy.id_klasy = $fetchKlasa";

        $sql = "SELECT
                    nauczyciele.imie,
                    nauczyciele.nazwisko,
                    klasy.nazwa,
                    COUNT(DISTINCT uczniowie.id_ucznia) AS ilosc_uczniow,
                    SUM(uwagi.typ = 'Pozytywna') AS ilosc_pozytywnych,
                    SUM(uwagi.typ = 'Negatywna') AS ilosc_negatywnych
                FROM klasy
                INNER JOIN nauczyciele ON klasy.id_wychowawcy = nauczyciele.id_nauczyciela
                INNER JOIN uczniowie ON klasy.id_klasy = uczniowie.id_klasy
                LEFT JOIN uwagi ON uczniowie.id_ucznia = uwagi.id_ucznia
                WHERE $klasa";

        echo "<table class='tableGlobal tableUwagi tableKlasa' border='1'>";
        echo "<tr><th>Wychowawca</th><th>Klasa</th><th>Ilość uczniów</th><th>Uwagi pozytywne</th><th>Uwagi negatywne</th></tr>";

        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo '<tr><td>' . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nazwa']) . '</td><td>' . $row['ilosc_uczniow'] . '</td>';
            echo '<td>' . ($row['ilosc_pozytywnych'] ?? 0) . '</td>';
            echo '<td>' . ($row['ilosc_negatywnych'] ?? 0) . '</td></tr>';
        }
        echo "</table>";
    }

    function Wyszukaj() {
        global $conn, $warunek;

        $sql = "SELECT
                    uczniowie.id_ucznia,
                    klasy.nazwa,
                    imie,
                    nazwisko,
                    SUM(uwagi.typ = 'Pozytywna') AS ilosc_pozytywnych,
                    SUM(uwagi.typ = 'Negatywna') AS ilosc_negatywnych
                FROM uczniowie
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                LEFT JOIN uwagi ON uczniowie.id_ucznia = uwagi.id_ucznia
                WHERE $warunek
                GROUP BY uczniowie.id_ucznia";

        echo "<table class='tableGlobal tableUwagi tableWyszukaj' border='1'>";
        echo "<tr>";
        if ($_POST['KlasaUczen'] == 'klasa') {
            echo "<th>Nr.</th>";
        }
        echo "<th>Uczeń</th><th>Klasa</th><th>Pozytywne</th><th>Negatywne</th><th>Typ</th><th>Opis</th></tr>";
        $result = $conn->query($sql . ';');

        $numerWDzienniku = 0;
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $numerWDzienniku++;
                $uczenID = $row['id_ucznia'];

                echo "<tr>";
                if ($_POST['KlasaUczen'] == 'klasa') {
                    echo "<td>" . $numerWDzienniku . "</td>";
                }
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nazwa']) . "</td>";
                echo "<td>" . ($row['ilosc_pozytywnych'] ?? 0) . "</td>";
                echo "<td>" . ($row['ilosc_negatywnych'] ?? 0) . "</td>";
                echo "<td><select class='selectGlobal selectUwagi' name='uwaga_typ[$uczenID]'>";
                uwagaTypSelect();
                echo "</select></td>";
                echo "<td><input class='inputGlobal inputUwagi' maxlength='1000' placeholder='opis' name='uwaga_opis[$uczenID]'></td>";
                echo "</tr>";
            }
        } else {
            $sql = "SELECT uczniowie.id_ucznia, uczniowie.imie, uczniowie.nazwisko, klasy.nazwa
                    FROM uczniowie
                    INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                    WHERE $warunek
                    GROUP BY uczniowie.id_ucznia
                    ORDER BY nazwa";

            $result = $conn->query($sql . ';');
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                if ($_POST['KlasaUczen'] == 'klasa') {
                    echo "<td>" . $numerWDzienniku . "</td>";
                }
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nazwa']) . "</td>";
                echo "<td>Brak uwag</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function UczenUwagi() {
        global $conn, $fetchKlasa, $fetchUczen;
        $id_nauczyciela = $_SESSION['id_nauczyciela'];
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        echo "<table class='tableGlobal tableUwagi tableUczen' border='1'>";
        echo "<tr><th>Data i czas</th><th>Typ</th><th>Opis</th><th>Nauczyciel</th><th></th></tr>";

        $sql = "SELECT uwagi.id_uwagi, uwagi.id_nauczyciela, uwagi.id_ucznia, uwagi.typ, uwagi.opis, uwagi.data, nauczyciele.imie, nauczyciele.nazwisko
                FROM uwagi
                INNER JOIN nauczyciele ON uwagi.id_nauczyciela = nauczyciele.id_nauczyciela
                WHERE uwagi.id_ucznia = $fetchUczen
                ORDER BY uwagi.data DESC";

        $result = $conn->query($sql . ';');
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['data'] . "</td>";
                $typClass = ($row['typ'] === 'Pozytywna') ? 'uwagaBadgePozytywna' : 'uwagaBadgeNegatywna';
                echo "<td><span class='uwagaBadge $typClass'>" . $row['typ'] . "</span></td>";
                echo "<td>" . htmlspecialchars($row['opis']) . "</td>";
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";

                if ($row['id_nauczyciela'] == $id_nauczyciela) {
                    echo "<td><button class='buttonGlobal buttonUwagi removeButton' value='" . $row['id_uwagi'] . "' name='usun'>Usuń</button></td>";
                } else {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<td colspan='5'>Brak uwag wpisanych</td>";
        }
        echo "</table>";
    }

    function WedlugPrzedmiotow_Klasa() {
        global $conn, $fetchKlasa;

        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT
                    nauczyciele.imie,
                    nauczyciele.nazwisko,
                    SUM(uwagi.typ = 'Pozytywna') AS ilosc_pozytywnych,
                    SUM(uwagi.typ = 'Negatywna') AS ilosc_negatywnych
                FROM nauczyciele
                LEFT JOIN uwagi ON nauczyciele.id_nauczyciela = uwagi.id_nauczyciela
                LEFT JOIN uczniowie ON uwagi.id_ucznia = uczniowie.id_ucznia
                LEFT JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                WHERE klasy.id_klasy = $fetchKlasa OR klasy.id_klasy IS NULL
                GROUP BY nauczyciele.id_nauczyciela
                ORDER BY nauczyciele.nazwisko";

        echo "<table class='tableGlobal tableUwagi tableWedlugPrzedmiotow' border='1'><tr><th>Nauczyciel</th><th>Uwagi pozytywne</th><th>Uwagi negatywne</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
            echo "<td>" . ($row['ilosc_pozytywnych'] ?? 0) . "</td>";
            echo "<td>" . ($row['ilosc_negatywnych'] ?? 0) . "</td></tr>";
        }
    }

    function WedlugPrzedmiotow_Uczen() {
        global $conn, $fetchUczen;

        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT
                    nauczyciele.imie,
                    nauczyciele.nazwisko,
                    SUM(uwagi.typ = 'Pozytywna') AS ilosc_pozytywnych,
                    SUM(uwagi.typ = 'Negatywna') AS ilosc_negatywnych
                FROM nauczyciele
                LEFT JOIN uwagi ON nauczyciele.id_nauczyciela = uwagi.id_nauczyciela AND uwagi.id_ucznia = $fetchUczen
                GROUP BY nauczyciele.id_nauczyciela
                ORDER BY nauczyciele.nazwisko";

        echo "<table class='tableGlobal tableUwagi tableWedlugPrzedmiotow' border='1'><tr><th>Nauczyciel</th><th>Uwagi pozytywne</th><th>Uwagi negatywne</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
            echo "<td>" . ($row['ilosc_pozytywnych'] ?? 0) . "</td>";
            echo "<td>" . ($row['ilosc_negatywnych'] ?? 0) . "</td></tr>";
        }
    }

    function DodajUwagi() {
        global $conn;
        $id_nauczyciela = $_SESSION['id_nauczyciela'];

        if (!isset($_POST['uwaga_typ']) || !is_array($_POST['uwaga_typ'])) {
            return;
        }

        foreach ($_POST['uwaga_typ'] as $id_ucznia => $typ) {
            $id_ucznia = intval($id_ucznia);
            $opis = $_POST['uwaga_opis'][$id_ucznia] ?? '';

            if ($typ != '-1') {
                $opisEscaped = $conn->real_escape_string($opis);
                $sql = "INSERT INTO uwagi (id_ucznia, id_nauczyciela, typ, opis, data)
                        VALUES ($id_ucznia, $id_nauczyciela, '$typ', '$opisEscaped', NOW())";
                $conn->query($sql);
            }
        }
    }

    function UsunUwagi() {
        global $conn;
        $id_uwagi = intval($_POST['usun']);
        $id_nauczyciela = $_SESSION['id_nauczyciela'];
        $sql = "DELETE FROM uwagi WHERE id_uwagi = $id_uwagi AND id_nauczyciela = $id_nauczyciela";
        $conn->query($sql . ';');
    }

    function uwagaTypSelect() {
        echo "<option class='optionGlobal optionUwagi' value='-1'>Typ</option>";
        echo "<option class='optionGlobal optionUwagi' value='Pozytywna'>Pozytywna</option>";
        echo "<option class='optionGlobal optionUwagi' value='Negatywna'>Negatywna</option>";
    }
?>
