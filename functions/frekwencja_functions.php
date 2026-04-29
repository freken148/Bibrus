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
                    AVG(typ IN ('Obecny', 'Zwolniony', 'Spóźniony'))*100 AS srednia
                FROM klasy 
                INNER JOIN nauczyciele ON klasy.id_wychowawcy = nauczyciele.id_nauczyciela 
                INNER JOIN uczniowie ON klasy.id_klasy = uczniowie.id_klasy 
                INNER JOIN frekwencja ON uczniowie.id_ucznia = frekwencja.id_ucznia
                WHERE $klasa";

        echo "<table border='1'>";
        echo "<tr><th>Wychowawca</th><th>Klasa</th><th>Ilość uczniów</th><th>Frekwencja klasy</th></tr>";

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

        $sql = "SELECT 
                    uczniowie.id_ucznia, 
                    klasy.nazwa, 
                    imie, 
                    nazwisko, 
                    AVG(frekwencja.typ IN ('Obecny', 'Zwolniony', 'Spóźniony')) * 100 AS frekwencja 
                FROM uczniowie 
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy 
                LEFT JOIN frekwencja ON uczniowie.id_ucznia = frekwencja.id_ucznia 
                WHERE $warunek
                GROUP BY uczniowie.id_ucznia";

        echo "<table border='1'>";
        echo "<tr>";
        if ($_POST['KlasaUczen'] == 'klasa') { 
            echo "<th>Nr.</th>";
        }
        echo "<th>Uczen</th><th>Frekwencja</th><th>O</th><th>Sp</th><th>Zw</th><th>Us</th><th>N</th></tr>";
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
                    echo "<td>" . $row["imie"];
                    echo " " . $row["nazwisko"] . "</td>";
                    echo "<td>" .  number_format($row["frekwencja"], 2, '.') . "</td>";
                    frekSelect("frek[$uczenID]");
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
                    echo "<td>" . $row["imie"] . "</td>";
                    echo "<td>" . $row["nazwisko"] . "</td>";
                    echo "<td>" . $row["nazwa"] . "</td>";
                    echo "<td>Brak wpisów o frekwencji</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function UczenOceny() {
        global $conn, $fetchKlasa, $fetchUczen;
        echo "<table border='1'>";
        echo "<tr><th>Przedmiot</th><th>Data i czas</th><th>Typ</th></tr>";
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT frekwencja.id_obecnosci, frekwencja.id_ucznia, przedmioty.nazwa, data, frekwencja.typ
        FROM frekwencja
        INNER JOIN przedmioty ON frekwencja.id_przedmiotu = przedmioty.id_przedmiotu 
        WHERE frekwencja.id_ucznia = $fetchUczen
        ORDER BY data DESC";

        $result = $conn->query($sql . ';');
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["nazwa"] . "</td>";
                echo "<td>" . $row["data"] . "</td>";
                echo "<td>" . $row["typ"] . "</td>";
                
                echo "<td><button value = '" . $row['id_obecnosci'] . "' name='usun'>Usuń</button></td>"; 
                echo "</tr>";
            }
        } else {
            echo "<td colspan='3'>Brak obecnosci wpisanych</td>";
        }
        echo "</table>";
        echo "<button name='dodajPrzycisk'>dodaj</button>";
    }

    function WedlugPrzedmiotow_Klasa() {
        global $conn, $fetchKlasa;
        
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();

        $sql = "SELECT 
                    przedmioty.nazwa, 
                    AVG(frekwencja.typ IN ('Obecny', 'Zwolniony', 'Spóźniony')) * 100 AS Srednia_frekwencji
                FROM przedmioty
                INNER JOIN frekwencja ON przedmioty.id_przedmiotu = frekwencja.id_przedmiotu
                INNER JOIN uczniowie ON frekwencja.id_ucznia = uczniowie.id_ucznia
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy
                WHERE klasy.id_klasy = $fetchKlasa
                GROUP BY przedmioty.id_przedmiotu
                ORDER BY przedmioty.nazwa";

        echo "<table border='1'><tr><th>Przedmiot</th><th>Srednia</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['nazwa'] . "</td>" . "<td>" . number_format($row['Srednia_frekwencji'], 2, '.') . "</td></tr>";
        }
    }

    function WedlugPrzedmiotow_Uczen() {
        global $conn, $fetchUczen;
        
        $fetchUczen = $_POST['wybrany_uczen'] ?? 0;
        BugFixWhenClickUczenAndSelectKlasa();
        // тут хуйня бо звольнони рахується за + до обецнощі але в норм лібрусі (і наверн по логіці) це так ніби лекції не було, ані + ані -
        // тому, це полюбому можна виправити але поху бо 1. може зайняти дохуя часу 2. не сказати щоб важливо на мою думку
        $sql = "SELECT przedmioty.nazwa, AVG(frekwencja.typ IN ('Obecny', 'Zwolniony', 'Spóźniony')) * 100 AS Srednia_frekwencji
                FROM przedmioty
                LEFT JOIN frekwencja ON przedmioty.id_przedmiotu = frekwencja.id_przedmiotu AND frekwencja.id_ucznia = $fetchUczen
                LEFT JOIN uczniowie ON frekwencja.id_ucznia = uczniowie.id_ucznia 
                GROUP BY przedmioty.id_przedmiotu
                ORDER BY przedmioty.nazwa";

        echo "<table border='1'><tr><th>Przedmiot</th><th>Srednia</th></tr>";
        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['nazwa'] . "</td>" . "<td>" . number_format($row['Srednia_frekwencji'], 2, '.') . "</td></tr>";
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

        if (isset($_POST['frek'])) {
           foreach ($_POST['frek'] as $id_ucznia => $typ) {
                $sql = "INSERT INTO frekwencja (id_ucznia, id_przedmiotu, data, typ) 
                        VALUES ($id_ucznia, $id_przedmiotu, NOW(), '$typ')";
                $conn->query($sql);
            }
        }
    }

    function UsunOceny() {
        global $conn;
        $id_obecnosci = intval($_POST['usun']);
        $sql = "DELETE FROM frekwencja WHERE id_obecnosci = $id_obecnosci";
        $conn->query($sql . ';');
    }

    function frekSelect($z) {
        global $conn;

        $sql = "SELECT DISTINCT typ FROM frekwencja";

        $result = $conn->query($sql . ';');
        while($row = $result->fetch_assoc()) {
            echo "<td><input type='radio' name='$z' value='" . $row['typ'] . "'></td>";
        }
    }
?>