<?php
// Prevent timeout for large data generation
set_time_limit(0);

// ==========================================
// DATABASE CONFIGURATION
// ==========================================
$host = '127.0.0.1';
$db   = 'bibrus'; // Make sure this matches your DB name
$user = 'root';   
$pass = '';       
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==========================================
// MOCK DATA ARRAYS
// ==========================================
$firstNamesMale = ['Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Michał', 'Marcin', 'Jakub', 'Adam', 'Stanisław', 'Maciej', 'Kamil', 'Kacper', 'Szymon'];
$firstNamesFemale = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Krystyna', 'Barbara', 'Ewa', 'Elżbieta', 'Zofia', 'Julia', 'Maja', 'Zuzanna', 'Hanna'];
$lastNames = ['Nowak', 'Kowalski', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski', 'Jankowski', 'Mazur', 'Wojciechowski'];
$subjects = ['Matematyka', 'Język polski', 'Język angielski', 'Fizyka', 'Chemia', 'Biologia', 'Geografia', 'Historia', 'Wychowanie fizyczne', 'Informatyka', 'Muzyka', 'Plastyka'];
$classNames = ['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '3C', '4A', '4B', '4C'];

// Days 1-5 correspond to Poniedziałek - Piątek in your dnitygodnia table
$schoolDays = [1, 2, 3, 4, 5]; 

$gradeValues = [1, 1.5, 2, 2.5, 1.75, 3, 3.5, 2.75, 4, 4.5, 3.75, 5, 5.5, 4.75, 6, 5.75];
$gradeComments = ['Sprawdzian', 'Kartkówka', 'Odpowiedź ustna', 'Praca domowa', 'Aktywność', 'Projekt', 'Brak zadania', 'Złe zachowanie'];

$terminarzTypes = ['Sprawdzian', 'Kartkówka', 'Nieobecność', 'Zastępstwo', 'Informacja', 'Inne', 'Wywiadówka'];
$terminarzDescriptions = [
    'Sprawdzian' => ['Sprawdzian z rozdziału 2', 'Test podsumowujący semestr', 'Sprawdzian wiedzy ogólnej'],
    'Kartkówka' => ['Szybka kartkówka ze słówek', 'Kartkówka z 3 ostatnich lekcji', 'Niezapowiedziana kartkówka'],
    'Nieobecność' => ['L4 - zwolnienie lekarskie', 'Wyjazd na szkolenie', 'Opieka nad dzieckiem', 'Urlop okolicznościowy'],
    'Zastępstwo' => ['Zastępstwo z inną klasą', 'Zajęcia świetlicowe w ramach zastępstwa', 'Projekcja filmu dokumentalnego'],
    'Informacja' => ['Przynieść przybory geometryczne', 'Zbiórka pieniędzy na radę rodziców', 'Termin oddania projektów'],
    'Inne' => ['Wycieczka szkolna', 'Apel z okazji Święta Niepodległości', 'Dzień Sportu', 'Próbna ewakuacja'],
    'Wywiadówka' => ['Spotkanie z rodzicami', 'Omówienie wyników po pierwszym semestrze', 'Dzień Otwarty']
];

// ==========================================
// HELPER FUNCTIONS
// ==========================================
function getRandom($array) { return $array[array_rand($array)]; }

// Advanced date generator prioritizing the 2025-2026 school year
function generateTimestamp() {
    $rand = rand(1, 100);
    if ($rand <= 85) {
        // 85% chance: School Year (Sept 2025 - June 2026)
        $start = strtotime('2025-09-01 08:00:00');
        $end = strtotime('2026-06-30 15:00:00');
    } elseif ($rand <= 92) {
        // 7% chance: Outlier Past (e.g. 2023 - mid 2025)
        $start = strtotime('2023-01-01 08:00:00');
        $end = strtotime('2025-08-31 23:59:59');
    } else {
        // 8% chance: Outlier Future (July 2026 - Dec 2027)
        $start = strtotime('2026-07-01 08:00:00');
        $end = strtotime('2027-12-31 23:59:59');
    }
    return rand($start, $end);
}

echo "Starting massive data generation...\n";

// 1. PRZEDMIOTY
echo "1/8 Generating Przedmioty...\n";
foreach ($subjects as $subject) {
    // IGNORE prevents error if you run the script twice
    $pdo->exec("INSERT IGNORE INTO Przedmioty (nazwa) VALUES ('$subject')");
}
$subjectIds = $pdo->query("SELECT id_przedmiotu FROM Przedmioty")->fetchAll(PDO::FETCH_COLUMN);

// 2. NAUCZYCIELE
echo "2/8 Generating Nauczyciele...\n";
$stmt = $pdo->prepare("INSERT INTO Nauczyciele (imie, nazwisko, id_przedmiotu, Haslo) VALUES (?, ?, ?, 'Sala332!')");
$pdo->beginTransaction();
for ($i = 0; $i < 25; $i++) {
    $isMale = rand(0, 1);
    $firstName = $isMale ? getRandom($firstNamesMale) : getRandom($firstNamesFemale);
    $lastName = getRandom($lastNames) . ($isMale ? '' : 'a');
    $stmt->execute([$firstName, $lastName, getRandom($subjectIds)]);
}
$pdo->commit();
$teacherIds = $pdo->query("SELECT id_nauczyciela FROM Nauczyciele")->fetchAll(PDO::FETCH_COLUMN);
$teachersWithSubjects = $pdo->query("SELECT id_nauczyciela, id_przedmiotu FROM Nauczyciele")->fetchAll();

// 3. KLASY
echo "3/8 Generating Klasy...\n";
$stmt = $pdo->prepare("INSERT INTO Klasy (id_wychowawcy, nazwa) VALUES (?, ?)");
$pdo->beginTransaction();
$availableTeachers = $teacherIds;
shuffle($availableTeachers);
foreach ($classNames as $index => $className) {
    $tutorId = $availableTeachers[$index % count($availableTeachers)];
    $stmt->execute([$tutorId, $className]);
}
$pdo->commit();
$classIds = $pdo->query("SELECT id_klasy FROM Klasy")->fetchAll(PDO::FETCH_COLUMN);

// 4. UCZNIOWIE
echo "4/8 Generating Uczniowie (approx " . (count($classIds) * 25) . " students)...\n";
$stmt = $pdo->prepare("INSERT INTO Uczniowie (id_klasy, imie, nazwisko) VALUES (?, ?, ?)");
$pdo->beginTransaction();
foreach ($classIds as $classId) {
    $studentCount = rand(20, 30);
    for ($i = 0; $i < $studentCount; $i++) {
        $isMale = rand(0, 1);
        $firstName = $isMale ? getRandom($firstNamesMale) : getRandom($firstNamesFemale);
        $lastName = getRandom($lastNames) . ($isMale ? '' : 'a');
        $stmt->execute([$classId, $firstName, $lastName]);
    }
}
$pdo->commit();
$studentIds = $pdo->query("SELECT id_ucznia, id_klasy FROM Uczniowie")->fetchAll();

// 5. PLAN LEKCJI (FIXED!)
echo "5/8 Generating Plan Lekcji...\n";
$stmt = $pdo->prepare("INSERT INTO planLekcji (id_klasy, id_nauczyciela, id_przedmiotu, numer_lekcji, numer_sali, numer_dnia) VALUES (?, ?, ?, ?, ?, ?)");
$pdo->beginTransaction();
foreach ($classIds as $classId) {
    foreach ($schoolDays as $numer_dnia) { // Loops 1 to 5 (Mon to Fri)
        $lessonsCount = rand(5, 8); // Max 8 lessons a day to respect your LekcjeDictionary max bounds realistically
        for ($lessonNum = 1; $lessonNum <= $lessonsCount; $lessonNum++) {
            $teacher = getRandom($teachersWithSubjects);
            $room = rand(100, 315);
            $stmt->execute([$classId, $teacher['id_nauczyciela'], $teacher['id_przedmiotu'], $lessonNum, $room, $numer_dnia]);
        }
    }
}
$pdo->commit();

// 6. OCENY
echo "6/8 Generating Oceny (3000 records)...\n";
$stmt = $pdo->prepare("INSERT INTO Oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, waga, komentarz) VALUES (?, ?, ?, ?, ?, ?, ?)");
$pdo->beginTransaction();
for ($i = 0; $i < 3000; $i++) {
    $student = getRandom($studentIds);
    $teacher = getRandom($teachersWithSubjects);
    $grade = getRandom($gradeValues);
    $weight = rand(1, 5); // Matches your CHECK (waga >= 1 && waga <= 5)
    $comment = (rand(1,10) > 3) ? getRandom($gradeComments) : '';
    $date = date("Y-m-d H:i:s", generateTimestamp());
    
    $stmt->execute([$student['id_ucznia'], $teacher['id_przedmiotu'], $teacher['id_nauczyciela'], $date, $grade, $weight, $comment]);
}
$pdo->commit();

// 7. FREKWENCJA
echo "7/8 Generating Frekwencja (5000 records)...\n";
$stmt = $pdo->prepare("INSERT INTO Frekwencja (id_ucznia, id_przedmiotu, data, typ) VALUES (?, ?, ?, ?)");
$pdo->beginTransaction();
for ($i = 0; $i < 5000; $i++) {
    $student = getRandom($studentIds);
    $subjectId = getRandom($subjectIds);
    $date = date("Y-m-d H:i:s", generateTimestamp());
    
    $randNum = rand(1, 100);
    if ($randNum <= 80) $type = 'Obecny';
    elseif ($randNum <= 85) $type = 'Usprawiedliwiony';
    elseif ($randNum <= 90) $type = 'Nieobecny';
    elseif ($randNum <= 95) $type = 'Spóźniony';
    else $type = 'Zwolniony';

    $stmt->execute([$student['id_ucznia'], $subjectId, $date, $type]);
}
$pdo->commit();

// 8. TERMINARZ
echo "8/8 Generating Terminarz (1000 events with dynamic durations)...\n";
$stmt = $pdo->prepare("INSERT INTO terminarz (id_klasy, id_nauczyciela, id_przedmiotu, typ_wydarzenia, opis, zakres_start, zakres_end, data_dodania) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$pdo->beginTransaction();

for ($i = 0; $i < 1000; $i++) {
    $classId = getRandom($classIds);
    $teacher = getRandom($teachersWithSubjects);
    $type = getRandom($terminarzTypes);
    $desc = getRandom($terminarzDescriptions[$type]);
    
    $startTs = generateTimestamp();
    $endTs = $startTs;

    // Durations logic
    switch ($type) {
        case 'Sprawdzian':
        case 'Kartkówka':
            $endTs = $startTs + rand(30*60, 45*60); // 30 to 45 mins
            break;
        case 'Zastępstwo':
            $endTs = $startTs + rand(45*60, 120*60); // 45 mins to 2 hrs
            break;
        case 'Wywiadówka':
            $endTs = $startTs + rand(60*60, 180*60); // 1 to 3 hrs
            break;
        case 'Nieobecność':
            $endTs = $startTs + rand(1*24*60*60, 60*24*60*60); // 1 day to 2 months
            break;
        case 'Informacja':
        case 'Inne':
            $randDuration = rand(1, 100);
            if ($randDuration <= 50) {
                $endTs = $startTs + rand(60*60, 24*60*60); // Hours to 1 day
            } elseif ($randDuration <= 80) {
                $endTs = $startTs + rand(2*24*60*60, 14*24*60*60); // 2 days to 2 weeks
            } else {
                $endTs = $startTs + rand(30*24*60*60, 150*24*60*60); // 1 to 5 months
            }
            break;
    }

    $addedTs = $startTs - rand(1*24*60*60, 14*24*60*60);

    $startStr = date("Y-m-d H:i:s", $startTs);
    $endStr = date("Y-m-d H:i:s", $endTs);
    $addedStr = date("Y-m-d H:i:s", $addedTs);

    $stmt->execute([
        $classId, 
        $teacher['id_nauczyciela'], 
        $teacher['id_przedmiotu'], 
        $type, 
        $desc, 
        $startStr, 
        $endStr, 
        $addedStr
    ]);
}
$pdo->commit();

echo "\nSUCCESS! All dummy data, including the updated Plan Lekcji and Terminarz events, has been successfully generated.\n";
?>