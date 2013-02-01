<?php
$ns = $this->getModel('fonctions');
// tableau pour la ligne de titre
$firstrow = array();
foreach ($data['fields'] as $tablefield => $fieldmeta) {
    $firstrow[$tablefield] = $fieldmeta;
}
if (!(isset($data['return_json']) && $data['return_json'])) {
?>
    <table class="clementine_crud-list_table clementine-dataTables">
        <colgroup>
<?php
// contenu json
} else {
    $iTotal = $data['nb_total_values'];
    $iFilteredTotal = $iTotal;
    $output = array(
        "sEcho" => (int) $_GET['sEcho'],
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
}
// colonnes pour skinner les titres
foreach ($firstrow as $tablefield => $val) {
    $fieldmeta = $data['fields'][$tablefield];
    $field_name = $tablefield;
    $field_class = $tablefield;
    if ($fieldmeta['type'] != 'custom_field' && strpos($tablefield, '.')) {
        list ($table, $field) = explode('.', $tablefield, 2);
        $field_class = $table . '-' . $field;
        $field_name = $field;
    }
    $hidden = 0;
    if (isset($data['metas']['hidden_fields'][$tablefield]) && $data['metas']['hidden_fields'][$tablefield]) {
        $hidden = 1;
    }
    if (!$hidden && !(isset($data['return_json']) && $data['return_json'])) {
?>
        <col class="clementine_crud-list_table_col_<?php echo $field_class; ?>" />
<?php
    }
}
if (!(isset($data['return_json']) && $data['return_json'])) {
?>
            <col class="clementine_crud-list_table_col_actions" />
        </colgroup>
        <thead>
            <tr>
<?php
}
// titres
if (!(isset($data['return_json']) && $data['return_json'])) {
    foreach ($firstrow as $tablefield => $val) {
        $fieldmeta = $data['fields'][$tablefield];
        $field_name = $tablefield;
        $field_class = $tablefield;
        if ($fieldmeta['type'] != 'custom_field' && strpos($tablefield, '.')) {
            list ($table, $field) = explode('.', $tablefield, 2);
            $field_class = $table . '-' . $field;
            $field_name = $field;
        }
        $hidden = 0;
        if (isset($data['metas']['hidden_fields'][$tablefield]) && $data['metas']['hidden_fields'][$tablefield]) {
            $hidden = 1;
        }
        if (!$hidden) {
?>
            <th class="clementine_crud-list_table_th_<?php echo $field_class; ?>">
<?php
            if (isset($data['metas']['title_mapping'][$tablefield])) {
                echo $data['metas']['title_mapping'][$tablefield];
            } else {
                echo ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
            }
?>
            </th>
<?php
        }
    }
?>
                <th class="clementine_crud-list_table_th_actions no_autoclick">Actions</th>
            </tr>
        </thead>
        <tbody>
<?php
}
// valeurs
foreach ($data['values'] as $current_key => $ligne) {
    $out = $this->getBlockHtml($data['class'] . '/index_rows', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
    if (!(isset($data['return_json']) && $data['return_json'])) {
        echo $out;
    } else {
        $output['aaData'][] = json_decode($out);
    }
}
if (!(isset($data['return_json']) && $data['return_json'])) {
?>
        </tbody>
    </table>
<?php
}
if (!(isset($data['return_json']) && $data['return_json'])) {
    if (!(isset($data['hidden_sections']['createbutton']) && ($data['hidden_sections']['createbutton']))) {
?>
    <p>
        <a class="clementine_crud-list-createbutton" href="<?php echo __WWW__ . '/' . $data['class'] . '/create'; ?>">Nouveau</a>
    </p>
<?php
    }
}
// contenu json
if (isset($data['return_json']) && $data['return_json']) {
    echo json_encode($output);
}
?>
