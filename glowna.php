<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        session_start();
        require "core/SessionCheck.php";
        echo "Witaj, " . $_SESSION['imie'] . ' ' . $_SESSION['nazwisko'] . '<br>';
    ?>
    <button onclick="document.location='frekwencja.php'">Frekwencja</button>
    <button onclick="document.location='oceny.php'">Oceny</button>
    <button onclick="document.location='terminarz.php'" disabled>Terminarz</button>
    <button onclick="document.location='plan_lekcji.php'">Plan lekcji</button>
    <form method="POST" action="logowanie.php">
        <button>Wyloguj</button>
    </form>
</body>
</html>