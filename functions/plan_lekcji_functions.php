<?php
    function ShowPlan() {
        global $conn, $fetchKlasa;

        echo "<table class='tableGlobal tablePlanLekcji tableTerminarzCalendar'>";
        echo "<thead>";
        echo "<tr> <th class='thLesson'>Nr. lekcji</th> <th class='thLesson'>Godziny</th> <th>Poniedziałek</th> <th>Wtorek</th> <th>Środa</th> <th>Czwartek</th> <th>Piątek</th> <th>Sobota</th> <th>Niedziela</th> </tr>";
        echo "</thead>";
        echo "<tbody>";
        for ($i = 1; $i < 15; $i++) {
            $sql = "SELECT numer_lekcji, godzina_lekcji AS godzina_start, DATE_ADD(godzina_lekcji, INTERVAL 45 MINUTE) AS godzina_end
                    FROM lekcjedictionary
                    WHERE numer_lekcji = $i";
            $result = $conn->query($sql . ';');
            $row = $result->fetch_assoc();

            $timeStart = substr($row['godzina_start'], 0, -3);
            $timeEnd = substr($row['godzina_end'], 0, -3);

            // lesson row
            echo "<tr class='trLesson'>";
            echo "<td class='tdLessonNum'>" . ($i-1) . "</td>";
            echo "<td class='tdLessonTime'>" . $timeStart . " – " . $timeEnd . "</td>";
            for ($j = 0; $j < 7; $j++) {
                $sql = "SELECT nazwa, imie, nazwisko, numer_sali
                        FROM planlekcji
                        INNER JOIN przedmioty ON planlekcji.id_przedmiotu = przedmioty.id_przedmiotu
                        INNER JOIN nauczyciele ON planlekcji.id_nauczyciela = nauczyciele.id_nauczyciela
                        WHERE numer_lekcji = $i AND numer_dnia = $j+1 AND id_klasy = $fetchKlasa";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();

                if ($result->num_rows <= 0) {
                    echo "<td class='tdLessonEmpty'>&nbsp;</td>";
                } else {
                    echo "<td class='tdLessonCell'>";
                    echo "<div class='lessonSubject'>" . htmlspecialchars($row['nazwa']) . "</div>";
                    echo "<div class='lessonTeacher'>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</div>";
                    echo "<div class='lessonRoom'>Sala " . htmlspecialchars($row['numer_sali']) . "</div>";
                    echo "</td>";
                }
            }
            echo "</tr>";

            // break row (smaller, no borders)
            if ($i < 14) {
                $sql = "SELECT godzina_lekcji AS godzina_start
                        FROM lekcjedictionary
                        WHERE numer_lekcji = $i+1";
                $result = $conn->query($sql . ';');
                $row = $result->fetch_assoc();
                $timeStartNext = substr($row['godzina_start'], 0, -3);
                echo "<tr class='trBreak'>";
                echo "<td class='tdBreakNum'></td>";
                echo "<td class='tdBreakTime'>" . $timeEnd . " – " . $timeStartNext . "</td>";
                for ($p = 0; $p < 7; $p++) {
                    echo "<td class='tdBreakCell'></td>";
                }
                echo "</tr>";
            }
        }
        echo "</tbody>";
        echo "</table>";
    }
?>
