<?php
$data['formtype'] = 'create';
$this->getBlock($data['class'] . '/errors', $data);
foreach ($data['values'] as $current_key => $ligne) {
?>
<form class="clementine_crud-create_form clementine_crud-form" action="" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <table class="clementine_crud-create_table">
<?php
    if (!(isset($data['hidden_sections']['thead']) && ($data['hidden_sections']['thead']))) {
?>
        <thead>
            <tr>
<?php
        if (!(isset($data['hidden_sections']['names']) && ($data['hidden_sections']['names']))) {
?>
                <th class="clementine_crud-<?php echo $data['formtype']; ?>-title_column">Champ</th>
<?php
        }
        if (!(isset($data['hidden_sections']['values']) && ($data['hidden_sections']['values']))) {
?>
                <th class="clementine_crud-<?php echo $data['formtype']; ?>-value_column">Valeur</th>
<?php
        }
?>
            </tr>
        </thead>
<?php
    }
?>
        <tbody>
<?php
if (is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => $ligne) {
        $this->getBlock($data['class'] . '/create_fields', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
    }
}
?>
        </tbody>
    </table>
<?php
    $this->getBlock($data['class'] . '/create_actions', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
?>
</form>
<?php
}
