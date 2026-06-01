<?php 
    require "core/idk.php";
    require "functions/plan_lekcji_functions.php";
    require "core/SelectUczenKlasa.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan lekcji</title>
</head>
<body class="bodyGlobal bodyPlanLekcji">
    <form class="formGlobal formPlanLekcji" method="POST" action="plan_lekcji.php">
        <select class="selectGlobal selectPlanLekcji" name="wybrana_klasa" onchange='this.form.submit()'>
            <?php
                SelectKlasy();
            ?>
        </select>
        <button class="buttonGlobal buttonPlanLekcji glownaButton" name="glowna">Do głównej</button> 
        <?php
            Init();
            ShowPlan();
        ?>
    </form>
</body>
</html>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
