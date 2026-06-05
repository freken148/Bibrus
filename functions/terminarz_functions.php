<?php
    /*  Terminarz
        - Add and remove events by teacher who have lessons with certain class in certain day (except absence, it's should be possible to add in any day)
        - See events by months based on dates, not day of week like in real librus
        - Teacher must have to choice a type of event or absence, and range of lessons that it take
        - All types must be possible to see in lesson plan, except absence
        - Types of events:
        sprawdzian, kartkówka, nieobecność, zastępstwo, informacja, inne, wywiadówka
        - Supervisor of the class must have full access to change timetable events */

    function ShowTerminarz() {
        global $conn, $fetchKlasa;

        $year = $_POST['wybrany_rok'] ?? 1;
        $month = $_POST['wybrany_miesiac'] ?? 1;
        $klasa = $_POST['wybrana_klasa'] ?? 1;

        $date = new DateTime($year . '-' . $month . '-01');
        $dateString = $date->format('Y-m-d');
        $daysInMonth = intval($date->format('t'));
        $firstDayMonth = intval($date->format('N'));
        $date->modify('-1 days');
        $d = 1;

        echo "<table class='tableGlobal tableTerminarz tableTerminarzCalendar' border='1'>";
        echo "<tr><th>Pn</th><th>Wt</th><th>Śr</th><th>Cz</th><th>Pt</th><th>So</th><th>N</th></tr>";

        while ($d-$firstDayMonth < $daysInMonth) {
            echo "<tr>";
            for ($j = 1; $j < 8; $j++) {
                $date->modify('+1 days');
                $dateString = $date->format('Y-m-d');
                $sql = "SELECT
                            id_wydarzenia,
                            id_klasy,
                            terminarz.id_nauczyciela,
                            imie,
                            nazwisko,
                            nazwa,
                            typ_wydarzenia,
                            DATEDIFF(zakres_end, zakres_start) AS check1,
                            DATE_FORMAT(zakres_start, '%H:%i') AS time_start,
                            DATE_FORMAT(zakres_end, '%H:%i') AS time_end
                        FROM terminarz
                        INNER JOIN nauczyciele
                        ON terminarz.id_nauczyciela = nauczyciele.id_nauczyciela
                        INNER JOIN przedmioty
                        ON terminarz.id_przedmiotu = przedmioty.id_przedmiotu
                        WHERE id_klasy = $klasa
                        AND DATEDIFF('$dateString', zakres_start) >= 0
                        AND DATEDIFF(zakres_end, '$dateString') >= 0";
                $result = $conn->query($sql . ';');

                if ($firstDayMonth <= $d && $d-$firstDayMonth < $daysInMonth) {
                    echo "<td class='tdCalendarDay tdCalendarDayTerminarz'>";
                    echo "<div class='dayNumber'>" . ($d-$firstDayMonth+1) . "</div>";

                    while ($row = $result->fetch_assoc()) {
                        $Wid = $row['id_wydarzenia'];

                        echo "<div class='divGlobal divTerminarz eventCardWrapper'>";
                        echo "<label class='labelGlobal labelTerminarz eventDeleteLabel terminarzXButton' title='Usuń'>";
                        echo "X";
                        echo "<input onchange='this.form.submit()' type='radio' name='terminarzREMOVE' value='$Wid' hidden>";
                        echo "</label>";
                        echo "<div class='eventCard' onclick='this.parentNode.querySelector(\"input[name=terminarzINFO]\").checked=true; f=this.closest(\"form\"); f.action=\"terminarzInfoAdd.php\"; f.submit(); f.action=\"terminarz.php\"'>";
                        echo "<input type='radio' name='terminarzINFO' value='$Wid' hidden>";
                        echo "<div class='eventType'>" . htmlspecialchars($row['typ_wydarzenia']) . "</div>";
                        echo "<div class='eventSubject'>" . htmlspecialchars($row['nazwa']) . "</div>";
                        echo "<div class='eventTeacher'>" . htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) . "</div>";
                        if ($row['check1'] == 0) {
                            echo "<div class='eventTime'>" . $row['time_start'] . " – " . $row['time_end'] . "</div>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }

                    $dayofmonth = $d-$firstDayMonth;
                    echo "<input type='radio' name='terminarzAdd' value='$dayofmonth' hidden>";
                    echo "<label class='buttonGlobal buttonTerminarz addEventButton' title='Dodaj wpis' onclick='this.previousElementSibling.checked=true; f=this.closest(\"form\"); f.action=\"terminarzInfoAdd.php\"; f.submit();'>&plus;</label>";
                    echo "</td>";
                } else {
                    echo "<td class='tdCalendarDay tdCalendarDayEmpty'></td>";
                }
                $d++;
            }
            echo "</tr>";
        }
    }

    function miesiacSelect() {
        $months = array('Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień');

        $_SESSION['miesiacDefault'] = $_POST['wybrany_miesiac'] ?? 1;
        $selected_object = $_SESSION['miesiacDefault'];

        for ($i = 0; $i < 12; $i++) {
            $selected = ($selected_object == $i+1) ? "selected" : "";
            echo "<option class='optionGlobal optionTerminarz' value='" . ($i+1) . "' $selected>" . $months[$i] . "</option>";
        }
    }

    function rokSelect() {
        global $conn;
        $sql = "SELECT
                    DATE_FORMAT(MIN(zakres_start), '%Y') AS year_start,
                    DATE_FORMAT(MAX(zakres_end), '%Y') AS year_end
                FROM terminarz";
        $result = $conn->query($sql . ';');
        $row = $result->fetch_assoc();

        $start = $row['year_start'] ?? date('Y');
        $end = $row['year_end'] ?? date('Y');

        $_SESSION['rokDefault'] = $_POST['wybrany_rok'] ?? $start;
        $selected_object = $_SESSION['rokDefault'];

        $selected = ($selected_object == $start) ? "selected" : "";
        echo "<option class='optionGlobal optionTerminarz' value='" . $start . "' $selected>" . $start . "</option>";
        while($start != $end) {
            $start++;
            $selected = ($selected_object == $start) ? "selected" : "";
            echo "<option class='optionGlobal optionTerminarz' value='" . $start . "'  $selected>" . $start . "</option>";
        }
    }

    function terminarzDodaj() {
        global $conn;

        $zakresS = $_POST['zakresS'] ?? '';
        $zakresE = $_POST['zakresE'] ?? '';
        $typT = $_POST['typT'] ?? 'Inne';
        $opisT = $_POST['opisT'] ?? '';
        $id_nauczyciela = $_SESSION['id_nauczyciela'];
        $klasa = $_SESSION['klasaDefault'] ?? 1;

        $sql = "SELECT przedmioty.id_przedmiotu FROM przedmioty
                INNER JOIN nauczyciele ON przedmioty.id_przedmiotu = nauczyciele.id_przedmiotu
                WHERE id_nauczyciela = $id_nauczyciela";
        $result = $conn->query($sql . ';');
        $row = $result->fetch_assoc();
        $id_przedmiotu = $row['id_przedmiotu'] ?? null;

        if (!$id_przedmiotu) {
            $id_przedmiotu = 0;
        }

        $zakresSEsc = $conn->real_escape_string($zakresS);
        $zakresEEsc = $conn->real_escape_string($zakresE);
        $typTEsc = $conn->real_escape_string($typT);
        $opisTEsc = $conn->real_escape_string($opisT);

        $sql = "INSERT INTO terminarz (id_klasy, id_nauczyciela, id_przedmiotu, typ_wydarzenia, opis, zakres_start, zakres_end, data_dodania)
                VALUES ($klasa, $id_nauczyciela, $id_przedmiotu, '$typTEsc', '$opisTEsc', '$zakresSEsc', '$zakresEEsc', NOW())";
        $conn->query($sql . ';');
    }

    function terminarzRemove() {
        global $conn;

        $Wid = intval($_POST['terminarzREMOVE'] ?? 0);
        $id_nauczyciela = $_SESSION['id_nauczyciela'];

        // only allow removal of own events
        $sql = "DELETE FROM terminarz WHERE id_wydarzenia = $Wid AND id_nauczyciela = $id_nauczyciela";
        $conn->query($sql . ';');
    }
?>
