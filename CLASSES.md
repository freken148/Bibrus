# Bibrus — CSS Class Reference

This document lists every CSS class that has been added to the Bibrus project HTML elements, plus which PHP file/line each class is applied to.

The naming convention is **two-tier**:

```
class="<globalClass> <pageClass> [<variantClass>...]"
```

- **Global classes** — applied to every matching element on every page (one class per HTML element type)
- **Page classes** — one set per page (one class per HTML element type per page)
- **Variant classes** — extra classes for elements that look different from their siblings (e.g. "dodaj" button vs "remove" button)

A single CSS rule can target a global class (applies everywhere), a page class (applies on one page), or both together (most specific).

---

## How to use these in CSS

```css
/* Style all tables in the app */
.tableGlobal { border-collapse: collapse; }

/* Style only uwagi.php tables */
.tableUwagi { background: #fff; }

/* Style only the per-student add-form table on uwagi */
.tableWyszukaj { width: 100%; }

/* Style the "dodaj" button (matches both global and variant) */
.buttonGlobal.dodajButton { background: green; color: white; }
```

The general pattern is:

```html
<element class="globalClass pageClass [variantClass] ...">
```

---

## Global classes (apply on every page)

| Class | Element | Purpose |
|---|---|---|
| `bodyGlobal` | `<body>` | Base body styling (font, background, margins) |
| `formGlobal` | `<form>` | Base form layout (spacing, alignment) |
| `tableGlobal` | `<table>` | Base table styling (borders, padding) |
| `buttonGlobal` | `<button>` | Base button styling (padding, font, hover) |
| `inputGlobal` | `<input>` | Base input styling (border, padding, font) |
| `selectGlobal` | `<select>` | Base select styling (border, padding) |
| `labelGlobal` | `<label>` | Base label styling (margin, cursor) |
| `optionGlobal` | `<option>` | Base option styling (background, font) |
| `divGlobal` | `<div>` | Base div styling (used by terminarz event cards) |

---

## Per-page local classes (one set per page)

For each page `X` ∈ {`Uwagi`, `Frekwencja`, `Oceny`, `PlanLekcji`, `Terminarz`, `Logowanie`, `Glowna`}:

| Class | Purpose |
|---|---|
| `bodyX` | Page-specific body tweaks |
| `formX` | Page-specific form tweaks |
| `tableX` | Page-specific table tweaks |
| `buttonX` | Page-specific button tweaks |
| `inputX` | Page-specific input tweaks |
| `selectX` | Page-specific select tweaks |
| `labelX` | Page-specific label tweaks |
| `optionX` | Page-specific option tweaks |

Examples:
- `<button class="buttonGlobal buttonUwagi dodajButton">` in `uwagi.php`
- `<button class="buttonGlobal buttonFrekwencja dodajButton">` in `frekwencja.php`

---

## Variant classes

