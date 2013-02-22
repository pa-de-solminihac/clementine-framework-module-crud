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
    <a class="clementine_crud-list-createbutton" href="<?php echo __WWW__ . '/' . $data['class'] . '/create'; ?>">Nouveau</a>
<?php
    }
    if (!(isset($data['hidden_sections']['xlsbutton']) && ($data['hidden_sections']['xlsbutton']))) {
?>
    <a class="clementine_crud-list-xlsbutton" href="<?php echo $request->FULLURL . '&export_xls&sEcho=1'; ?>">Export XLS</a>
<?php
    }
}
// contenu json
if (isset($data['return_json']) && $data['return_json']) {
    if (isset($data['export_xls'])) {
        if (!function_exists('clementine_crud_filter_xls')) {
            // filtre les contenus avant de les passer au fichier Excel
            function clementine_crud_filter_xls(&$string, $key, $header)
            {
                // si header, supprime les colonnes qui ne sont pas dans le header
                if (!isset($header[$key]['title'])) {
                    $string = null;
                }
                if (isset($header[$key]['type'])) {
                    switch ($header[$key]['type']) {
                        case 'int':
                            $string = trim(strip_tags($string));
                            $string = (int) $string;
                            break;
                        case 'float':
                            $string = trim(strip_tags($string));
                            $string = (float) $string;
                            break;
                        case 'html':
                            $string = html_entity_decode($string, ENT_QUOTES, mb_internal_encoding());
                            $string = trim(strip_tags($string));
                            break;
                        default:
                            $string = trim(strip_tags($string));
                            break;
                    }
                } else {
                    $string = trim(strip_tags($string));
                }
            }
        }
        $header = array();
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
                $header_part = array();
                if (isset($data['metas']['title_mapping'][$tablefield])) {
                    $header_part['title'] = $data['metas']['title_mapping'][$tablefield];
                } else {
                    $header_part['title'] = ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
                }
                if ($fieldmeta['type'] != 'custom_field') {
                    $header_part['type'] = $fieldmeta['type'];
                }
                $header[] = $header_part;
            }
        }
        $donnees = $output['aaData'];
        array_walk_recursive($donnees, 'clementine_crud_filter_xls', $header);
        $header_titles = array();
        foreach ($header as $key => $val) {
            $header_titles[$key] = trim(strip_tags($val['title']));
        }
        $ns->matrix2xls('export.xls', $donnees, $header_titles);
    } else {
        echo json_encode($output);
    }
}
?>
