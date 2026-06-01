<?php
    require "core/db.php";
    session_start();
    session_unset();
    session_destroy();
    session_start();
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
</head>
<body class="bodyGlobal bodyLogowanie">
    <form class="formGlobal formLogowanie formLogin" method="POST" action="logowanie.php">
        <input class="inputGlobal inputLogowanie inputLogin" name="imie" placeholder="imie" pattern="^[^\\]*$">
        <input class="inputGlobal inputLogowanie inputLogin" name="nazwisko" placeholder="nazwisko" pattern="^[^\\]*$">
        <input class="inputGlobal inputLogowanie inputLogin" name="haslo" type="password" placeholder="haslo" pattern="^[^\\]*$">
        <button class="buttonGlobal buttonLogowanie submitButton">Zaloguj</button>
    </form>
</body>
</html>

<?php
if (isset($_POST['imie']) && isset($_POST['nazwisko']) && isset($_POST['haslo'])) {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $haslo = $_POST['haslo'];

    $sql = "SELECT id_nauczyciela, imie, nazwisko, haslo 
            FROM nauczyciele 
            WHERE imie = '$imie' AND nazwisko = '$nazwisko' AND haslo = '$haslo'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $_SESSION['id_nauczyciela'] = $row['id_nauczyciela'];
            $_SESSION['imie'] = $row['imie'];
            $_SESSION['nazwisko'] = $row['nazwisko'];

            $_SESSION['klasaDefault'] = 1;
            $_SESSION['uczenDefault'] = 1;
            $_SESSION['miesiacDefault'] = 1;
            $_SESSION['rokDefault'] = 1;
        }
        header('Location: glowna.php');
    } else {
        echo "Błędne dane";
    }
}
?>
