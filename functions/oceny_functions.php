<?php 
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

        echo "<table border='1'>";
        echo "<tr><th>Wychowawca</th><th>Klasa</th><th>Ilość uczniów</th><th>Srednia klasy</th></tr>";

        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo '<tr><td>' . $row['imie'] . ' ' . $row['nazwisko'] . '</td>';
            echo '<td>' . $row['nazwa'] . '</td><td>' . $row['ilosc_uczniow'] . '</td>';
            echo '<td>' . number_format($row['srednia'], 2, '.') . '</td></tr>';
        }
        echo "</table>";
    }

    function Wyszukaj() {
        global $conn, $warunek;

        $sql = "SELECT uczniowie.id_ucznia, uczniowie.imie, uczniowie.nazwisko, klasy.nazwa, ocenydictionary.ocena, SUM(ocenydictionary.wartosc*waga)/SUM(waga) AS Srednia_ucznia 
                FROM uczniowie 
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy 
                INNER JOIN oceny ON uczniowie.id_ucznia = oceny.id_ucznia 
                INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc 
                WHERE $warunek 
                GROUP BY uczniowie.id_ucznia 
                ORDER BY nazwa";

        echo "<table border='1'>";
        echo "<tr><th>Uczeń</th><th>Oceny</th><th>Srednia ucznia</th></tr>";

        $result = $conn->query($sql . ';');
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {                    
                $FullOceny = '';
                $uczenID = $row['id_ucznia'];

                $sql = "SELECT id_ucznia, ocenydictionary.ocena 
                        FROM oceny 
                        INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc 
                        WHERE id_ucznia = $uczenID";

                echo "<tr>";
                    echo "<td>" . $row["imie"];
                    echo ' ' . $row["nazwisko"] . "</td>";

                    $resOceny = $conn->query($sql . ';');
                    while ($rowOcena = $resOceny->fetch_assoc()) {
                        $FullOceny = $FullOceny . $rowOcena["ocena"] . ' ';
                    }

                    echo "<td>" . $FullOceny . "</td>";
                    echo "<td>" . number_format($row['Srednia_ucznia'], 2, '.') . "</td>";
                    echo "<td><input maxlength='1000' placeholder='komentarz' name='komentarz[$uczenID]'></td>";
                    echo "<td><select name='ocena[$uczenID]'>";
                    ocenaSelect();
                    echo "</select></td>";
                    echo "<td><select name='waga[$uczenID]'>"; 
                    wagaSelect();
                    echo "</select></td>";
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
                    echo "<td>" . $row["imie"] . "</td>";
                    echo "<td>" . $row["nazwisko"] . "</td>";
                    echo "<td>" . $row["nazwa"] . "</td>";
                    echo "<td>Brak ocen</td>";
                    echo "<td>Brak średniej</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function UczenOceny() {
        global $conn, $fetchKlasa, $fetchUczen;
        echo "<table border='1'>";
        echo "<tr><th>Nauczyciel</th><th>Przedmiot</th><th>Data i czas</th><th>Ocena</th><th>Waga</th><th>Komentarz</th></tr>";
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT oceny.id_nauczyciela, oceny.id_oceny, uczniowie.id_ucznia, nauczyciele.imie, nauczyciele.nazwisko, przedmioty.nazwa, data, ocenydictionary.ocena, waga, komentarz 
                FROM oceny 
                INNER JOIN nauczyciele ON oceny.id_nauczyciela = nauczyciele.id_nauczyciela 
                INNER JOIN przedmioty ON oceny.id_przedmiotu = przedmioty.id_przedmiotu 
                INNER JOIN uczniowie ON oceny.id_ucznia = uczniowie.id_ucznia 
                INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc
                WHERE uczniowie.id_ucznia = $fetchUczen 
                ORDER BY oceny.id_oceny DESC";

        $result = $conn->query($sql . ';');
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["imie"] . " " . $row["nazwisko"] . "</td>";
                echo "<td>" . $row["nazwa"] . "</td>";
                echo "<td>" . $row["data"] . "</td>";
                echo "<td>" . $row["ocena"] . "</td>";
                echo "<td>" . $row["waga"] . "</td>";
                echo "<td>" . $row["komentarz"] . "</td>";

                if ($row['id_nauczyciela'] == $_SESSION['id_nauczyciela']) {
                    echo "<td><button value = '" . $row['id_oceny'] . "' name='usun'>Usuń</button></td>"; 
                }
                echo "</tr>";
            }
        } else {
            echo "<td colspan='6'>Brak ocen wpisanych</td>";
        }
        echo "</table>";
        echo "<button name='dodajPrzycisk'>dodaj</button>";
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

        echo "<table border='1'><tr><th>Przedmiot</th><th>Srednia klasy</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['nazwa'] . "</td>" . "<td>" . number_format($row['Srednia_klasy'], 2, '.') . "</td></tr>";
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

        echo "<table border='1'><tr><th>Przedmiot</th><th>Oceny</th><th>Srednia ucznia</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['nazwa'] . "</td>";
            $przedmiot = $row['id_przedmiotu'];

            $FullOceny = '';

             
            $sql = "SELECT id_ucznia, przedmioty.id_przedmiotu, ocenydictionary.ocena 
                    FROM oceny 
                    INNER JOIN ocenydictionary ON oceny.ocena = ocenydictionary.wartosc 
                    INNER JOIN przedmioty ON oceny.id_przedmiotu = przedmioty.id_przedmiotu
                    WHERE id_ucznia = $fetchUczen AND przedmioty.id_przedmiotu = $przedmiot";

            $resOceny = $conn->query($sql . ';');
            while ($rowOcena = $resOceny->fetch_assoc()) {
                $FullOceny = $FullOceny . $rowOcena["ocena"] . ' ';
            }

            if ($FullOceny != '') {
                echo "<td>" . $FullOceny . "</td>" ."<td>" . number_format($row['Srednia_ocena'], 2, '.') . "</td></tr>";
            } else {
                echo "<td>Brak</td><td>0</td></tr>";
            }
        }
    }

    function DodajOceny() {
        global $conn;
        $id_przedmiotu = 0;
        $id_nauczyciela = $_SESSION['id_nauczyciela'];

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
                $sql = "INSERT INTO oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, komentarz, waga) 
                        VALUES ($id_ucznia, $id_przedmiotu, $id_nauczyciela, NOW(), $ocena, '$komentarz', $waga)";
                $conn->query($sql);
            }
        }
    }

    function UsunOceny() {
        global $conn;
        $sql = "DELETE FROM oceny WHERE id_oceny = $id_oceny";
        $id_oceny = intval($_POST['usun']);
        $conn->query($sql . ';');
    }

    function ocenaSelect() {
        global $conn;

        $sql = "SELECT wartosc, ocena FROM ocenydictionary";

        $result = $conn->query($sql . ';');
        echo "<option value='-1'>Ocena</option>";
        while($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['wartosc'] . "'>" . $row['ocena'] . "</option>";
        }
    }

    function wagaSelect() {
        global $conn;
        $i = 1;
        echo "<option value='-1'>Waga</option>";
        while($i < 6) {
            echo "<option value='$i'>$i</option>";
            $i++;
        }
    }
?>