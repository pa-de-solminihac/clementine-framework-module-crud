<?php
$ns = $this->getModel('fonctions');
if (!(isset($data['alldata']['hidden_sections']['savebutton']) && ($data['alldata']['hidden_sections']['savebutton']))) {
?>
<input class="clementine_crud-update-savebutton savebutton" type="submit" value="Enregistrer" />
<?php
}
if (!(isset($data['alldata']['hidden_sections']['backbutton']) && ($data['alldata']['hidden_sections']['backbutton']))) {
?>
<a class="clementine_crud-update-backbutton backbutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class']; ?>">Retour</a>
<?php
}
if (!(isset($data['alldata']['hidden_sections']['delbutton']) && ($data['alldata']['hidden_sections']['delbutton']))) {
?>
<a class="clementine_crud-update-delbutton delbutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class'] . '/delete?' . $ns->htmlentities($data['current_key']); ?>">Supprimer</a>
<?php
}
