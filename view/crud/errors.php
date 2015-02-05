<?php
if (!empty($data['errors']) && !(isset($data['hidden_sections']['errors']) && ($data['hidden_sections']['errors']))) {
    if ($request->AJAX) {
        echo json_encode($data['errors']);
    } else {
        // si requete classique : affichage des erreurs dans la page
?>
<ul class="errors <?php
    if (isset($data['formtype'])) {
        echo $data['formtype'];
    }
?>">
<?php
        $message = "Merci de vérifier les points suivants : ";
        if (!empty($data['errors']['error_title'])) {
            $message = $data['errors']['error_title'];
            unset($data['errors']['error_title']);
            $message .= "</br > \r\n";
        }
?>
    <li class="errtitle err_error_title"><?php echo $message; ?></li>
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
