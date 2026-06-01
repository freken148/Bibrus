<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body class="bodyGlobal bodyGlowna">
    <?php
        session_start();
        require "core/SessionCheck.php";
        echo "Witaj, " . $_SESSION['imie'] . ' ' . $_SESSION['nazwisko'] . '<br>';
    ?>
    <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='frekwencja.php'">Frekwencja</button>
    <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='oceny.php'">Oceny</button>
    <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='plan_lekcji.php'">Plan lekcji</button>
    <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='terminarz.php'">Terminarz</button>
    <button class="buttonGlobal buttonGlowna navButton" onclick="document.location='uwagi.php'">Uwagi</button>
    <form class="formGlobal formGlowna" method="POST" action="logowanie.php">
        <button class="buttonGlobal buttonGlowna submitButton">Wyloguj</button>
    </form>
</body>
</html>
