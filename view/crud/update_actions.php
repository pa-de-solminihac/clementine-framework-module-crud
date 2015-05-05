<?php
$ns = $this->getModel('fonctions');
$formtype = 'create';
if (!empty($data['alldata']['formtype'])) {
    $formtype = $data['alldata']['formtype'];
}
// annuler
if (!(isset($data['alldata']['hidden_sections']['backbutton']) && ($data['alldata']['hidden_sections']['backbutton']))) {
    $button_label = $data['alldata']["button_label_back"];
    $href = __WWW__ . '/' . $data['alldata']['class'];
    if(isset($data['alldata']['button_url_back']) && !empty($data['alldata']['button_url_back'])) {
        $href = $data['alldata']['button_url_back'];
    }
    foreach ($data['alldata']['url_parameters'] as $key => $val) {
        $href = $ns->add_param($href, $key, $val, 1);
    }
?>
<a class="clementine_crud-backbutton clementine_crud-<?php echo $formtype; ?>-backbutton backbutton <?php echo implode(' ', $data['alldata']['more_classes_backbutton']); ?>"
    href="<?php echo $href; ?>"
    title="<?php echo $button_label; ?>">
    <i class="glyphicon glyphicon-arrow-left"></i><span class="text-hide"><?php echo $button_label; ?></span>
</a>
<?php
}
// enregistrer
if (!(isset($data['alldata']['hidden_sections']['savebutton']) && ($data['alldata']['hidden_sections']['savebutton']))) {
    $button_label = $data['alldata']["button_label_save"];
?>
<button
    type="submit"
    class="clementine_crud-savebutton clementine_crud-<?php echo $formtype; ?>-savebutton savebutton <?php echo implode(' ', $data['alldata']['more_classes_savebutton']); ?>"
    title="<?php echo $button_label; ?>">
    <i class="glyphicon glyphicon-ok"></i><span class="text-hide"><?php echo $button_label; ?></span>
</button>
<?php
}
// supprimer
if ($formtype == 'update') {
    $button_label = $data['alldata']["button_label_del"];
    if (!(isset($data['alldata']['hidden_sections']['delbutton']) && ($data['alldata']['hidden_sections']['delbutton']))) {
        $href = __WWW__ . '/' . $data['alldata']['class'] . '/delete?' . $ns->htmlentities($data['current_key']);
        if(isset($data['alldata']['button_url_del']) && !empty($data['alldata']['button_url_del'])) {
            $href = $data['alldata']['button_url_del'];
        }
        foreach ($data['alldata']['url_parameters'] as $key => $val) {
            $href = $ns->add_param($href, $key, $val, 1);
        }
?>
<a class="clementine_crud-delbutton clementine_crud-<?php echo $formtype; ?>-delbutton delbutton <?php echo implode(' ', $data['alldata']['more_classes_delbutton']); ?>"
    href="<?php echo $href; ?>"
    title="<?php echo $button_label; ?>">
    <i class="glyphicon glyphicon-trash"></i><span class="text-hide"><?php echo $button_label; ?></span>
</a>
<?php
    }
}
