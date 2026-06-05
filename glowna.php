<?php
    require "core/idk.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibrus</title>
    <?php require "core/head.php"; ?>
</head>
<body class="bodyGlobal bodyGlowna">
    <header class="glownaHeader">
        <h1>Witaj, <?= htmlspecialchars($_SESSION['imie'] . ' ' . $_SESSION['nazwisko']) ?></h1>
        <p>Wybierz sekcję</p>
    </header>

    <nav class="glownaNav">
        <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='frekwencja.php'">Frekwencja</button>
        <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='oceny.php'">Oceny</button>
        <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='plan_lekcji.php'">Plan lekcji</button>
        <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='terminarz.php'">Terminarz</button>
        <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='uwagi.php'">Uwagi</button>
    </nav>

    <footer class="glownaFooter">
        <form class="formGlownaLogout" method="POST" action="logowanie.php">
            <button class="buttonGlobal buttonGlowna submitButton">Wyloguj</button>
        </form>
    </footer>
</body>
</html>
