<?php
    isset($_POST['glowna']) ? header('Location: glowna.php') : NULL;
    session_start();
    require "core/db.php";
    require "core/SessionCheck.php";
?>