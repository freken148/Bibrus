<?php
    require "core/idk.php";
    require "functions/uwagi_functions.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uwagi</title>
    <?php require "core/head.php"; ?>
</head>
<body class="bodyGlobal bodyUwagi">
    <form id="forma1" class="formGlobal formUwagi" method="POST" action="uwagi.php">
        <?php
            require "core/frekOcenyUni.php";

            if (isset($_POST['dodajPrzycisk'])) {
                DodajUwagi();
            }

            if (isset($_POST['usun'])) {
                UsunUwagi();
            }

            if (isset($_POST['KlasaUczen'])) {
                Init();

                if ($_POST['KlasaUczen'] == 'klasa') {
                    GeneralKlasaInfo();
                }

                if (isset($_POST['WedlugPrzedmiotow'])) {
                    if ($_POST['KlasaUczen'] == 'klasa') {
                        WedlugPrzedmiotow_Klasa();
                    }

                    if ($_POST['KlasaUczen'] == 'uczen') {
                        Wyszukaj();
                        WedlugPrzedmiotow_Uczen();
                    }
                } else {
                    Wyszukaj();

                    if ($_POST['KlasaUczen'] == 'klasa') {
                        echo "<button class='buttonGlobal buttonUwagi dodajButton' name='dodajPrzycisk'>dodaj</button>";
                    }

                    if ($_POST['KlasaUczen'] == 'uczen') {
                        echo "<button class='buttonGlobal buttonUwagi dodajButton' name='dodajPrzycisk'>dodaj</button>";
                        UczenUwagi();
                    }
                }
            } else {
                echo "<br>Wybierz klase lub ucznia";
            }
        ?>
    </form>
</body>
</html>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
