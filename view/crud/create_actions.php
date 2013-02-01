<?php
    if (!(isset($data['alldata']['hidden_sections']['savebutton']) && ($data['alldata']['hidden_sections']['savebutton']))) {
?>
<input class="clementine_crud-create-savebutton savebutton" type="submit" value="Enregistrer" />
<?php
    }
    if (!(isset($data['alldata']['hidden_sections']['backbutton']) && ($data['alldata']['hidden_sections']['backbutton']))) {
?>
<a class="clementine_crud-create-backbutton backbutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class']; ?>">Retour</a>
<?php
    }
?>
