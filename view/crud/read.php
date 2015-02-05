<?php
$this->getBlock($data['class'] . '/errors', $data);
if (is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => $ligne) {
?>
<table class="clementine_crud-read">
<?php
if (!(isset($data['hidden_sections']['thead']) && ($data['hidden_sections']['thead']))) {
?>
    <thead>
        <tr>
<?php
if (!(isset($data['hidden_sections']['names']) && ($data['hidden_sections']['names']))) {
?>
            <th class="clementine_crud-read-title_column">Champ</th>
<?php
}
if (!(isset($data['hidden_sections']['values']) && ($data['hidden_sections']['values']))) {
?>
            <th class="clementine_crud-read-value_column">Valeur</th>
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
$this->getBlock($data['class'] . '/read_fields', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
?>
    </tbody>
</table>
<?php
    }
}
