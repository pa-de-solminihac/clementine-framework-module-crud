<?php
if (count($data['errors']) && !(isset($data['hidden_sections']['errors']) && ($data['hidden_sections']['errors']))) {
    if ($request->AJAX) {
        // si requete AJAX : affichage des erreurs dans une alerte JS
        $message = "Merci de vérifier les points suivants : \r\n- ";
        $message .= implode("\r\n- ", $data['errors']);
        echo $message;
    } else {
        // si requete classique : affichage des erreurs dans la page
?>
<ul class="errors <?php
    if (isset($data['formtype'])) {
        echo $data['formtype'];
    }
?>">
<?php
    foreach ($data['errors'] as $key => $errmsg) {
?>
    <li class="errmsg err_<?php echo $key; ?>"><?php echo $errmsg; ?></li>
<?php
    }
?>
</ul>
<?php
    }
}
?>
