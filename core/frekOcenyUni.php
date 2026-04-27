<?php
    require "SelectUczenKlasa.php";
?>
<label>
    Klasa
    <input onchange="this.form.submit()" name="KlasaUczen" type="radio" value="klasa" <?php if (isset($_POST['KlasaUczen']) && $_POST['KlasaUczen'] == 'klasa') echo 'checked'; ?>>
</label>
<label>
    Uczen
    <input onchange="this.form.submit()" name="KlasaUczen" type="radio" value="uczen" <?php if (isset($_POST['KlasaUczen']) && $_POST['KlasaUczen'] == 'uczen') echo 'checked'; ?>>
</label>
<select name="wybrana_klasa" onchange='this.form.submit()'>
    <?php
        SelectKlasy();
    ?>
</select>
<select name="wybrany_uczen" onchange='this.form.submit()'>
    <?php
        SelectUcznie();
    ?>
</select>
<label>
    Według przedmiotów
    <input onchange='this.form.submit()' type="checkbox" value="WedlugPrzedmiotowChecked" name="WedlugPrzedmiotow" <?php if (isset($_POST['WedlugPrzedmiotow']) && $_POST['WedlugPrzedmiotow'] == 'WedlugPrzedmiotowChecked') echo 'checked'; ?>>
</label>
<button name="glowna">Do głównej</button>  