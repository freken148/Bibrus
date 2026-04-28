<?php 
    require "core/idk.php";
    require "functions/terminarz_functions.php";
    require "core/SelectUczenKlasa.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminarz</title>
</head>
<body>
    <form method="POST" action="terminarz.php">
        <select name="wybrana_klasa" onchange='this.form.submit()'>
            <?php
                SelectKlasy();
            ?>
        </select>
        <button name="glowna">Do głównej</button> 
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