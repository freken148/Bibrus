<?php
    // handle inline add (TerAdd is pressed inside the add form)
    if (isset($_POST['TerAdd'])) {
        terminarzDodaj();
        header('Location: terminarz.php');
        exit;
    }

    // Persist calendar selection on entry from terminarz.php
    if (isset($_POST['wybrana_klasa'])) {
        $_SESSION['klasaDefault'] = intval($_POST['wybrana_klasa']);
        $_SESSION['terminarz_klasa'] = intval($_POST['wybrana_klasa']);
    }
    if (isset($_POST['wybrany_miesiac'])) {
        $_SESSION['terminarz_miesiac'] = intval($_POST['wybrany_miesiac']);
        $_SESSION['miesiacDefault'] = intval($_POST['wybrany_miesiac']);
    }
    if (isset($_POST['wybrany_rok'])) {
        $_SESSION['terminarz_rok'] = $_POST['wybrany_rok'];
        $_SESSION['rokDefault'] = $_POST['wybrany_rok'];
    }

    // Pre-fill date from calendar day selection
    $prefillDate = '';
    if (isset($_POST['terminarzAdd'])) {
        $year = $_POST['wybrany_rok'] ?? date('Y');
        $month = str_pad($_POST['wybrany_miesiac'] ?? date('m'), 2, '0', STR_PAD_LEFT);
        $dayOffset = intval($_POST['terminarzAdd']);
        $prefillDateObj = new DateTime("$year-$month-01");
        $prefillDateObj->modify("+$dayOffset days");
        $prefillDate = $prefillDateObj->format('Y-m-d\TH:i');
    }
?>