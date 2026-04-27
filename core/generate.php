<?php
// ==========================================
// DATABASE CONFIGURATION
// ==========================================
$host = '127.0.0.1';
$db   = 'bibrus'; // Change this to your actual database name
$user = 'root';   // Change this to your database username
$pass = '';       // Change this to your database password
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
$firstNamesMale = ['Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Michał', 'Marcin', 'Jakub', 'Adam', 'Stanisław', 'Maciej', 'Kamil'];
$firstNamesFemale = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Krystyna', 'Barbara', 'Ewa', 'Elżbieta', 'Zofia', 'Julia', 'Maja'];
$lastNames = ['Nowak', 'Kowalski', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski', 'Jankowski', 'Mazur', 'Wojciechowski'];
$subjects = ['Matematyka', 'Język polski', 'Język angielski', 'Fizyka', 'Chemia', 'Biologia', 'Geografia', 'Historia', 'Wychowanie fizyczne', 'Informatyka', 'Muzyka', 'Plastyka'];
$classNames = ['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '3C', '4A', '4B', '4C'];
$days = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek'];
$attendanceTypes = ['Obecny', 'Usprawiedliwiony', 'Nieobecny', 'Zwolniony', 'Spóźniony'];
$gradeValues = [1, 1.5, 2, 2.5, 1.75, 3, 3.5, 2.75, 4, 4.5, 3.75, 5, 5.5, 4.75, 6, 5.75];
$comments = ['Sprawdzian', 'Kartkówka', 'Odpowiedź ustna', 'Praca domowa', 'Aktywność', 'Projekt', 'Brak zadania', 'Złe zachowanie'];

// Helper function to get random elements
function getRandom($array) { return $array[array_rand($array)]; }
function generateDate($daysBack = 180) {
    $timestamp = time() - rand(0, $daysBack * 24 * 60 * 60);
    return date("Y-m-d H:i:s", $timestamp);
}

echo "Starting data generation...\n";

// ==========================================
// 1. GENERATE PRZEDMIOTY (SUBJECTS)
// ==========================================
echo "Generating Przedmioty...\n";
$stmt = $pdo->prepare("INSERT INTO Przedmioty (nazwa) VALUES (?)");
foreach ($subjects as $subject) {
    // Ignore if exists to prevent duplicates on rerun
    $pdo->exec("INSERT IGNORE INTO Przedmioty (nazwa) VALUES ('$subject')");
}
$subjectIds = $pdo->query("SELECT id_przedmiotu FROM Przedmioty")->fetchAll(PDO::FETCH_COLUMN);

// ==========================================
// 2. GENERATE NAUCZYCIELE (TEACHERS)
// ==========================================
echo "Generating Nauczyciele...\n";
$stmt = $pdo->prepare("INSERT INTO Nauczyciele (imie, nazwisko, id_przedmiotu, Haslo) VALUES (?, ?, ?, 'Sala332!')");
$pdo->beginTransaction();
for ($i = 0; $i < 20; $i++) {
    $isMale = rand(0, 1);
    $firstName = $isMale ? getRandom($firstNamesMale) : getRandom($firstNamesFemale);
    $lastName = getRandom($lastNames) . ($isMale ? '' : 'a'); // basic Polish feminine surname ending
    $stmt->execute([$firstName, $lastName, getRandom($subjectIds)]);
}
$pdo->commit();
$teacherIds = $pdo->query("SELECT id_nauczyciela FROM Nauczyciele")->fetchAll(PDO::FETCH_COLUMN);

// ==========================================
// 3. GENERATE KLASY (CLASSES)
// ==========================================
echo "Generating Klasy...\n";
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

// ==========================================
// 4. GENERATE UCZNIOWIE (STUDENTS)
// ==========================================
echo "Generating Uczniowie (approx " . (count($classIds) * 25) . " students)...\n";
$stmt = $pdo->prepare("INSERT INTO Uczniowie (id_klasy, imie, nazwisko) VALUES (?, ?, ?)");
$pdo->beginTransaction();
foreach ($classIds as $classId) {
    $studentCount = rand(20, 30); // 20-30 students per class
    for ($i = 0; $i < $studentCount; $i++) {
        $isMale = rand(0, 1);
        $firstName = $isMale ? getRandom($firstNamesMale) : getRandom($firstNamesFemale);
        $lastName = getRandom($lastNames) . ($isMale ? '' : 'a');
        $stmt->execute([$classId, $firstName, $lastName]);
    }
}
$pdo->commit();
$studentIds = $pdo->query("SELECT id_ucznia, id_klasy FROM Uczniowie")->fetchAll();

// ==========================================
// 5. GENERATE PLAN LEKCJI (SCHEDULE)
// ==========================================
echo "Generating Plan Lekcji...\n";
$stmt = $pdo->prepare("INSERT INTO planLekcji (id_klasy, id_nauczyciela, id_przedmiotu, numer_lekcji, numer_sali, dzien) VALUES (?, ?, ?, ?, ?, ?)");
$pdo->beginTransaction();
$teachersWithSubjects = $pdo->query("SELECT id_nauczyciela, id_przedmiotu FROM Nauczyciele")->fetchAll();

foreach ($classIds as $classId) {
    foreach ($days as $day) {
        $lessonsCount = rand(5, 8); // 5 to 8 lessons a day
        for ($lessonNum = 1; $lessonNum <= $lessonsCount; $lessonNum++) {
            $teacher = getRandom($teachersWithSubjects);
            $room = rand(100, 315); // Random room numbers
            $stmt->execute([$classId, $teacher['id_nauczyciela'], $teacher['id_przedmiotu'], $lessonNum, $room, $day]);
        }
    }
}
$pdo->commit();

// ==========================================
// 6. GENERATE OCENY (GRADES)
// ==========================================
echo "Generating Oceny (This might take a moment)...\n";
$stmt = $pdo->prepare("INSERT INTO Oceny (id_ucznia, id_przedmiotu, id_nauczyciela, data, ocena, waga, komentarz) VALUES (?, ?, ?, ?, ?, ?, ?)");
$pdo->beginTransaction();
for ($i = 0; $i < 3000; $i++) { // Generating 3000 random grades
    $student = getRandom($studentIds);
    $teacher = getRandom($teachersWithSubjects);
    $grade = getRandom($gradeValues);
    $weight = rand(1, 5); // Must be between 1 and 5 per your CHECK constraint
    $comment = (rand(1,10) > 3) ? getRandom($comments) : ''; // 70% chance of a comment
    $date = generateDate();
    
    $stmt->execute([$student['id_ucznia'], $teacher['id_przedmiotu'], $teacher['id_nauczyciela'], $date, $grade, $weight, $comment]);
}
$pdo->commit();

// ==========================================
// 7. GENERATE FREKWENCJA (ATTENDANCE)
// ==========================================
echo "Generating Frekwencja...\n";
$stmt = $pdo->prepare("INSERT INTO Frekwencja (id_ucznia, id_przedmiotu, data, typ) VALUES (?, ?, ?, ?)");
$pdo->beginTransaction();
for ($i = 0; $i < 5000; $i++) { // Generating 5000 attendance records
    $student = getRandom($studentIds);
    $subjectId = getRandom($subjectIds);
    $date = generateDate();
    
    // Make 'Obecny' (Present) much more likely (80% chance)
    $randNum = rand(1, 100);
    if ($randNum <= 80) {
        $type = 'Obecny';
    } elseif ($randNum <= 85) {
        $type = 'Usprawiedliwiony';
    } elseif ($randNum <= 90) {
        $type = 'Nieobecny';
    } elseif ($randNum <= 95) {
        $type = 'Spóźniony';
    } else {
        $type = 'Zwolniony';
    }

    $stmt->execute([$student['id_ucznia'], $subjectId, $date, $type]);
}
$pdo->commit();

echo "\nSUCCESS! Data generation is complete.\n";
?>