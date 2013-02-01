<?php
$ns = $this->getModel('fonctions');
if (!(isset($data['alldata']['hidden_sections']['readbutton']) && ($data['alldata']['hidden_sections']['readbutton']))) {
?>
                    <a class="clementine_crud-list-readbutton readbutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class'] . '/read?' . $ns->htmlentities($data['current_key']); ?>">Afficher</a>
<?php
}
if (!(isset($data['alldata']['hidden_sections']['updatebutton']) && ($data['alldata']['hidden_sections']['updatebutton']))) {
?>
                    <a class="clementine_crud-list-updatebutton updatebutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class'] . '/update?' . $ns->htmlentities($data['current_key']); ?>">Modifier</a>
<?php
}
// masqué par défaut
if (!(isset($data['alldata']['hidden_sections']['duplicatebutton']) && ($data['alldata']['hidden_sections']['duplicatebutton']))) {
?>
                    <a class="clementine_crud-list-duplicatebutton duplicatebutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class'] . '/create?duplicate=1&' . $ns->htmlentities($data['current_key']); ?>">Dupliquer</a>
<?php
}
if (!(isset($data['alldata']['hidden_sections']['delbutton']) && ($data['alldata']['hidden_sections']['delbutton']))) {
?>
                    <a class="clementine_crud-list-delbutton delbutton" href="<?php echo __WWW__ . '/' . $data['alldata']['class'] . '/delete?' . $ns->htmlentities($data['current_key']); ?>">Supprimer</a>
<?php
}
?>
