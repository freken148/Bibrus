<?php
    function ShowPlan() {
        global $conn, $fetchKlasa;
    
        echo "<table border='1'>";
        echo "<tr> <th>Nr. lekcji</th> <th>Godziny</th> <th>Poniedziałek</th> <th>Wtorek</th> <th>Środa</th> <th>Czwartek</th> <th>Piątek</th> <th>Sobota</th> <th>Niedziela</th> </tr>";
        for ($i = 1; $i < 15; $i++) {
            $sql = "SELECT numer_lekcji, godzina_lekcji AS godzina_start, DATE_ADD(godzina_lekcji, INTERVAL 45 MINUTE) AS godzina_end
                    FROM lekcjedictionary
                    WHERE numer_lekcji = $i";
            $result = $conn->query($sql . ';');
            $row = $result->fetch_assoc();

            $timeStart = substr($row['godzina_start'], 0, -3);
            $timeEnd = substr($row['godzina_end'], 0, -3);
            echo "<tr>";
            echo "<td>" . $i-1 . "</td>";
            echo "<td>" . $timeStart . " - " . $timeEnd . "</td>";
            for ($j = 0; $j < 7; $j++) {
                $sql = "SELECT nazwa, imie, nazwisko, numer_sali 
                        FROM planlekcji
                        INNER JOIN przedmioty ON planlekcji.id_przedmiotu = przedmioty.id_przedmiotu
                        INNER JOIN nauczyciele ON planlekcji.id_nauczyciela = nauczyciele.id_nauczyciela
                        WHERE numer_lekcji = $i AND numer_dnia = $j+1 AND id_klasy = $fetchKlasa";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();

                if ($result->num_rows <= 0) {
                    echo "<td><br><br></td>";
                } else {
                    echo "<td>";
                    echo $row['nazwa'] . '<br>';
                    echo $row['imie'] . ' ' . $row['nazwisko'];
                    echo '<br>Sala: ' . $row['numer_sali'];
                    echo "</td>";
                }
            }
            echo "</tr>";

            // breaks
            if ($i < 14) {
                $sql = "SELECT godzina_lekcji AS godzina_start
                        FROM lekcjedictionary
                        WHERE numer_lekcji = $i+1";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();
                $timeStart = substr($row['godzina_start'], 0, -3);
                echo "<tr>";
                echo "<td></td>";
                echo "<td>" . $timeEnd . " - " . $timeStart ."</td>";
                for ($p = 0; $p < 7; $p++) {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
        }
    }
?>