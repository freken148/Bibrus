<?php

// ==========================================
// CONFIGURATION - UPDATE THESE FOR YOUR DB
// ==========================================
$db_host = '127.0.0.1';
$db_name = 'bibrus';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "Connected to database. Generating data...\n";

// ==========================================
// DATA DICTIONARIES (For realistic generation)
// ==========================================

$subjectsList = ['Matematyka', 'Język polski', 'Język angielski', 'Historia', 'Biologia', 'Chemia', 'Fizyka', 'Geografia', 'Informatyka', 'Wychowanie fizyczne', 'WOS', 'Plastyka', 'Muzyka'];

$imionaMale = ['Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Michał', 'Marcin', 'Jakub', 'Adam', 'Stanisław', 'Kamil', 'Kacper', 'Szymon', 'Mikołaj', 'Filip', 'Antoni', 'Wojciech', 'Mateusz'];
$nazwiskaMale = ['Nowak', 'Kowalski', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski', 'Jankowski', 'Mazur'];

$imionaFemale = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Krystyna', 'Alina', 'Julia', 'Zuzanna', 'Zofia', 'Hanna', 'Maja', 'Lena', 'Alicja', 'Oliwia'];
$nazwiskaFemale = ['Nowak', 'Kowalska', 'Wiśniewska', 'Wójcik', 'Kowalczyk', 'Kamińska', 'Lewandowska', 'Zielińska', 'Szymańska', 'Woźniak', 'Dąbrowska', 'Kozłowska', 'Jankowska', 'Mazur'];

$ocenyValues = [1, 1.5, 2, 2.5, 1.75, 3, 3.5, 2.75, 4, 4.5, 3.75, 5, 5.5, 4.75, 6, 5.75];
$attendanceTypes = ['Obecny', 'Obecny', 'Obecny', 'Obecny', 'Obecny', 'Usprawiedliwiony', 'Nieobecny', 'Zwolniony', 'Spóźniony']; // More 'Obecny' to make it realistic
$eventTypes = ['sprawdzian', 'kartkówka', 'nieobecność', 'zastępstwo', 'informacja', 'inne', 'wywiadówka'];

$gradeComments = ['Brak zadania', 'Odpowiedź ustna', 'Sprawdzian', 'Kartkówka', 'Aktywność na lekcji', 'Praca w grupie', 'Projekt', 'Zadanie domowe', 'Złe zachowanie', ''];

// ==========================================
// 1. GENERATE SUBJECTS (Przedmioty)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Przedmioty (nazwa) VALUES (?)");
$subjectIds = [];
foreach ($subjectsList as $subject) {
    $stmt->execute([$subject]);
    $subjectIds[] = $pdo->lastInsertId();
}
$pdo->commit();
echo "Inserted " . count($subjectIds) . " subjects.\n";

// ==========================================
// 2. GENERATE TEACHERS (Nauczyciele)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Nauczyciele (id_przedmiotu, imie, nazwisko, Haslo) VALUES (?, ?, ?, 'Sala332!')");
$teachers = []; // Array to hold detailed teacher info for later use
$tutorCandidates = [];

for ($i = 0; $i < 30; $i++) {
    $subjectId = $subjectIds[array_rand($subjectIds)];
    $isMale = rand(0, 1);
    $imie = $isMale ? $imionaMale[array_rand($imionaMale)] : $imionaFemale[array_rand($imionaFemale)];
    $nazwisko = $isMale ? $nazwiskaMale[array_rand($nazwiskaMale)] : $nazwiskaFemale[array_rand($nazwiskaFemale)];
    
    $stmt->execute([$subjectId, $imie, $nazwisko]);
    $teacherId = $pdo->lastInsertId();
    
    $teachers[] = ['id' => $teacherId, 'id_przedmiotu' => $subjectId];
    $tutorCandidates[] = $teacherId;
}
$pdo->commit();
echo "Inserted 30 teachers.\n";

// ==========================================
// 3. GENERATE CLASSES (Klasy)
// ==========================================
$classNames = ['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '4A', '4B', '5A'];
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Klasy (id_wychowawcy, nazwa) VALUES (?, ?)");
$classIds = [];
shuffle($tutorCandidates);

foreach ($classNames as $index => $nazwa) {
    $id_wychowawcy = $tutorCandidates[$index]; // Unique tutor per class
    $stmt->execute([$id_wychowawcy, $nazwa]);
    $classIds[] = $pdo->lastInsertId();
}
$pdo->commit();
echo "Inserted " . count($classIds) . " classes.\n";

// ==========================================
// 4. GENERATE STUDENTS (Uczniowie)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Uczniowie (id_klasy, imie, nazwisko) VALUES (?, ?, ?)");
$studentIds = [];

foreach ($classIds as $classId) {
    $studentsInClass = rand(22, 30); // Realistic class size
    for ($i = 0; $i < $studentsInClass; $i++) {
        $isMale = rand(0, 1);
        $imie = $isMale ? $imionaMale[array_rand($imionaMale)] : $imionaFemale[array_rand($imionaFemale)];
        $nazwisko = $isMale ? $nazwiskaMale[array_rand($nazwiskaMale)] : $nazwiskaFemale[array_rand($nazwiskaFemale)];
        
        $stmt->execute([$classId, $imie, $nazwisko]);
        $studentIds[] = $pdo->lastInsertId();
    }
}
$pdo->commit();
echo "Inserted " . count($studentIds) . " students.\n";

// ==========================================
// 5. GENERATE LESSON PLAN (planLekcji)
// ==========================================
// Rule: A teacher can only teach 1 class at a time.
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO planLekcji (id_klasy, id_nauczyciela, id_przedmiotu, numer_lekcji, numer_sali, numer_dnia) VALUES (?, ?, ?, ?, ?, ?)");

$lessonPlanCounter = 0;

// numer_dnia 1-5 (Mon-Fri)
for ($day = 1; $day <= 5; $day++) {
    // Array to track which teachers are busy during which lesson on this day
    $busyTeachers = []; // Format: [numer_lekcji => [teacher_id1, teacher_id2]]
    
    foreach ($classIds as $classId) {
        $startLesson = rand(1, 3); // Class starts at 1st, 2nd, or 3rd lesson
        $totalLessons = rand(5, 8); // Class has 5 to 8 lessons a day
        
        for ($lessonNum = $startLesson; $lessonNum < ($startLesson + $totalLessons); $lessonNum++) {
            
            // Sometimes generate a gap (okienko) intentionally
            if (rand(1, 100) <= 5) continue; 
            
            // Find available teachers
            $availableTeachers = array_filter($teachers, function($t) use ($busyTeachers, $lessonNum) {
                return empty($busyTeachers[$lessonNum]) || !in_array($t['id'], $busyTeachers[$lessonNum]);
            });
            
            if (count($availableTeachers) > 0) {
                $chosenTeacher = $availableTeachers[array_rand($availableTeachers)];
                $numer_sali = rand(10, 300); // Room numbers like 10, 102, 215
                
                $stmt->execute([
                    $classId, 
                    $chosenTeacher['id'], 
                    $chosenTeacher['id_przedmiotu'], 
                    $lessonNum, 
                    $numer_sali, 
                    $day
                ]);
                $lessonPlanCounter++;
                
                // Mark teacher as busy for this hour
                $busyTeachers[$lessonNum][] = $chosenTeacher['id'];
            }
        }
    }
}
$pdo->commit();
echo "Inserted $lessonPlanCounter lesson plan entries.\n";

// ==========================================
// 6. GENERATE GRADES (Oceny)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, waga, komentarz) VALUES (?, ?, ?, ?, ?, ?, ?)");
$gradesCounter = 0;

foreach ($studentIds as $studentId) {
    $numGrades = rand(15, 30); // 15-30 grades per student total
    for ($i = 0; $i < $numGrades; $i++) {
        // Pick a random teacher & their subject to ensure integrity
        $teacher = $teachers[array_rand($teachers)];
        $ocena = $ocenyValues[array_rand($ocenyValues)];
        $waga = rand(1, 5); // Constraint check: waga >= 1 && waga <= 5
        $komentarz = $gradeComments[array_rand($gradeComments)];
        
        // Random date in the last 6 months
        $timestamp = time() - rand(0, 180 * 24 * 60 * 60);
        $data = date("Y-m-d H:i:s", $timestamp);
        
        $stmt->execute([
            $studentId,
            $teacher['id_przedmiotu'],
            $teacher['id'],
            $data,
            $ocena,
            $waga,
            $komentarz
        ]);
        $gradesCounter++;
    }
}
$pdo->commit();
echo "Inserted $gradesCounter grades.\n";

// ==========================================
// 7. GENERATE ATTENDANCE (Frekwencja)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO Frekwencja (id_ucznia, id_przedmiotu, data, typ) VALUES (?, ?, ?, ?)");
$attendanceCounter = 0;

foreach ($studentIds as $studentId) {
    $numRecords = rand(20, 50); // Sample attendance records
    for ($i = 0; $i < $numRecords; $i++) {
        $subjectId = $subjectIds[array_rand($subjectIds)];
        $typ = $attendanceTypes[array_rand($attendanceTypes)];
        $timestamp = time() - rand(0, 30 * 24 * 60 * 60); // Last 30 days
        $data = date("Y-m-d H:i:s", $timestamp);
        
        $stmt->execute([$studentId, $subjectId, $data, $typ]);
        $attendanceCounter++;
    }
}
$pdo->commit();
echo "Inserted $attendanceCounter attendance records.\n";

// ==========================================
// 8. GENERATE EVENTS (Terminarz)
// ==========================================
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO terminarz (id_klasy, id_nauczyciela, numer_dnia, typ_wydarzenia, zakres_start, zakres_end, data_dodania) VALUES (?, ?, ?, ?, ?, ?, ?)");
$eventsCounter = 0;

for ($i = 0; $i < 150; $i++) { // 150 school events
    $classId = $classIds[array_rand($classIds)];
    $teacherId = $teachers[array_rand($teachers)]['id'];
    $numerDnia = rand(1, 5); // Mon-Fri
    $typ = $eventTypes[array_rand($eventTypes)];
    
    // Future or past dates
    $startTimestamp = time() + rand(-30 * 24 * 60 * 60, 30 * 24 * 60 * 60);
    $endTimestamp = $startTimestamp + (45 * 60); // 45 minutes duration
    $addedTimestamp = $startTimestamp - (7 * 24 * 60 * 60); // added a week before
    
    $zakres_start = date("Y-m-d H:i:s", $startTimestamp);
    $zakres_end = date("Y-m-d H:i:s", $endTimestamp);
    $data_dodania = date("Y-m-d H:i:s", $addedTimestamp);
    
    $stmt->execute([$classId, $teacherId, $numerDnia, $typ, $zakres_start, $zakres_end, $data_dodania]);
    $eventsCounter++;
}
$pdo->commit();
echo "Inserted $eventsCounter events.\n";

echo "\n=========================================\n";
echo "SUCCESS! Database is fully populated.\n";
echo "=========================================\n";

?>