| Class | Element | Where | Purpose |
|---|---|---|---|
| `dodajButton` | "dodaj" `<button>` on every page | All `*_functions.php` (called from each page's `*_functions.php`) | Visually distinct from other buttons (e.g. green) |
| `removeButton` | "Usuń" `<button>` | `frekwencja_functions.php UczenOceny`, `oceny_functions.php UczenOceny`, `uwagi_functions.php UczenUwagi` | Visually distinct (e.g. red) |
| `submitButton` | submit `<button>` (Zaloguj, Wyloguj, TerAdd) | `glowna.php`, `logowanie.php`, `terminarzInfoAdd.php` | Visually distinct (e.g. primary blue) |
| `navButton` | nav `<button>` on `glowna.php` | `glowna.php` | Card-style for the main menu |
| `glownaButton` | "Do głównej" `<button>` | `core/frekOcenyUni.php`, `plan_lekcji.php`, `terminarz.php` | Small/secondary text button |
| `formLogin` | `<form>` | `logowanie.php` | Centered, narrow login form |
| `inputLogin` | `<input>` in login | `logowanie.php` | Larger, stacked login inputs |
| `radioGlobal` | `<input type="radio">` | `core/frekOcenyUni.php`, `frekwencja_functions.php frekSelect` | Distinct from text inputs (small) |
| `checkboxGlobal` | `<input type="checkbox">` | `core/frekOcenyUni.php` | Distinct from radios (small) |
| `tableKlasa` | class-summary `<table>` | All `*_functions.php GeneralKlasaInfo` | Wider / different layout than per-student tables |
| `tableWyszukaj` | per-student input `<table>` | All `*_functions.php Wyszukaj` | Distinct from data tables |
| `tableUczen` | per-student history `<table>` | `frekwencja_functions.php UczenOceny`, `oceny_functions.php UczenOceny`, `uwagi_functions.php UczenUwagi` | Distinct from input table |
| `tableWedlugPrzedmiotow` | per-subject summary `<table>` | All `*_functions.php WedlugPrzedmiotow_*` | Simpler styling |
| `tableTerminarzCalendar` | calendar `<table>` | `plan_lekcji_functions.php ShowPlan`, `terminarz_functions.php ShowTerminarz` | Grid styling |
| `tableTerminarzDetails` | details `<table>` | `terminarzInfoAdd.php` | Key-value layout |
| `tdCalendarDay` | calendar `<td>` | `terminarz_functions.php ShowTerminarz` | Fixed size / positioning context |
| `tdCalendarDayTerminarz` | active day `<td>` | `terminarz_functions.php ShowTerminarz` | Day with events |
| `tdCalendarDayEmpty` | empty leading `<td>` | `terminarz_functions.php ShowTerminarz` | Padding cell |
| `divTerminarz` | event card `<div>` | `terminarz_functions.php ShowTerminarz` | Card-like event container |
| `eventCard` | event card `<div>` | `terminarz_functions.php ShowTerminarz` | Same as divTerminarz, separate hook |
| `addEventButton` | "+" `<button>` | `terminarz_functions.php ShowTerminarz` | Circular / small |
| `eventDeleteLabel` | "X" `<label>` | `terminarz_functions.php ShowTerminarz` | Small overlay |
| `terminarzXButton` | "X" `<label>` | `terminarz_functions.php ShowTerminarz` | **Special** corner-overlay X delete button |
| `inputTerminarzAdd` | `<input>` in terminarz add form | `terminarzInfoAdd.php` | Distinct styling for the add form |
| `selectTerminarzAdd` | `<select>` in terminarz add form | `terminarzInfoAdd.php` | Distinct styling for the add form |
| `formTerminarzAdd` | `<form>` in terminarz add form | `terminarzInfoAdd.php` | Distinct styling for the add form |

---

## Where each class is applied — by file

### glowna.php
- `<body class="bodyGlobal bodyGlowna">`
- 5 nav `<button class="buttonGlobal buttonGlowna navButton">` (Frekwencja, Oceny, Plan lekcji, Terminarz, Uwagi)
- `<form class="formGlobal formGlowna">` (logout)
- `<button class="buttonGlobal buttonGlowna submitButton">` (Wyloguj)

### logowanie.php
- `<body class="bodyGlobal bodyLogowanie">`
- `<form class="formGlobal formLogowanie formLogin">`
- 3× `<input class="inputGlobal inputLogowanie inputLogin">` (imie, nazwisko, haslo)
- `<button class="buttonGlobal buttonLogowanie submitButton">` (Zaloguj)

### core/frekOcenyUni.php (shared by frekwencja/oceny/uwagi)
- 2× `<label class="labelGlobal labelFrekwencja">` wrapping the Klasa/Uczen radios
- 2× `<input class="inputGlobal inputFrekwencja radioGlobal" type="radio">`
- 2× `<select class="selectGlobal selectFrekwencja">` (wybrana_klasa, wybrany_uczen)
- 1× `<label class="labelGlobal labelFrekwencja">` wrapping the Wedlug przedmiotow checkbox
- 1× `<input class="inputGlobal inputFrekwencja checkboxGlobal" type="checkbox">`
- 1× `<button class="buttonGlobal buttonFrekwencja glownaButton" name="glowna">`

### core/SelectUczenKlasa.php
- All `<option class='optionGlobal optionFrekwencja'>` (in `SelectKlasy` and `SelectUcznie`)

### frekwencja.php
- `<body class="bodyGlobal bodyFrekwencja">`
- `<form class="formGlobal formFrekwencja">`
- `<button class='buttonGlobal buttonFrekwencja dodajButton' name='dodajPrzycisk'>` (in klasa branch)

### oceny.php
- `<body class="bodyGlobal bodyOceny">`
- `<form class="formGlobal formOceny">`
- `<button class='buttonGlobal buttonOceny dodajButton' name='dodajPrzycisk'>` (in klasa branch)

### uwagi.php
- `<body class="bodyGlobal bodyUwagi">`
- `<form class="formGlobal formUwagi">`
- 2× `<button class='buttonGlobal buttonUwagi dodajButton' name='dodajPrzycisk'>` (one in klasa, one in uczen)

### plan_lekcji.php
- `<body class="bodyGlobal bodyPlanLekcji">`
- `<form class="formGlobal formPlanLekcji">`
- `<select class="selectGlobal selectPlanLekcji" name="wybrana_klasa">`
- `<button class="buttonGlobal buttonPlanLekcji glownaButton" name="glowna">`

### terminarz.php
- `<body class="bodyGlobal bodyTerminarz">`
- `<form class="formGlobal formTerminarz">`
- 3× `<select class="selectGlobal selectTerminarz">` (wybrana_klasa, wybrany_miesiac, wybrany_rok)
- `<button class="buttonGlobal buttonTerminarz glownaButton" name="glowna">`

### terminarzInfoAdd.php
- `<body class="bodyGlobal bodyTerminarz">`
- `<form class="formGlobal formTerminarz">`
- Details view: `<table class='tableGlobal tableTerminarz tableTerminarzDetails'>`
- Add view:
  - `<form class='formGlobal formTerminarz formTerminarzAdd'>`
  - `<table class='tableGlobal tableTerminarz tableTerminarzDetails'>`
  - 2× `<input class='inputGlobal inputTerminarz inputTerminarzAdd' type='datetime-local'>` (zakresS, zakresE)
  - `<input class='inputGlobal inputTerminarz inputTerminarzAdd' name='opisT'>`
  - `<select class='selectGlobal selectTerminarz selectTerminarzAdd' name='typT'>`
  - 7× `<option class='optionGlobal optionTerminarz'>` (event types)
  - `<input class='buttonGlobal buttonTerminarz submitButton' type='submit' name='TerAdd'>`

### functions/frekwencja_functions.php
- `GeneralKlasaInfo`: `<table class='tableGlobal tableFrekwencja tableKlasa'>`
- `Wyszukaj`: `<table class='tableGlobal tableFrekwencja tableWyszukaj'>`
- `UczenOceny`: `<button class='buttonGlobal buttonFrekwencja dodajButton'>` + `<table class='tableGlobal tableFrekwencja tableUczen'>` + each "Usuń" button gets `removeButton`
- `WedlugPrzedmiotow_Klasa` / `WedlugPrzedmiotow_Uczen`: `<table class='tableGlobal tableFrekwencja tableWedlugPrzedmiotow'>`
- `frekSelect`: each `<input type='radio'>` gets `class='inputGlobal inputFrekwencja radioGlobal'`

### functions/oceny_functions.php
- `GeneralKlasaInfo`: `<table class='tableGlobal tableOceny tableKlasa'>`
- `Wyszukaj`: `<table class='tableGlobal tableOceny tableWyszukaj'>` + per-student `<input class='inputGlobal inputOceny'>` (komentarz) and `<select class='selectGlobal selectOceny'>` (ocena, waga)
- `UczenOceny`: `<table class='tableGlobal tableOceny tableUczen'>` + each "Usuń" button gets `removeButton` + "dodaj" button gets `dodajButton`
- `WedlugPrzedmiotow_Klasa` / `WedlugPrzedmiotow_Uczen`: `<table class='tableGlobal tableOceny tableWedlugPrzedmiotow'>`
- `ocenaSelect` / `wagaSelect`: each `<option>` gets `class='optionGlobal optionOceny'`

### functions/uwagi_functions.php
- `GeneralKlasaInfo`: `<table class='tableGlobal tableUwagi tableKlasa'>`
- `Wyszukaj`: `<table class='tableGlobal tableUwagi tableWyszukaj'>` + per-student `<select class='selectGlobal selectUwagi'>` (Typ) and `<input class='inputGlobal inputUwagi'>` (Opis)
- `UczenUwagi`: `<table class='tableGlobal tableUwagi tableUczen'>` + each "Usuń" button gets `removeButton`
- `WedlugPrzedmiotow_Klasa` / `WedlugPrzedmiotow_Uczen`: `<table class='tableGlobal tableUwagi tableWedlugPrzedmiotow'>`
- `uwagaTypSelect`: each `<option>` gets `class='optionGlobal optionUwagi'`

### functions/plan_lekcji_functions.php
- `ShowPlan`: `<table class='tableGlobal tablePlanLekcji tableTerminarzCalendar'>`

### functions/terminarz_functions.php
- `ShowTerminarz`:
  - `<table class='tableGlobal tableTerminarz tableTerminarzCalendar'>`
  - Each active day: `<td class='tdCalendarDay tdCalendarDayTerminarz'>`
  - Each leading empty day: `<td class='tdCalendarDay tdCalendarDayEmpty'>`
  - Each event card: `<div class='divGlobal divTerminarz eventCard'>`
  - Each "X" delete: `<label class='labelGlobal labelTerminarz eventDeleteLabel terminarzXButton'>`
  - Each "+" add: `<button class='buttonGlobal buttonTerminarz addEventButton'>`
- `miesiacSelect`: each `<option>` gets `class='optionGlobal optionTerminarz'`
- `rokSelect`: each `<option>` gets `class='optionGlobal optionTerminarz'`

---

## Files that were NOT modified

- `core/db.php` — no HTML output, just DB connection
- `core/SessionCheck.php` — redirects only
- `core/idk.php` — `session_start` + auth, no HTML output
- `core/generate.php` — vibecoded data generator, not part of the live UI
- `core/bibrus.sql` — SQL schema, no HTML
- `README.md`, `LICENSE`, `.gitignore` — non-PHP

---

## CSS example

Here's a starter CSS file using these classes (drop into `style.css` in the project root and link it in each page's `<head>`):

