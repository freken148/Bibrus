# Bibrus — Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Core Files](#core-files)
5. [Function Libraries](#function-libraries)
6. [Page Scripts](#page-scripts)
7. [Session & POST Conventions](#session--post-conventions)
8. [Glossary](#glossary)

---

## Project Overview

**Bibrus** is a PHP-based school management system (an electronic gradebook) inspired by Librus. It supports the following features:

- Teacher authentication
- Attendance tracking (frekwencja) per class and per student
- Grade management (oceny) with weighted averages, comments, and per-student or per-class entry
- Student remarks (uwagi) categorized as positive or negative
- Calendar/scheduler (terminarz) for events (tests, absences, substitutions, meetings, etc.)
- Lesson plan (plan_lekcji)

The application is built on plain PHP with MySQL via the `mysqli` extension. No frameworks or external libraries are used. CSS is provided by a single `style.css` file. The UI is rendered as server-side HTML with a sprinkling of vanilla JavaScript for form interactions.

The project files are organized as follows:

- `*.php` — Page entry points and shared includes
- `core/` — Shared infrastructure (DB connection, sessions, head includes, SQL schema)
- `functions/` — Page-specific helper functions
- `core/bibrus.sql` — Database schema and seed data

---

## Architecture

### Request flow
1. The browser sends a request to one of the page scripts (`frekwencja.php`, `oceny.php`, `terminarz.php`, etc.).
2. The page script `require`s `core/idk.php`, which:
   - Handles the `glowna` button (redirects to `glowna.php` if pressed)
   - Starts the PHP session
   - Connects to the MySQL database via `core/db.php` (assigns the connection to `$conn`)
   - Includes `core/SessionCheck.php` to ensure the user is logged in
3. The page script may `require` other helpers, then renders the HTML.
4. Form actions POST to the same page or a related page, mutating data via SQL `INSERT`/`DELETE` queries, then redirecting (e.g., `header('Location: ...')`) to follow the Post-Redirect-Get pattern and prevent double-submission.

### State
- All state is stored in the MySQL database.
- User identity is stored in `$_SESSION` (`id_nauczyciela`).
- Form selections (class, month, year) are mirrored into `$_SESSION` so they persist across redirects (this is the basis of the "selects stay selected" feature in `terminarz.php`).

### Conventions
- The `idk.php` file is included by every page; it bootstraps session, DB, and auth.
- Helper functions live in `functions/*.php` and `core/*.php` and are typically not in classes — this is procedural PHP.
- The shared form toolbar (`core/frekOcenyUni.php`) is included by every grade/attendance/remarks page.
- Each page's `*.php` file is the controller; the corresponding `functions/*.php` is the view helper library.

---

## Database Schema

The SQL schema is defined in `core/bibrus.sql`. It is a script intended to be run once to create the database. Note: the schema uses mixed-case identifiers (e.g., `Przedmioty`, `Nauczyciele`, `Klasy`, `Uczniowie`, `OcenyDictionary`, `Oceny`, `Frekwencja`, `LekcjeDictionary`, `dnitygodnia`, `planLekcji`, `terminarz`, `Uwagi`), but in queries throughout the codebase these are referenced in **lowercase** (e.g., `nauczyciele`, `klasy`, `uczniowie`, `oceny`, `frekwencja`, `terminarz`, `uwagi`, `przedmioty`, `planlekcji`). MySQL on Windows is case-insensitive by default with `lower_case_table_names=1`, which is why this works.

### Tables

#### Przedmioty (Subjects)
- `id_przedmiotu INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `nazwa VARCHAR(50)` — Subject name (e.g., "Math", "Polish").

#### Nauczyciele (Teachers)
- `id_nauczyciela INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_przedmiotu INT` — Foreign key to `Przedmioty`. **One teacher teaches exactly one subject** in this model. (Real-world teachers usually teach multiple subjects; this simplification lets the grade/attendance pages automatically know which subject a teacher is logging grades for.)
- `imie VARCHAR(50)`, `nazwisko VARCHAR(50)` — First and last name.
- `Haslo VARCHAR(100)` — Password. After table creation, the `UPDATE nauczyciele SET Haslo = 'Sala332!';` line sets every teacher's password to the same default ("Sala332!"). This is a teaching/demo system, not a production-grade auth system.

#### Klasy (Classes)
- `id_klasy INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_wychowawcy INT` — Foreign key to `Nauczyciele`. Identifies the homeroom teacher (`wychowawca`).
- `nazwa VARCHAR(10)` — Class name (e.g., "1A", "3B").

#### Uczniowie (Students)
- `id_ucznia INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_klasy INT` — Foreign key to `Klasy`. Each student belongs to exactly one class.
- `imie VARCHAR(50)`, `nazwisko VARCHAR(50)` — First and last name.

#### OcenyDictionary (Grade Dictionary)
- `wartosc FLOAT PRIMARY KEY` — The numeric value (e.g., 1, 1.5, 2, 2.5, 2.75, 3, 3.5, 3.75, 4, 4.5, 4.75, 5, 5.5, 5.75, 6).
- `ocena VARCHAR(5)` — The display string (e.g., "1", "1+", "2-", "5+"). This lets the system support Polish plus/minus grades (e.g., "5+" → 5.5) while keeping the underlying data numeric.

The `INSERT INTO OcenyDictionary VALUES ...` line populates the dictionary with the standard Polish grade scale.

#### Oceny (Grades)
- `id_oceny INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_ucznia INT` — Foreign key to `Uczniowie`.
- `id_przedmiotu INT` — Foreign key to `Przedmioty`.
- `id_nauczyciela INT` — Foreign key to `Nauczyciele` (the teacher who assigned the grade).
- `data DATETIME(0)` — Timestamp of the grade (set to `NOW()` at insert time).
- `ocena FLOAT` — Foreign key to `OcenyDictionary.wartosc`. (MySQL allows this as a foreign key even though `wartosc` is a FLOAT.)
- `waga INT CHECK (waga >= 1 && waga <= 5)` — Grade weight (1–5). Used in weighted-average calculations: `SUM(ocena * waga) / SUM(waga)`.
- `komentarz VARCHAR(1000)` — Optional comment.
- Foreign keys: `id_ucznia`, `id_przedmiotu`, `id_nauczyciela`, `ocena` (to `OcenyDictionary.wartosc`).

#### Frekwencja (Attendance)
- `id_obecnosci INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_ucznia INT` — Foreign key.
- `id_przedmiotu INT` — Foreign key.
- `id_nauczyciela INT` — Foreign key.
- `data DATETIME(0)` — Timestamp.
- `typ ENUM('Obecny', 'Usprawiedliwiony', 'Nieobecny', 'Zwolniony', 'Spóźniony') DEFAULT 'Obecny'` — Attendance status.
  - **Obecny** = Present
  - **Usprawiedliwiony** = Excused absence
  - **Nieobecny** = Unexcused absence
  - **Zwolniony** = Excused (e.g., released early)
  - **Spóźniony** = Late

#### LekcjeDictionary (Lesson Time Dictionary)
- `numer_lekcji INT PRIMARY KEY AUTO_INCREMENT` — Lesson number (1st period, 2nd period, etc.).
- `godzina_lekcji TIME(0)` — The start time of that period.

Populated with 14 standard school periods, including a longer mid-morning break.

#### dnitygodnia (Days of Week)
- `numer_dnia INT PRIMARY KEY AUTO_INCREMENT` — Day number (1=Mon..7=Sun).
- `dzien ENUM('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela')` — Day name in Polish.

#### planLekcji (Lesson Plan)
- `id_lekcji INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_klasy INT` — Foreign key.
- `id_nauczyciela INT` — Foreign key.
- `id_przedmiotu INT` — Foreign key.
- `numer_lekcji INT` — Foreign key to `LekcjeDictionary` (which period).
- `numer_sali INT` — Classroom number.
- `numer_dnia INT` — Foreign key to `dnitygodnia` (which day of the week).

#### terminarz (Schedule / Events)
- `id_wydarzenia INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_klasy INT` — Foreign key.
- `id_nauczyciela INT` — Foreign key.
- `id_przedmiotu INT` — Foreign key.
- `typ_wydarzenia ENUM('Sprawdzian', 'Kartkówka', 'Nieobecność', 'Zastępstwo', 'Informacja', 'Inne', 'Wywiadówka')` — Event type. Sprawdzian = test, Kartkówka = quiz, Nieobecność = absence, Zastępstwo = substitution, Informacja = info, Inne = other, Wywiadówka = parent-teacher meeting.
- `opis VARCHAR(1000)` — Description.
- `zakres_start DATETIME(0)`, `zakres_end DATETIME(0)` — Start and end timestamps. Multi-day events use a date range.
- `data_dodania DATETIME(0)` — When the event was added.

#### Uwagi (Remarks)
- `id_uwagi INT PRIMARY KEY AUTO_INCREMENT` — Surrogate primary key.
- `id_ucznia INT` — Foreign key.
- `id_nauczyciela INT` — Foreign key.
- `typ ENUM('Pozytywna', 'Negatywna')` — Positive or negative remark.
- `opis VARCHAR(1000)` — Description.
- `data DATETIME(0)` — Timestamp.

---

## Core Files

### `core/idk.php`

The "I Don't Know" file is the universal bootstrap for every page. It handles the `glowna` button (which sends the user back to the main page), starts the session, connects to the database, and includes the session check.

```php
<?php
    isset($_POST['glowna']) ? header('Location: glowna.php') : NULL;
    session_start();
    require "core/db.php";
    require "core/SessionCheck.php";
?>
```

- `isset($_POST['glowna']) ? header('Location: glowna.php') : NULL;` — If the "Do głównej" (to main) button was pressed, redirect to `glowna.php`. The `exit` is missing here but PHP's `header()` is called before any output is emitted, so it works.
- `session_start();` — Begin or resume the session. Must be called before any HTML output. The session is used to store the logged-in teacher's id (`id_nauczyciela`) and form-select defaults.
- `require "core/db.php";` — Includes the database connection.
- `require "core/SessionCheck.php";` — Includes the authentication check.

### `core/db.php`

Establishes a connection to the MySQL database and exposes it as the global variable `$conn`.

```php
<?php
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'bibrus';
    $conn = new mysqli($host, $user, $pass, $dbname);
?>
```

- The variables are hardcoded for the local XAMPP setup (no password, `bibrus` database).
- The connection is assigned to `$conn`, which is later used in helper functions via the `global $conn;` declaration.
- There is no error handling — if the connection fails, a `mysqli` exception/error will be raised when queries are run. For a production system, you'd want `if ($conn->connect_error) die(...)` or similar.

### `core/SessionCheck.php`

Verifies that the user is logged in. If not, redirects to the login page.

```php
<?php
    if (!isset($_SESSION['id_nauczyciela'])) {
        header('Location: logowanie.php');
        exit;
    }
?>
```

- The `id_nauczyciela` session key is set by `logowanie.php` after successful authentication.
- `exit;` is critical here to stop further script execution after the redirect.

### `core/head.php`

Renders the common `<head>` content (CSS, viewport, charset) and is included by every page. Since it's included from within the `<head>` of each page, it can only contain `<meta>`/`<link>`/`<title>` elements.

This file likely contains:
- A `<meta charset="UTF-8">` (or relies on the page's own charset declaration)
- A `<meta name="viewport">`
- A `<link>` to `style.css`

### `core/frekOcenyUni.php`

The "Frekwencja-Oceny Universal" toolbar — included by `frekwencja.php`, `oceny.php`, and `uwagi.php`. Renders the class/student selector toolbar that is shared by all three.

- Renders a `<div class="toolbarFrekwencja">` containing:
  - Two `<label>` radio buttons (`KlasaUczen`): one with value `klasa`, one with value `uczen`. Both have `onchange="this.form.submit()"` so changing the radio auto-submits the form.
  - A `<select name="wybrana_klasa">` populated by `SelectKlasy()`.
  - A `<select name="wybrany_uczen">` populated by `SelectUcznie()`.
  - A checkbox `<input type="checkbox" name="WedlugPrzedmiotow" value="WedlugPrzedmiotowChecked">` with label "Według przedmiotów" (by subject), also auto-submitting.
  - A `<button name="glowna">` to return to the main page.
- All three pages then dispatch on `$_POST['KlasaUczen']` (klasa/uczen) and the checkbox state to render different tables.

### `core/SelectUczenKlasa.php`

Provides global state and helper functions for selecting classes and students. Required by `frekwencja.php`, `oceny.php`, and `uwagi.php` (and indirectly by `terminarz.php` for the class dropdown).

#### Globals
- `$warunek` — A SQL WHERE-clause fragment that filters by either `klasy.id_klasy = X` (class mode) or `uczniowie.id_ucznia = X` (student mode). Used by `Wyszukaj()` in each helper file.
- `$selected_object` — The currently selected class id.
- `$fetchKlasa`, `$fetchUczen` — Integer ids of the currently selected class and student.

#### `Init()`
Called by the page after `$_POST` is set up. It:
1. Reads `wybrana_klasa` and `wybrany_uczen` from `$_POST` (with fallback to 1).
2. Verifies the chosen student actually belongs to the chosen class. If not (e.g., the user changed class after picking a student from a different class), it falls back to the first student of the new class. This is the "bug fix when switching class while in uczen mode" behavior.
3. Sets `$warunek` to either `klasy.id_klasy = $fetchKlasa` (class mode) or `uczniowie.id_ucznia = $fetchUczen` (student mode) based on `$_POST['KlasaUczen']`.

#### `SelectKlasy()`
Renders `<option>` elements for every row in `klasy`, ordered by name. The option matching `$_SESSION['klasaDefault']` is marked `selected`. As a side effect, it also sets `$_SESSION['klasaDefault']` from `$_POST['wybrana_klasa']` so it can be reused across pages.

#### `SelectUcznie()`
Renders `<option>` elements for students in the currently selected class, ordered by surname then first name. Each option's text is `"{first_name} {last_name} {class_name}"`. The option matching `$_SESSION['uczenDefault']` is marked `selected`.

#### `BugFixWhenClickUczenAndSelectKlasa()`
Called by views that operate on a specific student (e.g., `UczenOceny()`, `UczenUwagi()`). It re-runs the same fallback as `Init()` to ensure that after switching class, a valid student is in scope (re-selects the first student of the new class if the previously selected student doesn't belong to it).

---

## Function Libraries

### `functions/frekwencja_functions.php` — Attendance views

#### `GeneralKlasaInfo()`
Renders a summary table for the selected class:
- Homeroom teacher (`wychowawca`) name
- Class name
- Number of distinct students
- Class-wide attendance rate: `AVG(typ IN ('Obecny', 'Zwolniony', 'Spóźniony')) * 100` — counts a student "present" if the attendance type is one of the three "good" states. The `* 100` converts the 0..1 average to a percentage.

The query is intentionally simple (no `GROUP BY`) because it returns one row per class.

#### `Wyszukaj()`
The main search table. Dispatches on `$_POST['KlasaUczen']`:
- If `klasa`: shows a numbered list of students with their attendance percentages and a row of radio buttons per student (one for each of the 5 attendance types).
- If `uczen`: only shows the selected student (or single-student row).

The percentage is computed as `AVG(frekwencja.typ IN ('Obecny', 'Zwolniony', 'Spóźniony')) * 100` over all attendance records for that student. `LEFT JOIN frekwencja` ensures students with no attendance records still appear (their percentage will be `NULL`, rendered as the word "Brak wpisów o frekwencji" via the empty-result branch).

#### `UczenOceny()` (misnamed — this is the per-student attendance list)
Renders a list of attendance entries for one student, including the subject, timestamp, and type. A "Usuń" (delete) button is shown only for records where the current teacher is the author (so teachers can only delete their own entries). A "dodaj" button is rendered above the table to add a new entry — clicking it sets the form context to "add a new attendance row" via the `dodajPrzycisk` POST handler.

Despite the name (which is a relic of the project history), this is the per-student attendance view, not a grades view. The grades equivalent is `UczenOceny()` in `functions/oceny_functions.php`.

#### `WedlugPrzedmiotow_Klasa()`
When the "Według przedmiotów" checkbox is on and class mode is selected, this renders a table with one row per subject showing the average attendance percentage for that class+subject.

#### `WedlugPrzedmiotow_Uczen()`
Same as above but for one student — one row per subject with the average attendance percentage for that student+subject.

#### `DodajOceny()` (misnamed — this inserts attendance)
Triggered when `$_POST['dodajPrzycisk']` is set. Iterates `$_POST['frek']` (an array of `id_ucznia => typ`) and inserts a `frekwencja` row for each. The subject is derived from the logged-in teacher's `id_przedmiotu` (each teacher teaches exactly one subject in this model). The timestamp is `NOW()`.

#### `UsunOceny()` (misnamed — this deletes attendance)
Triggered when `$_POST['usun']` is set. Deletes the row with the given `id_obecnosci` only if it belongs to the current teacher (security: prevents teachers from deleting each other's entries).

#### `frekSelect($z)`
Renders five `<td>` cells, each containing a radio button named `$z` with values Obecny/Spoźniony/Zwolniony/Usprawiedliwiony/Nieobecny. The `<td>` has `onclick='this.querySelector("input").checked=true' style='cursor:pointer'` so clicking anywhere in the cell selects the radio — a UX improvement for fast attendance taking. The `$z` parameter is the radio name (e.g., `"frek[42]"` for student id 42), forming a `frek[uczenID] => typ` array on submit.

### `functions/oceny_functions.php` — Grades views

#### `gradeClassFromString($s)`
Helper that returns a CSS class name based on the first character of a grade string:
- "1..." → `grade1`
- "2..." → `grade2`
- ... etc.
- Otherwise → empty string.

Used to color-code the grade badges (e.g., "1" is red, "6" is green). The actual colors are defined in `style.css`.

#### `GeneralKlasaInfo()`
Renders a summary table for the selected class:
- Homeroom teacher name
- Class name
- Number of students
- Class-wide weighted average: `SUM(ocena*waga)/SUM(waga)` over all grades in the class. Returns NULL if no grades exist.

#### `Wyszukaj()`
The main grades/averages table. Behavior depends on `$_POST['KlasaUczen']` and the `WedlugPrzedmiotow` checkbox:

**Klasa mode (checkbox off):**
- For each student: name, badge list of all their grades (oldest first, left to right), weighted average.
- Plus three extra columns per student: a grade `<select>` (`ocena[uczenID]`), a weight `<select>` (`waga[uczenID]`), and a comment `<input>` (`komentarz[uczenID]`). This is the **batch grade entry** form — the teacher can assign one grade to many students at once. The same form also includes a "dodaj" button below the table that triggers `DodajOceny()`.

**Klasa mode (checkbox on):**
- Calls `WedlugPrzedmiotow_Klasa()` instead (per-subject averages).

**Uczen mode (checkbox off):**
- Renders the "Dodaj ocenę" table (grade/weight/comment inputs for the single selected student) at the top.
- Then the main table: student name, grade badges, weighted average.
- Then `UczenOceny()` (the detailed per-grade table with delete buttons).

**Uczen mode (checkbox on):**
- Renders the main table (same as uczen-off) followed by `WedlugPrzedmiotow_Uczen()` (per-subject averages).

The `Ocena/Średnia` columns are always present; the input columns only appear when not in "Według przedmiotów" mode.

If a student has no grades at all, the badge column shows "Brak ocen" but the input columns are still present so the teacher can add the first grade.

#### `UczenOceny()` (the real grades version)
Renders a per-grade table for one student: each row shows the teacher, subject, timestamp, grade (as a colored badge), weight, comment, and a delete button (only for the current teacher's own grades).

#### `WedlugPrzedmiotow_Klasa()`
Renders a per-subject average for the selected class. Uses `LEFT JOIN` so subjects with no grades still appear (NULL average).

#### `WedlugPrzedmiotow_Uczen()`
Renders a per-subject average for the selected student. For each subject, queries the student's grades and computes `SUM(ocena*waga)/SUM(waga)`. If a subject has no grades, the row shows "Brak" / "—".

#### `DodajOceny()`
Triggered by `$_POST['dodajPrzycisk']`. Iterates `$_POST['ocena']` (an array of `id_ucznia => ocena_wartosc`). For each entry:
- If the grade is not `-1` (placeholder) and the weight is not `-1` (placeholder), insert a row into `oceny` with the current subject (from the teacher's `id_przedmiotu`), the current teacher, and `NOW()`.
- The comment is HTML-escaped via `real_escape_string` to prevent SQL injection (no prepared statements are used in this codebase, so escaping is the only defense).

Includes a null check on `$_POST['ocena']` to avoid errors if the form is submitted without any grade data.

#### `UsunOceny()`
Triggered by `$_POST['usun']`. Deletes the grade with the given `id_oceny` only if it belongs to the current teacher.

#### `ocenaSelect()`
Renders `<option>` elements for every entry in `OcenyDictionary`. The first option has value `-1` and label "Ocena" (the placeholder).

#### `wagaSelect()`
Renders `<option>` elements with values 1 through 5 (and a "-1" placeholder). Matches the `CHECK (waga >= 1 && waga <= 5)` constraint.

### `functions/uwagi_functions.php` — Remarks views

#### `GeneralKlasaInfo()`
Renders a summary table for the selected class:
- Homeroom teacher name
- Class name
- Number of distinct students
- Count of positive remarks: `SUM(uwagi.typ = 'Pozytywna')`
- Count of negative remarks: `SUM(uwagi.typ = 'Negatywna')`

Uses `LEFT JOIN uwagi` so classes with no remarks still appear (counts are 0).

#### `Wyszukaj()`
The main remarks table. Renders a row per student with:
- Student name and class
- Count of positive remarks
- Count of negative remarks
- A type `<select>` (Pozytywna/Negatywna) named `uwaga_typ[uczenID]`
- A comment `<input>` named `uwaga_opis[uczenID]`

This is the **batch remark entry** form — the teacher can add one remark to many students at once.

#### `UczenUwagi()`
Renders a per-remark table for one student: each row shows timestamp, type (as a colored badge), description, teacher, and a delete button (only for the current teacher's own remarks).

#### `WedlugPrzedmiotow_Klasa()`
When the checkbox is on and class mode is selected, this renders a table with one row per teacher showing their positive/negative remark counts for the selected class.

#### `WedlugPrzedmiotow_Uczen()`
Same as above but for one student — per-teacher remark counts.

#### `DodajUwagi()`
Triggered by `$_POST['dodajPrzycisk']`. Iterates `$_POST['uwaga_typ']` and inserts a remark for each non-placeholder entry. Uses `real_escape_string` for the description.

#### `UsunUwagi()`
Triggered by `$_POST['usun']`. Deletes the remark with the given `id_uwagi` only if it belongs to the current teacher.

#### `uwagaTypSelect()`
Renders `<option>` elements: `-1` placeholder, "Pozytywna", "Negatywna".

### `functions/terminarz_functions.php` — Calendar/Scheduler views

The calendar is rendered as a standard 7-column grid (Mon..Sun) with the current month displayed. The page also has a "dodaj" (add) page for individual events and a "info" view for existing events.

#### `ShowTerminarz()`
Renders the monthly calendar grid:
1. Reads `wybrany_rok`, `wybrany_miesiac`, `wybrana_klasa` from POST, with session fallbacks (`terminarz_rok`, `terminarz_miesiac`, `terminarz_klasa`). If those session values are missing, defaults are `date('Y')`, `date('n')`, and 1.
2. Mirrors POST values back into `$_SESSION` so the selects stay selected across page navigations.
3. Builds a `DateTime` for the 1st of the selected month, then queries the calendar grid:
   - `$daysInMonth` = number of days in the month (`t` format = total days).
   - `$firstDayMonth` = day-of-week of the 1st (1=Mon..7=Sun).
   - `$date->modify('-1 days')` moves the date pointer to the last day of the previous month, so the first `+1 day` in the inner loop lands on the 1st of the current month.
   - The outer `while ($d-$firstDayMonth < $daysInMonth)` loop iterates by 7-cell rows.
   - The inner `for ($j = 1; $j < 8; $j++)` walks each cell of the row. For each cell:
     - `$date->modify('+1 days')` advances the date pointer.
     - `$dateString = $date->format('Y-m-d')` is the cell's real date.
     - A SQL query selects all events for the selected class whose date range covers `$dateString` (using `DATEDIFF('$dateString', zakres_start) >= 0 AND DATEDIFF(zakres_end, '$dateString) >= 0`).
     - If the cell is in the current month (i.e., `$d-$firstDayMonth+1` is in `[1, $daysInMonth]`), the cell is rendered with the day number and the events.
     - Otherwise, an empty cell is rendered.
     - A hidden radio input `terminarzAdd` (value = 0-indexed day offset) and a visible `+` label are added. Clicking the `+` checks the radio and submits the form to `terminarzInfoAdd.php` with the calendar context.
     - For each existing event, an `X` label is rendered (clicking it submits the form with `terminarzREMOVE` set to delete the event) and an event card is rendered (clicking it submits to `terminarzInfoAdd.php` with `terminarzINFO` set to show event details).
4. The loop naturally ends when `$d` advances past the last day of the month.

#### `miesiacSelect()`
Renders a `<select>` with 12 `<option>` elements (Styczeń..Grudzień). The selected month is determined by:
1. `$_POST['wybrany_miesiac']` if present,
2. else `$_SESSION['terminarz_miesiac']` if set,
3. else the current month (`date('n')`).

The selection is mirrored back to `$_SESSION['miesiacDefault']`.

#### `rokSelect()`
Renders a `<select>` with one `<option>` per year between `MIN(zakres_start)` and `MAX(zakres_end)` from the `terminarz` table. If the table is empty, the default is the current year. The selected year is determined similarly to month (POST → session → current year), and is mirrored back to `$_SESSION['rokDefault']`.

#### `terminarzDodaj()`
Triggered by `$_POST['TerAdd']` (the "dodaj" button on the add-event page). Reads `zakresS`, `zakresE`, `typT`, `opisT` from POST, gets the teacher's subject from the database, and inserts a new row in `terminarz` with `data_dodania = NOW()`.

#### `terminarzRemove()`
Triggered by `$_POST['terminarzREMOVE']`. Deletes the event with the given `id_wydarzenia` only if it belongs to the current teacher.

### `functions/plan_lekcji_functions.php` — Lesson plan views

This file is not shown in full in the conversation, but based on the database schema it likely renders the weekly schedule (rows = days, columns = periods) for a selected class.

---

## Page Scripts

### `logowanie.php` — Login

Not shown in full in the conversation, but based on `core/SessionCheck.php` it:
- Renders a login form with `imie`/`nazwisko` and password fields (or a single teacher-id + password).
- On submit, looks up the teacher in `nauczyciele` and verifies the password (compared against the `Haslo` column, which is set to 'Sala332!' for every teacher).
- On success, sets `$_SESSION['id_nauczyciela']` and redirects to `glowna.php`.

### `glowna.php` — Main page

The teacher's home page. Renders navigation buttons to the main features (frekwencja, oceny, uwagi, terminarz, plan_lekcji).

### `frekwencja.php` — Attendance page

Controller for the attendance view. Flow:
1. `require` `core/idk.php` (bootstrap).
2. `require` `functions/frekwencja_functions.php` (helpers).
3. Output the HTML head, body, and the shared toolbar (`core/frekOcenyUni.php`).
4. If `dodajPrzycisk` was pressed, call `DodajOceny()` (insert attendance).
5. If `usun` was pressed, call `UsunOceny()` (delete attendance).
6. If `KlasaUczen` was set, call `Init()`, then dispatch:
   - `klasa` → `GeneralKlasaInfo()`.
   - `WedlugPrzedmiotow` + `klasa` → `WedlugPrzedmiotow_Klasa()`.
   - `WedlugPrzedmiotow` + `uczen` → `Wyszukaj()` + `WedlugPrzedmiotow_Uczen()`.
   - else → `Wyszukaj()`, then if klasa add a "dodaj" button; if uczen call `UczenOceny()` (the attendance list).
7. If no `KlasaUczen` was set, output "Wybierz klase lub ucznia".

### `oceny.php` — Grades page

Controller for the grades view. Similar structure to `frekwencja.php` but uses `functions/oceny_functions.php`. The "dodaj" button is shown for both klasa and uczen modes (since the grade-input row is built into the `Wyszukaj()` table).

### `uwagi.php` — Remarks

Controller for the remarks view. Almost identical to `frekwencja.php` but uses `functions/uwagi_functions.php` and calls `DodajUwagi()` / `UsunUwagi()` / `UczenUwagi()`. The "dodaj" button is rendered for both klasa and uczen modes.

### `terminarz.php` — Calendar

Controller for the calendar view. Flow:
1. `require` `core/idk.php` (bootstrap).
2. `require` `functions/terminarz_functions.php` (helpers).
3. `require` `core/SelectUczenKlasa.php` (for `SelectKlasy()`).
4. **Mirror POST → session**: copies `wybrana_klasa`, `wybrany_miesiac`, `wybrany_rok` from POST into `$_SESSION` keys `klasaDefault`, `terminarz_klasa`, `miesiacDefault`, `terminarz_miesiac`, `rokDefault`, `terminarz_rok`. This is what makes the selects stay selected after returning from `terminarzInfoAdd.php`.
5. If `TerAdd` was pressed, call `terminarzDodaj()` and redirect to `terminarz.php`.
6. If `terminarzREMOVE` was pressed, call `terminarzRemove()` and redirect to `terminarz.php`.
7. Render the HTML, including:
   - The toolbar with class/month/year `<select>`s and a "Do głównej" button.
   - `Init()` then `ShowTerminarz()` (the calendar grid).

### `terminarzInfoAdd.php` — Add/View event page

Secondary page for adding new events or viewing existing ones. Flow:
1. `require` `core/idk.php` (bootstrap).
2. `require` `functions/terminarz_functions.php` (helpers).
3. If `TerAdd` was pressed, call `terminarzDodaj()` and redirect to `terminarz.php`.
4. **Mirror POST → session** (same as `terminarz.php`, so the calendar state is preserved on return).
5. **Pre-fill the date** for the add form. If `terminarzAdd` is in POST (i.e., the user clicked the `+` on a specific day), compute the actual date by adding the day offset to the 1st of the month.
6. Render:
   - A "Wróć" (back) button — a small form that POSTs the saved class/month/year back to `terminarz.php` so the calendar state is preserved.
   - If `terminarzINFO` is set: show event details (date or range, teacher, subject, type, description, added-on timestamp).
   - Otherwise: show the add form (start datetime, end datetime, teacher (read-only), subject (read-only), event type select, description input) and a "dodaj" submit button.

### `plan_lekcji.php` — Lesson plan

Renders the weekly schedule for a selected class. Not shown in full, but based on the schema it likely:
- Renders a 5-row × N-column grid (rows = days, columns = periods).
- Populates each cell with the teacher and subject of the lesson at that day+period.
- Uses the shared toolbar (`core/frekOcenyUni.php`) to select the class.

---

## Session & POST Conventions

### Authentication
- On successful login, `logowanie.php` sets `$_SESSION['id_nauczyciela'] = $id`.
- Every subsequent page load includes `core/SessionCheck.php`, which redirects to `logowanie.php` if `id_nauczyciela` is not set.

### Form state persistence
For multi-step flows (like calendar → add event → back), the codebase uses a `$_SESSION` mirror:
- On every load of a page that has selects, POST values are copied into `$_SESSION`.
- Select-generating functions prefer POST, then fall back to `$_SESSION`, then to a safe default.
- The "back" button in `terminarzInfoAdd.php` is a form that POSTs the session-saved values to the parent page, ensuring the calendar's selects stay selected.

### Insert/delete security
Every `Dodaj*` and `Usun*` function:
- Reads the current teacher's id from `$_SESSION['id_nauczyciela']` and includes it in the `WHERE` clause (for inserts) or uses it as a guard (for deletes: `AND id_nauczyciela = $id_nauczyciela`).
- This ensures teachers can only modify their own records.
- For text fields, uses `$conn->real_escape_string()` to prevent SQL injection (no prepared statements are used).

### Post-Redirect-Get
After every successful insert/update, the handler issues `header('Location: ...')` followed by `exit;`. This prevents the browser from re-submitting the form on refresh and gives a clean URL.

### JavaScript form helpers
- Radio/checkbox changes auto-submit: `onchange="this.form.submit()"`.
- Clicking a cell in the attendance grid selects the radio: `onclick='this.querySelector("input").checked=true'`.
- Clicking a calendar event or `+` button programmatically sets a hidden radio to checked and submits the form to a different `action` (e.g., `terminarzInfoAdd.php`).
- Clicking an "X" delete label submits the form with the `usun` POST key set to the row id.

### History replacement
Every page includes this at the end of `<body>`:
```html
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
```
This replaces the current history entry with the same URL, preventing the browser from re-submitting POST data when the user navigates back. The "Post-Redirect-Get" pattern means there's no POST data to resubmit, but this is a belt-and-suspenders safety.

---

## Glossary

| Polish term | English | Notes |
|---|---|---|
| Frekwencja | Attendance | Per-class or per-student. |
| Oceny | Grades | Weighted (1–5), with comments. |
| Uwagi | Remarks | Positive or negative, with description. |
| Terminarz | Calendar/Schedule | Monthly view of events. |
| Plan lekcji | Lesson plan | Weekly grid (days × periods). |
| Wychowawca | Homeroom teacher | The teacher responsible for a class. |
| Klasa | Class | A group of students (e.g., "1A"). |
| Uczeń | Student | Belongs to one class. |
| Nauczyciel | Teacher | Teaches one subject in this model. |
| Przedmiot | Subject | E.g., "Math", "Polish". |
| Dodaj | Add | Button to add a new record. |
| Usuń | Delete/Remove | Button to delete a record. |
| Wróć | Back | Return to previous page. |
| Do głównej | To main | Return to the home page. |
| Wybierz klase lub ucznia | Choose class or student | Shown when no `KlasaUczen` POST is set. |
| Według przedmiotów | By subject | Checkbox that toggles per-subject view. |
| Średnia | Average | Computed as `SUM(ocena*waga)/SUM(waga)` for grades. |
| Ocena | Grade | A single grade value. |
| Waga | Weight | 1–5, used in the weighted average. |
| Komentarz | Comment | Optional text attached to a grade. |
| Sprawdzian | Test | Major graded test. |
| Kartkówka | Quiz | Short graded quiz. |
| Nieobecność | Absence | Teacher's absence. |
| Zastępstwo | Substitution | A substitute teacher. |
| Wywiadówka | Parent-teacher meeting | Class-level meeting. |
| Obecny | Present | Attendance: present. |
| Nieobecny | Absent | Attendance: absent (unexcused). |
| Usprawiedliwiony | Excused | Attendance: excused. |
| Zwolniony | Released | Attendance: released (e.g., early dismissal). |
| Spóźniony | Late | Attendance: late. |
| Pozytywna | Positive | Remark: positive. |
| Negatywna | Negative | Remark: negative. |
| Uwaga | Note/Remark | A student's remark. |
| Sala | Classroom/Room | The room number in the lesson plan. |
| Numer lekcji | Lesson number | Period number (1st, 2nd, etc.). |
| Dzień tygodnia | Day of week | Mon, Tue, ... |
| Dodaj ocenę | Add grade | Button to add a grade. |
| Dodaj wpis | Add entry | Button to add a calendar event. |
| Dodaj uwagę | Add remark | Button to add a remark. |
| Wpis | Entry | A record. |
| Dzień | Day | |
| Miesiąc | Month | |
| Rok | Year | |
| Numer w dzienniku | Number in register | The student's position in the class roster (1, 2, 3, ...). |
| Imię | First name | |
| Nazwisko | Surname/Last name | |
| Wybrana klasa | Selected class | |
| Wybrany uczeń | Selected student | |
| Wybrany rok | Selected year | |
| Wybrany miesiąc | Selected month | |
| Szczegóły | Details | Shown in the event info view. |
| Zakres | Range | A date/time range. |
| Opis | Description | Optional free-text field. |
| Dodano | Added on | Timestamp when the record was added. |
| Data i czas | Date and time | |
| Przedmiot | Subject | |
| Nauczyciel | Teacher | |
| Klasa | Class | |
| Klasa/Uczeń | Class/Student | Radio toggle. |
| Niezalogowany | Not logged in | |
| Zaloguj | Log in | |
| Wyloguj | Log out | |
| Średnia klasy | Class average | |
| Średnia ucznia | Student average | |
| Frekwencja klasy | Class attendance rate | Percentage. |
| Brak | None/Missing | Shown when a student has no records. |
| Brak wpisów | No entries | Shown in empty results. |
| Brak ocen | No grades | Shown when a student has no grades. |
| Brak uwag | No remarks | Shown when a student has no remarks. |
| Ilość | Count/Quantity | |
| Ilość uczniów | Number of students | |
| Ilość pozytywnych | Number of positive | (remarks). |
| Ilość negatywnych | Number of negative | (remarks). |
| Uwagi pozytywne | Positive remarks | |
| Uwagi negatywne | Negative remarks | |
| Zakres: | Range: | (event info) |
| Data: | Date: | (event info) |
| Powrót | Return | |
| Strona główna | Home page | |
| Witaj | Welcome | |
| Zalogowany jako | Logged in as | |
| Hasło | Password | |
| Imię i nazwisko | Full name | |
| Wybierz | Choose | |
| Zatwierdź | Confirm | |
| Anuluj | Cancel | |
| Szukaj | Search | |
| Filtruj | Filter | |
| Eksportuj | Export | |
| Drukuj | Print | |
| Ustawienia | Settings | |
| Profil | Profile | |
| Pomoc | Help | |
| O programie | About | |
| Wersja | Version | |
| Rok szkolny | School year | |
| Semestr | Semester | |
| Lekcja | Lesson/Class period | |
| Przerwa | Break | |
| Dzwonek | Bell | |
| Plan | Plan | |
| Zajęcia | Activities/Classes | |
| Wydarzenie | Event | |
| Wydarzenia | Events | |
| Sprawdziany | Tests | |
| Zadanie domowe | Homework | |
| Projekt | Project | |
| Prezentacja | Presentation | |
| Praca klasowa | Class work | |
| Odpowiedź ustna | Oral answer | |
| Aktywność | Activity/Participation | |
| Zachowanie | Behavior | |
| Punkt | Point | |
| Procent | Percent | |
| Liczba | Number | |
| Wartość | Value | |
| Ocena końcowa | Final grade | |
| Ocena semestralna | Semester grade | |
| Ocena roczna | Yearly grade | |
| Klasyfikacja | Classification/Grading | |
| Egzamin | Exam | |
| Test | Test | |
| Kartkówka | Quiz | |
| Sprawdzian | Test | |
| Zadanie | Task/Assignment | |
| Ćwiczenie | Exercise | |
| Powtórzenie | Review | |
| Materiał | Material | |
| Temat | Topic | |
| Lekcja | Lesson | |
| Konsultacje | Consultation | |
| Godziny przyjęć | Office hours | |
| Pedagog | Pedagogue/School counselor | |
| Psycholog | Psychologist | |
| Dyrektor | Principal | |
| Wicedyrektor | Vice-principal | |
| Sekretariat | School office | |
| Pedagogiczny | Pedagogical | |
| Rada pedagogiczna | Pedagogical council | |
| Zebranie | Meeting | |
| Rodzic | Parent | |
| Opiekun | Guardian | |
| Uczeń | Student | |
| Absolwent | Graduate | |
| Klasa | Class/Grade | |
| Szkoła | School | |
| Liceum | High school/Lyceum | |
| Technikum | Technical school | |
| Szkoła podstawowa | Primary school | |
| Gimnazjum | Middle school | |
| Przedszkole | Kindergarten | |
| Uniwersytet | University | |
| Studia | Studies | |
| Kierunek | Field of study | |
| Specjalizacja | Specialization | |
| Grupa | Group | |
| Podgrupa | Subgroup | |
| Rok studiów | Year of study | |
| Semestr | Semester | |
| Sesja | Exam session | |
| Egzamin | Exam | |
| Zaliczenie | Credit/Pass | |
| Poprawka | Retake | |
| Komisja | Committee | |
| Przewodniczący | Chairman | |
| Członek | Member | |
| Protokół | Protocol/Minutes | |
| Raport | Report | |
| Sprawozdanie | Statement/Report | |
| Dokument | Document | |
| Zaświadczenie | Certificate | |
| Legitymacja | ID card | |
| Indeks | Index/Record book | |
| Dziennik | Register/Gradebook | |
| Klasówka | Class test | |
| Kartkówka | Short test/Quiz | |
| Sprawdzian | Test | |
| Egzamin | Exam | |
| Zaliczenie | Pass/Credit | |
| Ocena | Grade | |
| Stopień | Degree/Grade | |
| Poziom | Level | |
| Klasa | Class | |
| Grupa | Group | |
| Rok | Year | |
| Semestr | Semester | |
| Okres | Period/Term | |
| Kwartał | Quarter | |
| Miesiąc | Month | |
| Tydzień | Week | |
| Dzień | Day | |
| Godzina | Hour | |
| Minuta | Minute | |
| Sekunda | Second | |
| Data | Date | |
| Czas | Time | |
| Termin | Deadline/Date | |
| Harmonogram | Schedule | |
| Grafik | Timetable | |
| Rozkład | Layout/Schedule | |
| Kalendarz | Calendar | |
| Plan lekcji | Lesson plan | |
| Plan zajęć | Activity plan | |
| Rozkład dnia | Daily schedule | |
| Rozkład tygodnia | Weekly schedule | |
| Tygodniowy | Weekly | |
| Dzienny | Daily | |
| Miesięczny | Monthly | |
| Roczny | Yearly/Annual | |
| Tygodniowo | Weekly | |
| Dziennie | Daily | |
| Miesięcznie | Monthly | |
| Rocznie | Yearly/Annually | |
| Co tydzień | Every week | |
| Co dzień | Every day | |
| Co miesiąc | Every month | |
| Co rok | Every year | |
| Wakacje | Vacation | |
| Ferie | Holiday | |
| Przerwa świąteczna | Christmas break | |
| Przerwa zimowa | Winter break | |
| Przerwa wiosenna | Spring break | |
| Przerwa letnia | Summer break | |
| Dzień wolny | Day off | |
| Święto | Holiday | |
| Uroczystość | Ceremony | |
| Apel | Assembly | |
| Zgromadzenie | Gathering | |
| Zebranie | Meeting | |
| Konferencja | Conference | |
| Szkolenie | Training | |
| Kurs | Course | |
| Warsztaty | Workshop | |
| Wykład | Lecture | |
| Seminarium | Seminar | |
| Prelekcja | Talk/Lecture | |
| Wycieczka | Trip | |
| Wyjście | Outing | |
| Wyjazd | Trip away | |
| Zawody | Competition | |
| Olimpiada | Olympiad | |
| Konkurs | Contest | |
| Zawody sportowe | Sports competition | |
| Mecz | Match | |
| Turniej | Tournament | |
| Finał | Final | |
| Półfinał | Semi-final | |
| Ćwierćfinał | Quarter-final | |
| Eliminacje | Eliminations/Qualifiers | |
| Kwalifikacje | Qualifications | |
| Start | Start | |
| Meta | Finish | |
| Trasa | Route | |
| Dystans | Distance | |
| Czas | Time | |
| Rekord | Record | |
| Wynik | Result/Score | |
| Punktacja | Scoring | |
| Tabela | Table
