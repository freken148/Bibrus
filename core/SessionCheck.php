<?php  
    if (!(isset($_SESSION['imie']) && isset($_SESSION['nazwisko']) && isset($_SESSION['id_nauczyciela']))) {
        header('Location: logowanie.php');
    };
?>