```css
/* base */
bodyGlobal, .bodyGlobal { font-family: sans-serif; margin: 16px; background: #fafafa; }
tableGlobal, .tableGlobal { border-collapse: collapse; width: 100%; margin: 8px 0; }
.tableGlobal th { background: #ddd; padding: 6px 10px; }
.tableGlobal td { padding: 4px 10px; border-top: 1px solid #ccc; }
.inputGlobal, .inputGlobal, .selectGlobal, .selectGlobal { padding: 4px 8px; border: 1px solid #999; border-radius: 3px; }
.buttonGlobal, .buttonGlobal { padding: 6px 14px; border: 1px solid #666; background: #eee; cursor: pointer; border-radius: 3px; }

/* variants */
.dodajButton { background: #4a4; color: white; border-color: #383; }
.removeButton { background: #c44; color: white; border-color: #a33; }
.submitButton { background: #48c; color: white; border-color: #369; }
.glownaButton { background: transparent; color: #666; font-size: 12px; }
.navButton { display: block; width: 240px; margin: 8px 0; padding: 16px; text-align: left; }

/* per-page overrides */
.tableUwagi { background: #fff; }
.tableUwagi .tableUczen { background: #fefefe; }
.tableFrekwencja { background: #fff; }

/* calendar */
.tableTerminarzCalendar td { width: 14%; height: 80px; vertical-align: top; border: 1px solid #999; padding: 4px; }
.tdCalendarDayEmpty