<?php
    function gradeClassFromString($s) {
        $first = substr(trim($s), 0, 1);
        if (in_array($first, ['1','2','3','4','5','6'])) {
            return 'grade' . $first;
        }
        return '';
    }

    function GeneralKlasaInfo() {
        global $conn;
        $fetchKlasa = $_POST['wybrana_klasa'] ?? '';
        $klasa = "klasy.id_klasy = $fetchKlasa";

        $sql = "SELECT nauczyciele.imie, nauczyciele.nazwisko, klasy.nazwa, COUNT(uczniowie.id_ucznia) AS ilosc_uczniow, (
                SELECT SUM(oceny.ocena*oceny.waga)/SUM(oceny.waga)
                    FROM oceny
                    INNER JOIN uczniowie ON oceny.id_ucznia = uczniowie.id_ucznia
                    INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                    WHERE $klasa
                ) AS srednia
                FROM klasy
                INNER JOIN nauczyciele ON klasy.id_wychowawcy = nauczyciele.id_nauczyciela
                INNER JOIN uczniowie ON klasy.id_klasy = uczniowie.id_klasy
                WHERE $klasa";

        echo "<table class='tableGlobal tableOceny tableKlasa' border='1'>";
        echo "<tr><th>Wychowawca</th><th>Klasa</th><th>Ilość uczniów</th><th>Średnia klasy</th></tr>";

        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo '<tr><td>' . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nazwa']) . '</td><td>' . $row['ilosc_uczniow'] . '</td>';
            echo '<td>' . number_format($row['srednia'], 2, '.') . '</td></tr>';
        }
        echo "</table>";
    }

    function Wyszukaj() {
        global $conn, $warunek;

        $showGradeInputs = !isset($_POST['WedlugPrzedmiotow']);

        $sql = "SELECT uczniowie.id_ucznia, uczniowie.imie, uczniowie.nazwisko, klasy.nazwa, ocenydictionary.ocena, SUM(ocenydictionary.wartosc*waga)/SUM(waga) AS Srednia_ucznia
                FROM uczniowie
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                INNER JOIN oceny ON uczniowie.id_ucznia = oceny.id_ucznia
                INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc
                WHERE $warunek
                GROUP BY uczniowie.id_ucznia
                ORDER BY nazwa";

        echo "<table class='tableGlobal tableOceny tableWyszukaj' border='1'>";
        echo "<tr>";
        if ($_POST['KlasaUczen'] == 'klasa') {
            echo "<th>Nr.</th>";
        }
        echo "<th>Uczeń</th><th>Oceny</th><th>Średnia</th>";
        if ($showGradeInputs) {
            echo "<th>Ocena</th><th>Waga</th><th>Komentarz</th>";
        }
        echo "</tr>";

        $result = $conn->query($sql . ';');

        $numerWDzienniku = 0;
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $numerWDzienniku++;
                $uczenID = $row['id_ucznia'];

                $sql = "SELECT id_ucznia, ocenydictionary.ocena
                        FROM oceny
                        INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc
                        WHERE id_ucznia = $uczenID
                        ORDER BY oceny.data ASC";

                $resOceny = $conn->query($sql . ';');

                echo "<tr>";
                if ($_POST['KlasaUczen'] == 'klasa') {
                    echo "<td>" . $numerWDzienniku . "</td>";
                }
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";

                echo "<td class='ocenyBadgesCell'>";
                while ($rowOcena = $resOceny->fetch_assoc()) {
                    $cls = gradeClassFromString($rowOcena['ocena']);
                    echo "<span class='gradeBadge $cls'>" . htmlspecialchars($rowOcena['ocena']) . "</span> ";
                }
                echo "</td>";

                echo "<td>" . number_format($row['Srednia_ucznia'], 2, '.') . "</td>";

                if ($showGradeInputs) {
                    echo "<td><select class='selectGlobal selectOceny' name='ocena[$uczenID]'>";
                    ocenaSelect();
                    echo "</select></td>";
                    echo "<td><select class='selectGlobal selectOceny' name='waga[$uczenID]'>";
                    wagaSelect();
                    echo "</select></td>";
                    echo "<td><input class='inputGlobal inputOceny' type='text' name='komentarz[$uczenID]' placeholder='Komentarz'></td>";
                }
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
                $uczenID = $row['id_ucznia'];
                echo "<tr>";
                if ($_POST['KlasaUczen'] == 'klasa') {
                    echo "<td>" . $numerWDzienniku . "</td>";
                }
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nazwa']) . "</td>";
                echo "<td>Brak ocen</td>";
                if ($showGradeInputs) {
                    echo "<td><select class='selectGlobal selectOceny' name='ocena[$uczenID]'>";
                    ocenaSelect();
                    echo "</select></td>";
                    echo "<td><select class='selectGlobal selectOceny' name='waga[$uczenID]'>";
                    wagaSelect();
                    echo "</select></td>";
                    echo "<td><input class='inputGlobal inputOceny' type='text' name='komentarz[$uczenID]' placeholder='Komentarz'></td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function UczenOceny() {
        global $conn, $fetchKlasa, $fetchUczen;
        $id_nauczyciela = $_SESSION['id_nauczyciela'];
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        echo "<table class='tableGlobal tableOceny tableUczen' border='1'>";
        echo "<tr><th>Nauczyciel</th><th>Przedmiot</th><th>Data i czas</th><th>Ocena</th><th>Waga</th><th>Komentarz</th><th></th></tr>";

        $sql = "SELECT oceny.id_nauczyciela, oceny.id_oceny, uczniowie.id_ucznia, nauczyciele.imie, nauczyciele.nazwisko, przedmioty.nazwa, data, ocenydictionary.ocena, waga, komentarz
                FROM oceny
                INNER JOIN nauczyciele ON oceny.id_nauczyciela = nauczyciele.id_nauczyciela
                INNER JOIN przedmioty ON oceny.id_przedmiotu = przedmioty.id_przedmiotu
                INNER JOIN uczniowie ON oceny.id_ucznia = uczniowie.id_ucznia
                INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc
                WHERE uczniowie.id_ucznia = $fetchUczen
                ORDER BY data DESC";

        $result = $conn->query($sql . ';');
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nazwa']) . "</td>";
                echo "<td>" . $row['data'] . "</td>";
                $cls = gradeClassFromString($row['ocena']);
                echo "<td><span class='gradeBadge $cls'>" . htmlspecialchars($row['ocena']) . "</span></td>";
                echo "<td>" . $row['waga'] . "</td>";
                echo "<td>" . htmlspecialchars($row['komentarz']) . "</td>";

                if ($row['id_nauczyciela'] == $id_nauczyciela) {
                    echo "<td><button class='buttonGlobal buttonOceny removeButton' value='" . $row['id_oceny'] . "' name='usun'>Usuń</button></td>";
                } else {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<td colspan='7'>Brak ocen wpisanych</td>";
        }
        echo "</table>";

    }

    function WedlugPrzedmiotow_Klasa() {
        global $conn, $fetchKlasa;

        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT przedmioty.nazwa, SUM(oceny.ocena*oceny.waga)/SUM(waga) AS Srednia_klasy
                FROM przedmioty
                LEFT JOIN oceny ON przedmioty.id_przedmiotu = oceny.id_przedmiotu
                INNER JOIN uczniowie ON oceny.id_ucznia = uczniowie.id_ucznia
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                WHERE klasy.id_klasy = '$fetchKlasa'
                GROUP BY przedmioty.id_przedmiotu
                ORDER BY przedmioty.nazwa";

        echo "<table class='tableGlobal tableOceny tableWedlugPrzedmiotow' border='1'><tr><th>Przedmiot</th><th>Średnia klasy</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['nazwa']) . "</td><td>" . number_format($row['Srednia_klasy'], 2, '.') . "</td></tr>";
        }
    }

    function WedlugPrzedmiotow_Uczen() {
        global $conn, $fetchUczen;

        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT id_ucznia, przedmioty.id_przedmiotu, przedmioty.nazwa, oceny.ocena, oceny.waga, (
                    SELECT
                        SUM(oceny.ocena*oceny.waga)/SUM(oceny.waga)
                    FROM oceny
                    WHERE id_ucznia = $fetchUczen AND oceny.id_przedmiotu = przedmioty.id_przedmiotu
                ) AS Srednia_ocena
                FROM przedmioty
                LEFT JOIN oceny
                ON przedmioty.id_przedmiotu = oceny.id_przedmiotu AND id_ucznia = $fetchUczen
                GROUP BY id_przedmiotu
                ORDER BY nazwa";

        echo "<table class='tableGlobal tableOceny tableWedlugPrzedmiotow' border='1'><tr><th>Przedmiot</th><th>Oceny</th><th>Średnia</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            $przedmiot = $row['id_przedmiotu'];

            echo "<tr><td>" . htmlspecialchars($row['nazwa']) . "</td>";

            $sql2 = "SELECT ocenydictionary.ocena
                    FROM oceny
                    INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc
                    INNER JOIN przedmioty ON oceny.id_przedmiotu = przedmioty.id_przedmiotu
                    WHERE id_ucznia = $fetchUczen AND przedmioty.id_przedmiotu = $przedmiot";

            $resOceny = $conn->query($sql2 . ';');
            $gradesHtml = '';
            while ($rowOcena = $resOceny->fetch_assoc()) {
                $cls = gradeClassFromString($rowOcena['ocena']);
                $gradesHtml .= "<span class='gradeBadge $cls'>" . htmlspecialchars($rowOcena['ocena']) . "</span> ";
            }

            if ($gradesHtml != '') {
                echo "<td class='ocenyBadgesCell'>" . $gradesHtml . "</td>";
                echo "<td>" . number_format($row['Srednia_ocena'], 2, '.') . "</td></tr>";
            } else {
                echo "<td>Brak</td><td>—</td></tr>";
            }
        }
    }

    function DodajOceny() {
        global $conn;
        $id_przedmiotu = 0;
        $id_nauczyciela = $_SESSION['id_nauczyciela'];

        if (!isset($_POST['ocena']) || !is_array($_POST['ocena'])) {
            return;
        }

        $sql = "SELECT przedmioty.id_przedmiotu
                FROM przedmioty
                INNER JOIN nauczyciele ON przedmioty.id_przedmiotu = nauczyciele.id_przedmiotu
                WHERE id_nauczyciela = $id_nauczyciela";

        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            $id_przedmiotu = $row['id_przedmiotu'];
        }

        foreach ($_POST['ocena'] as $id_ucznia => $ocena) {
            $waga = $_POST['waga'][$id_ucznia];
            $komentarz = $_POST['komentarz'][$id_ucznia];

            if ($ocena != '-1' && $waga != '-1') {
                $komentarzEscaped = $conn->real_escape_string($komentarz);
                $sql = "INSERT INTO oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, komentarz, waga)
                        VALUES ($id_ucznia, $id_przedmiotu, $id_nauczyciela, NOW(), $ocena, '$komentarzEscaped', $waga)";
                $conn->query($sql);
            }
        }
    }

    function UsunOceny() {
        global $conn;
        $id_oceny = intval($_POST['usun']);
        $id_nauczyciela = $_SESSION['id_nauczyciela'];
        $sql = "DELETE FROM oceny WHERE id_oceny = $id_oceny AND id_nauczyciela = $id_nauczyciela";
        $conn->query($sql . ';');
    }

    function ocenaSelect() {
        global $conn;

        $sql = "SELECT wartosc, ocena FROM ocenydictionary";

        $result = $conn->query($sql . ';');
        echo "<option class='optionGlobal optionOceny' value='-1'>Ocena</option>";
        while($row = $result->fetch_assoc()) {
            echo "<option class='optionGlobal optionOceny' value='" . $row['wartosc'] . "'>" . $row['ocena'] . "</option>";
        }
    }

    function wagaSelect() {
        global $conn;
        $i = 1;
        echo "<option class='optionGlobal optionOceny' value='-1'>Waga</option>";
        while($i < 6) {
            echo "<option class='optionGlobal optionOceny' value='$i'>$i</option>";
            $i++;
        }
    }
?>
