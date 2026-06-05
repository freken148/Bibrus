<?php
    require "core/idk.php";
    require "functions/terminarz_functions.php";
    require "core/SelectUczenKlasa.php";

    if (isset($_POST['TerAdd'])) {
        terminarzDodaj();
        header('Location: terminarz.php');
        exit;
    }

    if (isset($_POST['terminarzREMOVE'])) {
        terminarzRemove();
        header('Location: terminarz.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminarz</title>
    <?php require "core/head.php"; ?>
</head>
<body class="bodyGlobal bodyTerminarz">
    <form class="formGlobal formTerminarz" method="POST" action="terminarz.php">
        <div class="toolbarTerminarz">
            <select class="selectGlobal selectTerminarz" name="wybrana_klasa" onchange='this.form.submit()'>
                <?php
                    SelectKlasy();
                ?>
            </select>
            <select class="selectGlobal selectTerminarz" name="wybrany_miesiac" onchange='this.form.submit()'>
                <?php
                    miesiacSelect();
                ?>
            </select>
            <select class="selectGlobal selectTerminarz" name="wybrany_rok" onchange='this.form.submit()'>
                <?php
                    rokSelect();
                ?>
            </select>
            <button class="buttonGlobal buttonTerminarz glownaButton" name="glowna">Do głównej</button>
        </div>
        <?php
            Init();
            ShowTerminarz();
        ?>
    </form>
</body>
</html>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
