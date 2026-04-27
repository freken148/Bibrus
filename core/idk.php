<?php
    // idk бо я не їбу як це назвати, теоретично це core.php але фігня типу core/core.php хуйово виглядає
    isset($_POST['glowna']) ? header('Location: glowna.php') : NULL;
    session_start();
    require "core/db.php";
    require "core/SessionCheck.php";
?>