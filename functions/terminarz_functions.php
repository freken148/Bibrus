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

        $date = new DateTime($year . '-' . $month . '-01'); // ley say month 02
        $daysInMonth = intval($date->format('t')); // 28
        $firstDayMonth = intval($date->format('N')); // Sunday - 7
        $d = 1;

        echo "<table border='1'>";
        echo "<tr><th>Pn</th><th>Wt</th><th>Śr</th><th>Cz</th><th>Pt</th><th>So</th><th>N</th></tr>";
        
        while ($d-$firstDayMonth < $daysInMonth) { 
            echo "<tr>";
            for ($j = 1; $j < 8; $j++) { 
                if ($firstDayMonth <= $d && $d-$firstDayMonth < $daysInMonth) {
                    echo "<td>";
                    echo $d-$firstDayMonth+1;           
                    echo "</td>"; 
                } else {
                    echo "<td></td>";
                }
                $d++;
            }
            echo "</tr>";
        }
 
    }

    function miesiacSelect() {
        $months = array('Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sieprień', 'Wrzesień', 'Paźdźiernik', 'Listopad', 'Grudzień');
        $_SESSION['miesiacDefault'] = $_POST['wybrany_miesiac'] ?? 1;
        $selected_object = $_SESSION['miesiacDefault'];
        
        for ($i = 0; $i < 12; $i++) {
            $selected = ($selected_object == $i+1) ? "selected" : "";
            echo "<option value='" . $i+1 . "' $selected>" . $months[$i] . "</option>";
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
        $start = $row['year_start'];
        $end = $row['year_end'];
        echo "<option value='" . $start . "'>" . $start . "</option>";
        while($start != $end) {
            $start++;
            echo "<option value='" . $start . "'>" . $start . "</option>";
        }
    }
?>