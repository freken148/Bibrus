<?php
/**
 * Database Seeder for bibrus database
 * 
 * Instructions:
 * 1. Ensure your database tables are created.
 * 2. Configure the database connection variables below.
 * 3. Run this script via CLI (`php seeder.php`) or in your browser.
 */

// --- CONFIGURATION ---
$dbHost = '127.0.0.1';
$dbName = 'bibrus';
$dbUser = 'root';        // Change if needed
$dbPass = '';            // Change if needed
$dbChar = 'utf8mb4';

// --- DATA VOLUME SETTINGS ---
$numClasses = 6;            // e.g., 1A, 1B, 2A, 2B, 3A, 3B
$studentsPerClass = 25;     // Total students = $numClasses * $studentsPerClass
$gradesPerStudent = 15;     // Number of grades each student will receive
$attendancePerStudent = 40; // Number of attendance records per student
$eventsPerClass = 5;        // Number of calendar entries in 'terminarz' per class
$remarksCount = 60;         // Total school remarks across all students

// --- SEED LISTS (POLISH) ---
$firstNamesMale = ['Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Paweł', 'Marcin', 'Michał', 'Jakub', 'Wojciech', 'Adam', 'Łukasz', 'Marek', 'Grzegorz', 'Mateusz', 'Kamil', 'Mariusz', 'Szymon', 'Maciej', 'Sebastian'];
$firstNamesFemale = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Krystyna', 'Magdalena', 'Elżbieta', 'Joanna', 'Aleksandra', 'Zofia', 'Monika', 'Marta', 'Patrycja', 'Natalia', 'Karolina', 'Sandra', 'Alicja'];
$lastNamesMale = ['Nowak', 'Kowalski', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński', 'Szymański', 'Woźniak', 'Kozłowski', 'Jankowski', 'Mazur', 'Wojciechowski', 'Kwiatkowski', 'Krawczyk', 'Kaczmarek', 'Piotrowski', 'Grabowski', 'Pawłowski'];
$lastNamesFemale = ['Nowak', 'Kowalska', 'Wiśniewska', 'Wójcik', 'Kowalczyk', 'Kamińska', 'Lewandowska', 'Zielińska', 'Szymańska', 'Woźniak', 'Kozłowska', 'Jankowska', 'Mazur', 'Wojciechowska', 'Kwiatkowska', 'Krawczyk', 'Kaczmarek', 'Piotrowska', 'Grabowska', 'Pawłowska'];

$subjects = [
    'Język polski', 'Matematyka', 'Język angielski', 'Język niemiecki',
    'Fizyka', 'Chemia', 'Biologia', 'Geografia', 'Historia',
    'Informatyka', 'Wychowanie fizyczne', 'Wiedza o społeczeństwie'
];

// Passed as strings to prevent strict Float precision FK constraint errors in PDO/MySQL
$validGrades = ['1', '1.5', '2', '2.5', '1.75', '3', '3.5', '2.75', '4', '4.5', '3.75', '5', '5.5', '4.75', '6', '5.75'];

$gradeComments = [
    'Aktywność na lekcji', 'Sprawdzian pisemny', 'Kartkówka', 'Praca domowa', 
    'Odpowiedź ustna', 'Projekt grupowy', 'Praca klasowa', 'Przygotowanie do lekcji'
];

$remarkTextsPositive = [
    'Wzorowa postawa podczas reprezentowania szkoły na zewnątrz.',
    'Bardzo duże zaangażowanie w pomoc przy organizacji szkolnego kiermaszu.',
    'Pomoc koledze w nauce trudnego tematu.',
    'Aktywny udział w dyskusji lekcyjnej i przygotowanie dodatkowych materiałów.',
    'Uczciwe zachowanie i zgłoszenie zgubionego przedmiotu.'
];

$remarkTextsNegative = [
    'Przeszkadzanie w prowadzeniu lekcji mimo wielokrotnych uwag.',
    'Korzystanie z telefonu komórkowego podczas sprawdzianu.',
    'Brak przygotowania do zajęć oraz brak podręczników.',
    'Niewłaściwe zachowanie wobec rówieśników na przerwie.',
    'Spóźnienie na lekcję bez usprawiedliwienia oraz lekceważący stosunek.'
];

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbChar";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Ensures accurate type binding
    ];
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    
    echo "Connection to the database established.\n<br>";
    
    // Start transaction for high insertion speed
    $pdo->beginTransaction();
    
    // 1. INSERT SUBJECTS (Przedmioty)
    echo "Inserting subjects...\n<br>";
    $stmtSubject = $pdo->prepare("INSERT INTO Przedmioty (nazwa) VALUES (:nazwa)");
    $subjectIds = [];
    foreach ($subjects as $sub) {
        $stmtSubject->execute([':nazwa' => $sub]);
        $subjectIds[] = $pdo->lastInsertId();
    }
    
    // 2. INSERT TEACHERS (Nauczyciele)
    echo "Inserting teachers...\n<br>";
    $stmtTeacher = $pdo->prepare("INSERT INTO Nauczyciele (id_przedmiotu, imie, nazwisko, Haslo) VALUES (:id_przedmiotu, :imie, :nazwisko, :haslo)");
    $teacherIds = [];
    $teacherToSubjectMap = []; // In-memory map for blazing fast lookup later
    
    // Ensure we have at least one teacher per subject
    foreach ($subjectIds as $subId) {
        $gender = rand(0, 1) === 0 ? 'male' : 'female';
        $name = ($gender === 'male') ? $firstNamesMale[array_rand($firstNamesMale)] : $firstNamesFemale[array_rand($firstNamesFemale)];
        $surname = ($gender === 'male') ? $lastNamesMale[array_rand($lastNamesMale)] : $lastNamesFemale[array_rand($lastNamesFemale)];
        
        $stmtTeacher->execute([
            ':id_przedmiotu' => $subId,
            ':imie' => $name,
            ':nazwisko' => $surname,
            ':haslo' => 'Sala332!'
        ]);
        $tId = $pdo->lastInsertId();
        $teacherIds[] = $tId;
        $teacherToSubjectMap[$tId] = $subId;
    }
    
    // Add extra teachers (shared subjects)
    for ($i = 0; $i < 5; $i++) {
        $gender = rand(0, 1) === 0 ? 'male' : 'female';
        $name = ($gender === 'male') ? $firstNamesMale[array_rand($firstNamesMale)] : $firstNamesFemale[array_rand($firstNamesFemale)];
        $surname = ($gender === 'male') ? $lastNamesMale[array_rand($lastNamesMale)] : $lastNamesFemale[array_rand($lastNamesFemale)];
        $subId = $subjectIds[array_rand($subjectIds)];
        
        $stmtTeacher->execute([
            ':id_przedmiotu' => $subId,
            ':imie' => $name,
            ':nazwisko' => $surname,
            ':haslo' => 'Sala332!'
        ]);
        $tId = $pdo->lastInsertId();
        $teacherIds[] = $tId;
        $teacherToSubjectMap[$tId] = $subId;
    }

    // 3. INSERT CLASSES (Klasy)
    echo "Inserting classes...\n<br>";
    $stmtClass = $pdo->prepare("INSERT INTO Klasy (id_wychowawcy, nazwa) VALUES (:id_wychowawcy, :nazwa)");
    
    $classIds = [];
    $yearPrefix = 1;
    $letterIndex = 0;
    $letters = ['A', 'B', 'C', 'D'];
    
    for ($i = 0; $i < $numClasses; $i++) {
        $className = $yearPrefix . $letters[$letterIndex];
        $wychowawcaId = $teacherIds[array_rand($teacherIds)]; 
        
        $stmtClass->execute([
            ':id_wychowawcy' => $wychowawcaId,
            ':nazwa' => $className
        ]);
        $classIds[] = $pdo->lastInsertId();
        
        $letterIndex++;
        if ($letterIndex >= count($letters)) {
            $letterIndex = 0;
            $yearPrefix++;
        }
    }

    // 4. INSERT STUDENTS (Uczniowie)
    echo "Inserting students...\n<br>";
    $stmtStudent = $pdo->prepare("INSERT INTO Uczniowie (id_klasy, imie, nazwisko) VALUES (:id_klasy, :imie, :nazwisko)");
    $studentIds = [];
    
    foreach ($classIds as $classId) {
        for ($s = 0; $s < $studentsPerClass; $s++) {
            $gender = rand(0, 1) === 0 ? 'male' : 'female';
            $name = ($gender === 'male') ? $firstNamesMale[array_rand($firstNamesMale)] : $firstNamesFemale[array_rand($firstNamesFemale)];
            $surname = ($gender === 'male') ? $lastNamesMale[array_rand($lastNamesMale)] : $lastNamesFemale[array_rand($lastNamesFemale)];
            
            $stmtStudent->execute([
                ':id_klasy' => $classId,
                ':imie' => $name,
                ':nazwisko' => $surname
            ]);
            $studentIds[] = $pdo->lastInsertId();
        }
    }

    // 5. INSERT SCHEDULE (Plan Lekcji)
    echo "Inserting timetables (planLekcji)...\n<br>";
    $stmtPlan = $pdo->prepare("INSERT INTO planLekcji (id_klasy, id_nauczyciela, id_przedmiotu, numer_lekcji, numer_sali, numer_dnia) VALUES (:id_klasy, :id_nauczyciela, :id_przedmiotu, :numer_lekcji, :numer_sali, :numer_dnia)");
    
    foreach ($classIds as $classId) {
        for ($day = 1; $day <= 5; $day++) {
            $numLessonsToday = rand(4, 7); // 4 to 7 lessons per day
            for ($lessonNum = 1; $lessonNum <= $numLessonsToday; $lessonNum++) {
                $randTeacherId = $teacherIds[array_rand($teacherIds)];
                $teacherSub = $teacherToSubjectMap[$randTeacherId];
                
                $stmtPlan->execute([
                    ':id_klasy' => $classId,
                    ':id_nauczyciela' => $randTeacherId,
                    ':id_przedmiotu' => $teacherSub,
                    ':numer_lekcji' => $lessonNum, // References LekcjeDictionary 1-14
                    ':numer_sali' => rand(101, 315),
                    ':numer_dnia' => $day // References dnitygodnia 1-5 (Mon-Fri)
                ]);
            }
        }
    }

    // 6. INSERT GRADES (Oceny)
    echo "Inserting grades (oceny)...\n<br>";
    $stmtGrade = $pdo->prepare("INSERT INTO Oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, waga, komentarz) VALUES (:id_ucznia, :id_przedmiotu, :id_nauczyciela, :data, :ocena, :waga, :komentarz)");

    foreach ($studentIds as $studentId) {
        for ($g = 0; $g < $gradesPerStudent; $g++) {
            $tId = array_rand($teacherToSubjectMap);
            $pId = $teacherToSubjectMap[$tId];
            
            $timestamp = rand(strtotime('-5 months'), time());
            $date = date('Y-m-d H:i:s', $timestamp);
            $ocena = $validGrades[array_rand($validGrades)];
            $waga = rand(1, 5);
            $komentarz = $gradeComments[array_rand($gradeComments)];
            
            $stmtGrade->execute([
                ':id_ucznia' => $studentId,
                ':id_przedmiotu' => $pId,
                ':id_nauczyciela' => $tId,
                ':data' => $date,
                ':ocena' => $ocena,
                ':waga' => $waga,
                ':komentarz' => $komentarz
            ]);
        }
    }

    // 7. INSERT ATTENDANCE (Frekwencja)
    echo "Inserting attendance (frekwencja)...\n<br>";
    $stmtAttendance = $pdo->prepare("INSERT INTO Frekwencja (id_ucznia, id_przedmiotu, data, typ) VALUES (:id_ucznia, :id_przedmiotu, :data, :typ)");
    
    // Higher ratio of "Obecny" to simulate realistic school attendance
    $attendanceTypes = ['Obecny', 'Obecny', 'Obecny', 'Obecny', 'Obecny', 'Obecny', 'Nieobecny', 'Spóźniony', 'Usprawiedliwiony', 'Zwolniony'];

    foreach ($studentIds as $studentId) {
        for ($a = 0; $a < $attendancePerStudent; $a++) {
            $pId = $subjectIds[array_rand($subjectIds)];
            
            $timestamp = rand(strtotime('-4 months'), time());
            $dayOfWeek = date('N', $timestamp);
            
            // Push weekend dates to Friday
            if ($dayOfWeek == 6) $timestamp -= 86400; // Saturday to Friday
            if ($dayOfWeek == 7) $timestamp -= 172800; // Sunday to Friday
            
            $hour = rand(8, 15);
            $minute = rand(0, 59);
            $date = date("Y-m-d $hour:$minute:00", $timestamp);
            
            $type = $attendanceTypes[array_rand($attendanceTypes)];
            
            $stmtAttendance->execute([
                ':id_ucznia' => $studentId,
                ':id_przedmiotu' => $pId,
                ':data' => $date,
                ':typ' => $type
            ]);
        }
    }

    // 8. INSERT EVENTS CALENDAR (Terminarz)
    echo "Inserting schedule events (terminarz)...\n<br>";
    $stmtEvent = $pdo->prepare("INSERT INTO terminarz (id_klasy, id_nauczyciela, id_przedmiotu, typ_wydarzenia, opis, zakres_start, zakres_end, data_dodania) VALUES (:id_klasy, :id_nauczyciela, :id_przedmiotu, :typ_wydarzenia, :opis, :zakres_start, :zakres_end, :data_dodania)");
    
    $eventTypes = ['Sprawdzian', 'Kartkówka', 'Nieobecność', 'Zastępstwo', 'Informacja', 'Inne', 'Wywiadówka'];
    $eventDescriptions = [
        'Sprawdzian' => 'Sprawdzian semestralny z działu drugiego.',
        'Kartkówka' => 'Krótki sprawdzian z trzech ostatnich lekcji.',
        'Nieobecność' => 'Nauczyciel nieobecny z przyczyn zdrowotnych.',
        'Zastępstwo' => 'Zastępstwo w sali gimnastycznej.',
        'Informacja' => 'Przynieść zgody na wycieczkę szkolną.',
        'Inne' => 'Apel z okazji rocznicy narodowej.',
        'Wywiadówka' => 'Spotkanie z rodzicami o godzinie 17:30.'
    ];

    foreach ($classIds as $classId) {
        for ($e = 0; $e < $eventsPerClass; $e++) {
            $tId = array_rand($teacherToSubjectMap);
            $pId = $teacherToSubjectMap[$tId];
            $type = $eventTypes[array_rand($eventTypes)];
            
            $startTs = rand(strtotime('now'), strtotime('+2 months'));
            $startDate = date('Y-m-d H:i:s', $startTs);
            $endDate = date('Y-m-d H:i:s', $startTs + (45 * 60)); // +45 minutes
            $dateAdded = date('Y-m-d H:i:s', $startTs - (7 * 86400)); // Added 7 days prior
            
            $stmtEvent->execute([
                ':id_klasy' => $classId,
                ':id_nauczyciela' => $tId,
                ':id_przedmiotu' => $pId,
                ':typ_wydarzenia' => $type,
                ':opis' => $eventDescriptions[$type],
                ':zakres_start' => $startDate,
                ':zakres_end' => $endDate,
                ':data_dodania' => $dateAdded
            ]);
        }
    }

    // 9. INSERT REMARKS (Uwagi)
    echo "Inserting remarks (uwagi)...\n<br>";
    $stmtRemark = $pdo->prepare("INSERT INTO Uwagi (id_ucznia, id_nauczyciela, typ, opis, data) VALUES (:id_ucznia, :id_nauczyciela, :typ, :opis, :data)");
    
    for ($r = 0; $r < $remarksCount; $r++) {
        $studentId = $studentIds[array_rand($studentIds)];
        $tId = array_rand($teacherToSubjectMap);
        $type = rand(0, 1) === 0 ? 'Pozytywna' : 'Negatywna';
        $desc = ($type === 'Pozytywna') ? $remarkTextsPositive[array_rand($remarkTextsPositive)] : $remarkTextsNegative[array_rand($remarkTextsNegative)];
        
        $date = date('Y-m-d H:i:s', rand(strtotime('-3 months'), time()));
        
        $stmtRemark->execute([
            ':id_ucznia' => $studentId,
            ':id_nauczyciela' => $tId,
            ':typ' => $type,
            ':opis' => $desc,
            ':data' => $date
        ]);
    }

    // Commit transaction
    $pdo->commit();
    echo "<h2>Database successfully seeded! ✅</h2>";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h2>An error occurred while seeding:</h2>";
    echo "<p style='color:red'>" . $e->getMessage() . "</p>";
}