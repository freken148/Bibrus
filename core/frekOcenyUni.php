<?php
    require "SelectUczenKlasa.php";
?>
<div class="toolbarFrekwencja">
    <label class="labelGlobal labelFrekwencja">
        <input class="inputGlobal inputFrekwencja radioGlobal" onchange="this.form.submit()" name="KlasaUczen" type="radio" value="klasa" <?php if (isset($_POST['KlasaUczen']) && $_POST['KlasaUczen'] == 'klasa') echo 'checked'; ?>>
        Klasa
    </label>
    <label class="labelGlobal labelFrekwencja">
        <input class="inputGlobal inputFrekwencja radioGlobal" onchange="this.form.submit()" name="KlasaUczen" type="radio" value="uczen" <?php if (isset($_POST['KlasaUczen']) && $_POST['KlasaUczen'] == 'uczen') echo 'checked'; ?>>
        Uczen
    </label>
    <select class="selectGlobal selectFrekwencja" name="wybrana_klasa" onchange='this.form.submit()'>
        <?php
            SelectKlasy();
        ?>
    </select>
    <select class="selectGlobal selectFrekwencja" name="wybrany_uczen" onchange='this.form.submit()'>
        <?php
            SelectUcznie();
        ?>
    </select>
    <label class="labelGlobal labelFrekwencja">
        <input class="inputGlobal inputFrekwencja checkboxGlobal" onchange='this.form.submit()' type="checkbox" value="WedlugPrzedmiotowChecked" name="WedlugPrzedmiotow" <?php if (isset($_POST['WedlugPrzedmiotow']) && $_POST['WedlugPrzedmiotow'] == 'WedlugPrzedmiotowChecked') echo 'checked'; ?>>
        Według przedmiotów
    </label>
    <button class="buttonGlobal buttonFrekwencja glownaButton" name="glowna">Do głównej</button>
</div>
