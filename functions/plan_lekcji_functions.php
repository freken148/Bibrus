<?php
    function ShowPlan() {
        global $conn, $fetchKlasa; 

        $days = array('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela');
        $timeStart = array('07:10','08:00','08:50','09:40','10:40','11:30','12:20','13:20','14:15','15:05','15:55','16:45','17:35','18:25');
        $timeEnd = array('07:55','08:45','09:35','10:25','11:25','12:15','13:05','14:10','15:00','15:50','15:55','16:40','18:20','19:10');
    
        echo "<table border='1'>";
        echo "<tr> <th>Nr. lekcji</th> <th>Godziny</th> <th>Poniedziałek</th> <th>Wtorek</th> <th>Środa</th> <th>Czwartek</th> <th>Piątek</th> <th>Sobota</th> <th>Niedziela</th> </tr>";
        for ($i = 0; $i < 14; $i++) {
            echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $timeStart[$i] . " - " . $timeEnd[$i] . "</td>";
            for ($j = 0; $j < 7; $j++) {
                $sql = "SELECT lekcjedictionary.numer_lekcji, godzina_lekcji, imie, nazwisko, nazwa, numer_sali, dzien
                        FROM lekcjedictionary
                        LEFT JOIN planlekcji 
                        ON lekcjedictionary.numer_lekcji = planlekcji.numer_lekcji AND id_klasy = $fetchKlasa
                        LEFT JOIN nauczyciele 
                        ON planlekcji.id_nauczyciela = nauczyciele.id_nauczyciela
                        LEFT JOIN przedmioty 
                        ON planlekcji.id_przedmiotu = przedmioty.id_przedmiotu
                        WHERE dzien = '$days[$j]' AND planlekcji.numer_lekcji = $i+1
                        ORDER BY lekcjedictionary.numer_lekcji, dzien";
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
                // At this point i think it would be better if i just made table "dayofweeks" or smth and just used it instead of enum but fuck this i don't wanna 100500 times recreating tables and other shit, then generate etc. etc.
                // But it seems as so simple solution
                // But no
                // i finally did it, but in my opinion a whole of this code a piece of shit and if i will have any free time and purpose i rewrite it's all fucking bullshit
            }
            echo "</tr>";

            // breaks
            if ($i < 13) {
                echo "<tr>";
                echo "<td></td>";
                echo "<td>" . $timeEnd[$i] . " - " . $timeStart[$i+1] ."</td>";
                for ($p = 0; $p < 7; $p++) {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
        }
    }
?>