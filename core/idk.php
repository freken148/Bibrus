<?php
    isset($_POST['glowna']) ? header('Location: glowna.php') : NULL;
    session_start();
    echo "<link href='style.css' rel='stylesheet' />";
    require "core/db.php";
    require "core/SessionCheck.php";
?>