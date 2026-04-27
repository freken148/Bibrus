<?php
    $warunek = '';
    $selected_object = 0;
    $fetchKlasa = 0;
    $fetchUczen = 0;
    
    function Init() {
        global $conn, $warunek, $fetchKlasa, $fetchUczen;
        $fetchKlasa = $_POST['wybrana_klasa'] ?? 1;
        $fetchUczen = $_POST['wybrany_uczen'] ?? 1;

        $sql = "SELECT id_ucznia
                FROM uczniowie 
                WHERE id_klasy = $fetchKlasa AND id_ucznia = $fetchUczen";
        
        $result = $conn->query($sql . ";");
        if ($result->num_rows <= 0) {       
            $sql = "SELECT id_ucznia 
                    FROM uczniowie 
                    WHERE id_klasy = $fetchKlasa
                    LIMIT 1";
            
            $result = $conn->query($sql . ";");
            $row = $result->fetch_assoc();
            $fetchUczen = $row['id_ucznia'];
        }

        if (isset($_POST['KlasaUczen']) && $_POST['KlasaUczen'] == 'klasa') {
            $warunek = "klasy.id_klasy = $fetchKlasa";
        } else {
            $warunek = "uczniowie.id_ucznia = $fetchUczen";
        } 
    }

    function SelectKlasy() {
        global $conn;

        $_SESSION['klasaDefault'] = $_POST['wybrana_klasa'] ?? 1;
        $selected_object = $_SESSION['klasaDefault'];

        $sql = "SELECT id_klasy, nazwa FROM klasy ORDER BY nazwa ASC";
        $result = $conn->query($sql);
        
        while($row = $result->fetch_assoc()) {
            $selected = ($row["id_klasy"] == $selected_object) ? "selected" : "";
            echo "<option value='" . $row["id_klasy"] . "' $selected>" . $row["nazwa"] . "</option>";
        }
    }

    function SelectUcznie() {
        global $conn, $selected_object;

        $_SESSION['klasaDefault'] = $_POST['wybrana_klasa'] ?? 5;
        $selected_object = $_SESSION['klasaDefault'];
        
        $_SESSION['uczenDefault'] = $_POST['wybrany_uczen'] ?? 1;
        $uczenID = $_SESSION['uczenDefault'];

        $sql = "SELECT id_ucznia, imie, nazwisko, nazwa 
                FROM uczniowie 
                INNER JOIN klasy ON uczniowie.id_klasy = klasy.id_klasy 
                WHERE klasy.id_klasy = $selected_object
                ORDER BY nazwa ASC";

        $result = $conn->query($sql . ";");
        
        while($row = $result->fetch_assoc()) {
            $selected = ($row["id_ucznia"] == $uczenID) ? "selected" : "";
            echo "<option value='" . $row['id_ucznia'] . "' $selected>" . $row['imie'] . ' ' . $row['nazwisko'] . ' ' . $row["nazwa"] . "</option>";
        }
    }

    function BugFixWhenClickUczenAndSelectKlasa() {
        global $conn, $fetchUczen, $fetchKlasa;
        $sql = "SELECT id_ucznia
                FROM uczniowie 
                WHERE id_klasy = $fetchKlasa AND id_ucznia = $fetchUczen";
        
        $result = $conn->query($sql . ";");
        if ($result->num_rows <= 0) {       
            $sql = "SELECT id_ucznia 
                    FROM uczniowie 
                    WHERE id_klasy = $fetchKlasa 
                    LIMIT 1";
            
            $result = $conn->query($sql . ";");
            $row = $result->fetch_assoc();
            $fetchUczen = $row['id_ucznia'];
        }
    }
?>