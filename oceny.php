<?php 
    require "core/idk.php";
    require "functions/oceny_functions.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oceny</title>
</head>
<body>   
    <form id="forma1" method="POST" action="oceny.php">
        <?php 
            require "core/frekOcenyUni.php";

            if (isset($_POST['dodajPrzycisk'])) {
                DodajOceny();
            }

            if (isset($_POST['usun'])) {
                UsunOceny();
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
                        echo "<button name='dodajPrzycisk'>dodaj</button>";
                    }

                    if ($_POST['KlasaUczen'] == 'uczen') {
                        UczenOceny();
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
