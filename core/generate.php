<?php

// ==========================================
// CONFIGURATION
// ==========================================
$db_host = '127.0.0.1';
$db_name = 'bibrus'; // Change to your DB name
$db_user = 'root';   // Change to your DB username
$db_pass = '';       // Change to your DB password
$charset = 'utf8mb4';

// Generation settings
$truncateFirst     = true; // Set to true to wipe existing data before generating
$num_classes       = 12;   // e.g., 1A, 1B, 2A, etc.
$students_per_class = 25;  // ~300 students total
$grades_per_student = 10;  // ~9000 grades total
$attend_per_student = 100;  // ~12000 attendance records total
$notes_per_student  = 10;   // ~1200 notes total

// ==========================================
// DATABASE CONNECTION
// ==========================================
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==========================================
// HELPER DICTIONARIES
// ==========================================
$imiona_m = ['Jan', 'Piotr', 'Krzysztof', 'Tomasz', 'Paweł', 'Michał', 'Marcin', 'Jakub', 'Adam', 'Stanisław', 'Mateusz', 'Kamil'];
$imiona_k = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Krystyna', 'Zofia', 'Julia', 'Maja', 'Zuzanna'];
$nazwiska = ['Nowak', 'Kowalski', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski', 'Mazur', 'Kwiatkowski', 'Krawczyk'];

$przedmioty_nazwy = ['Matematyka', 'Język polski', 'Język angielski', 'Język niemiecki', 'Historia', 'Geografia', 'Biologia', 'Chemia', 'Fizyka', 'Informatyka', 'Wychowanie fizyczne', 'Plastyka', 'Muzyka', 'WOS', 'EDB'];

$oceny_wartosci = [1, 1.5, 1.75, 2, 2.5, 2.75, 3, 3.5, 3.75, 4, 4.5, 4.75, 5, 5.5, 5.75, 6];
$frek_typy = ['Obecny', 'Usprawiedliwiony', 'Nieobecny', 'Zwolniony', 'Spóźniony'];
$uwagi_typy = ['Pozytywna', 'Negatywna'];
$wydarzenia_typy = ['Sprawdzian', 'Kartkówka', 'Nieobecność', 'Zastępstwo', 'Informacja', 'Inne', 'Wywiadówka'];
$komentarze_oceny = ['Zadanie domowe', 'Aktywność', 'Sprawdzian', 'Kartkówka', 'Odpowiedź ustna', 'Projekt', 'Praca na lekcji', 'Brak zadania'];

// Helper to generate a random date safely
function randomDate($startDate = null, $endDate = null) {
    // Default to the current school year if no dates are provided
    if (!$startDate) $startDate = date('Y-09-01', strtotime('-1 year'));
    if (!$endDate) $endDate = date('Y-06-20');

    $min = strtotime($startDate);
    $max = strtotime($endDate);

    // Prevent ValueError in PHP 8+ if dates are reversed
    if ($min > $max) {
        $temp = $min;
        $min = $max;
        $max = $temp;
    }

    return date('Y-m-d H:i:s', mt_rand($min, $max));
}

// ==========================================
// DATA GENERATION SCRIPT
// ==========================================
try {
    if ($truncateFirst) {
        echo "Truncating existing tables...\n";
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE Uwagi');
        $pdo->exec('TRUNCATE TABLE terminarz');
        $pdo->exec('TRUNCATE TABLE planLekcji');
        $pdo->exec('TRUNCATE TABLE Frekwencja');
        $pdo->exec('TRUNCATE TABLE Oceny');
        $pdo->exec('TRUNCATE TABLE Uczniowie');
        $pdo->exec('TRUNCATE TABLE Klasy');
        $pdo->exec('TRUNCATE TABLE Nauczyciele');
        $pdo->exec('TRUNCATE TABLE Przedmioty');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    echo "Generating new data...\n";
    $pdo->beginTransaction();

    // 1. GENERATE PRZEDMIOTY (Subjects)
    $stmt = $pdo->prepare("INSERT INTO Przedmioty (nazwa) VALUES (?)");
    $przedmioty_ids = [];
    foreach ($przedmioty_nazwy as $nazwa) {
        $stmt->execute([$nazwa]);
        $przedmioty_ids[] = $pdo->lastInsertId();
    }

    // 2. GENERATE NAUCZYCIELE (Teachers)
    $stmt = $pdo->prepare("INSERT INTO Nauczyciele (id_przedmiotu, imie, nazwisko, Haslo) VALUES (?, ?, ?, 'Sala332!')");
    $nauczyciele_ids = [];
    $teachers_by_subject = [];

    $num_teachers = max(30, count($przedmioty_ids));
    for ($i = 0; $i < $num_teachers; $i++) {
        $isMale = mt_rand(0, 1);
        $imie = $isMale ? $imiona_m[array_rand($imiona_m)] : $imiona_k[array_rand($imiona_k)];
        
        $nazwisko = $nazwiska[array_rand($nazwiska)];
        if (!$isMale && str_ends_with($nazwisko, 'i')) {
            $nazwisko = substr($nazwisko, 0, -1) . 'a';
        }

        $id_przedmiotu = ($i < count($przedmioty_ids)) ? $przedmioty_ids[$i] : $przedmioty_ids[array_rand($przedmioty_ids)];
        
        $stmt->execute([$id_przedmiotu, $imie, $nazwisko]);
        $tid = $pdo->lastInsertId();
        $nauczyciele_ids[] = $tid;
        $teachers_by_subject[$id_przedmiotu][] = $tid;
    }

    // 3. GENERATE KLASY (Classes)
    $stmt = $pdo->prepare("INSERT INTO Klasy (id_wychowawcy, nazwa) VALUES (?, ?)");
    $klasy_ids = [];
    $klasy_nazwy = ['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '3C', '4A', '4B', '4C'];
    $klasy_nazwy = array_slice($klasy_nazwy, 0, $num_classes);

    $available_teachers = $nauczyciele_ids;
    shuffle($available_teachers);

    foreach ($klasy_nazwy as $index => $nazwa) {
        $id_wychowawcy = $available_teachers[$index % count($available_teachers)];
        $stmt->execute([$id_wychowawcy, $nazwa]);
        $klasy_ids[] = $pdo->lastInsertId();
    }

    // 4. GENERATE UCZNIOWIE (Students)
    $stmt = $pdo->prepare("INSERT INTO Uczniowie (id_klasy, imie, nazwisko) VALUES (?, ?, ?)");
    $uczniowie_ids = [];
    foreach ($klasy_ids as $id_klasy) {
        for ($i = 0; $i < $students_per_class; $i++) {
            $isMale = mt_rand(0, 1);
            $imie = $isMale ? $imiona_m[array_rand($imiona_m)] : $imiona_k[array_rand($imiona_k)];
            $nazwisko = $nazwiska[array_rand($nazwiska)];
            if (!$isMale && str_ends_with($nazwisko, 'i')) $nazwisko = substr($nazwisko, 0, -1) . 'a';

            $stmt->execute([$id_klasy, $imie, $nazwisko]);
            $uczniowie_ids[] = $pdo->lastInsertId();
        }
    }

    // 5. GENERATE OCENY (Grades)
    $stmt = $pdo->prepare("INSERT INTO Oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, waga, komentarz) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($uczniowie_ids as $id_ucznia) {
        for ($i = 0; $i < $grades_per_student; $i++) {
            $id_przedmiotu = $przedmioty_ids[array_rand($przedmioty_ids)];
            $id_nauczyciela = $teachers_by_subject[$id_przedmiotu][array_rand($teachers_by_subject[$id_przedmiotu])];
            $data = randomDate();
            $ocena = $oceny_wartosci[array_rand($oceny_wartosci)];
            $waga = mt_rand(1, 5);
            $komentarz = $komentarze_oceny[array_rand($komentarze_oceny)];

            $stmt->execute([$id_ucznia, $id_przedmiotu, $id_nauczyciela, $data, $ocena, $waga, $komentarz]);
        }
    }

    // 6. GENERATE FREKWENCJA (Attendance)
    $stmt = $pdo->prepare("INSERT INTO Frekwencja (id_ucznia, id_przedmiotu, id_nauczyciela, data, typ) VALUES (?, ?, ?, ?, ?)");
    foreach ($uczniowie_ids as $id_ucznia) {
        for ($i = 0; $i < $attend_per_student; $i++) {
            $id_przedmiotu = $przedmioty_ids[array_rand($przedmioty_ids)];
            $id_nauczyciela = $teachers_by_subject[$id_przedmiotu][array_rand($teachers_by_subject[$id_przedmiotu])];
            $data = randomDate();
            
            $rand = mt_rand(1, 100);
            if ($rand <= 75) $typ = 'Obecny';
            elseif ($rand <= 85) $typ = 'Nieobecny';
            elseif ($rand <= 95) $typ = 'Usprawiedliwiony';
            elseif ($rand <= 98) $typ = 'Spóźniony';
            else $typ = 'Zwolniony';

            $stmt->execute([$id_ucznia, $id_przedmiotu, $id_nauczyciela, $data, $typ]);
        }
    }

    // 7. GENERATE PLAN LEKCJI (Timetable)
    $stmt = $pdo->prepare("INSERT INTO planLekcji (id_klasy, id_nauczyciela, id_przedmiotu, numer_lekcji, numer_sali, numer_dnia) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($klasy_ids as $id_klasy) {
        for ($dzien = 1; $dzien <= 5; $dzien++) {
            for ($lekcja = 1; $lekcja <= 7; $lekcja++) {
                $id_przedmiotu = $przedmioty_ids[array_rand($przedmioty_ids)];
                $id_nauczyciela = $teachers_by_subject[$id_przedmiotu][array_rand($teachers_by_subject[$id_przedmiotu])];
                $sala = mt_rand(100, 399);

                $stmt->execute([$id_klasy, $id_nauczyciela, $id_przedmiotu, $lekcja, $sala, $dzien]);
            }
        }
    }

    // 8. GENERATE TERMINARZ (Events/Schedule)
    $stmt = $pdo->prepare("INSERT INTO terminarz (id_klasy, id_nauczyciela, id_przedmiotu, typ_wydarzenia, opis, zakres_start, zakres_end, data_dodania) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($klasy_ids as $id_klasy) {
        for ($i = 0; $i < 100; $i++) {
            $id_przedmiotu = $przedmioty_ids[array_rand($przedmioty_ids)];
            $id_nauczyciela = $teachers_by_subject[$id_przedmiotu][array_rand($teachers_by_subject[$id_przedmiotu])];
            $typ = $wydarzenia_typy[array_rand($wydarzenia_typy)];
            $opis = "Zaplanowane wydarzenie: " . $typ;
            
            // Replaced hardcoded date with relative 6-months future date
            $start_date = randomDate(date('Y-m-d'), date('Y-m-d', strtotime('+6 months')));
            $end_date = date('Y-m-d H:i:s', strtotime($start_date) + 3600); // 1 hour duration
            $data_dodania = date('Y-m-d H:i:s');

            $stmt->execute([$id_klasy, $id_nauczyciela, $id_przedmiotu, $typ, $opis, $start_date, $end_date, $data_dodania]);
        }
    }

    // 9. GENERATE UWAGI (Notes / Remarks)
    $stmt = $pdo->prepare("INSERT INTO Uwagi (id_ucznia, id_nauczyciela, typ, opis, data) VALUES (?, ?, ?, ?, ?)");
    foreach ($uczniowie_ids as $id_ucznia) {
        for ($i = 0; $i < $notes_per_student; $i++) {
            $id_nauczyciela = $nauczyciele_ids[array_rand($nauczyciele_ids)];
            $typ = $uwagi_typy[array_rand($uwagi_typy)];
            $opis = ($typ == 'Pozytywna') ? "Uczeń wykazał się dużą aktywnością na lekcji." : "Brak kultury słowa i przeszkadzanie w prowadzeniu lekcji.";
            $data = randomDate();

            $stmt->execute([$id_ucznia, $id_nauczyciela, $typ, $opis, $data]);
        }
    }

    // Commit all inserts
    $pdo->commit();
    echo "Successfully generated all data!\n";

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error generating data: " . $e->getMessage() . "\n";
}

?>