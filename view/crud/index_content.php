<?php
$ns = $this->getModel('fonctions');
$config = $this->getModuleConfig();
// tableau pour la ligne de titre
$firstrow = array();
foreach ($data['fields'] as $tablefield => $fieldmeta) {
    $firstrow[$tablefield] = $fieldmeta;
}
if (!(isset($data['return_json']) && $data['return_json'])) {
    if ($config['table_layout_fixed']) {
        //calcul des tailles par défaut des colonnes
        $nb_cols = 0;
        //la colonne Actions fait au moins 1%
        $total_width = 1;
        foreach ($firstrow as $rowfield => $row) {
            $hidden = 0;
            if (isset($data['metas']['hidden_fields'][$rowfield]) && $data['metas']['hidden_fields'][$rowfield]) {
                $hidden = 1;
            }
            if (!$hidden) {
                ++$nb_cols;
                $maxsize = 0;
                if (!empty($firstrow[$rowfield]['size'])) {
                    $maxsize = $firstrow[$rowfield]['size'];
                } elseif (!empty($firstrow[$rowfield]['fieldvalues'])) {
                    $vals = $firstrow[$rowfield]['fieldvalues'];
                    $maxsize = 0;
                    foreach ($vals as $val) {
                        $maxsize = max($maxsize, $ns->strlen($val));
                    }
                }
                $total_width += $maxsize;
                $firstrow[$rowfield]['calculated_maxsize'] = $maxsize;
            }
        }
        $total_allowed_width = 0;
        foreach ($firstrow as $rowfield => $row) {
            $default_width = 100 / $nb_cols;
            if (isset($firstrow[$rowfield]['calculated_maxsize'])) {
                $calculated_width = $firstrow[$rowfield]['calculated_maxsize'] * 100 / $total_width;
                // arbitraire : une colonne ne doit pas prendre plus d'un certain pourcentage de la taille par défaut d'une colonne
                $calculated_width = max($calculated_width, ($default_width / 1.5));
                $calculated_width = min($calculated_width, ($default_width * 1.5));
                //echo $firstrow[$rowfield]['calculated_maxsize'] . '<br />' ;
                $firstrow[$rowfield]['calculated_width'] = $calculated_width;
                $total_allowed_width += $calculated_width;
            }
        }
        // on répartit à nouveau les tailles calculées sur un pourcentage
        //foreach ($firstrow as $rowfield => $row) {
            //if (isset($firstrow[$rowfield]['calculated_width'])) {
                //$firstrow[$rowfield]['calculated_width'] = $firstrow[$rowfield]['calculated_width'] * 100 / $total_allowed_width;
            //}
        //}
    }

?>
<div class="clementine_crud-list_div <?php echo implode(' ', $data['more_classes_wrap']); ?>">
    <table class="clementine_crud-list_table clementine-dataTables <?php echo implode(' ', $data['more_classes_table']); ?>" style="<?php
    if ($config['table_layout_fixed']) {
        echo 'table-layout: fixed';
    } ?>">
        <colgroup>
<?php
// contenu json
} else {
    $iTotal = $data['nb_total_values'];
    $iFilteredTotal = $iTotal;
    $output = array(
        "sEcho" => $request->get('int', 'sEcho'),
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
    <col class="clementine_crud-list_table_col_<?php echo $field_class; ?>" style="<?php
    if ($config['table_layout_fixed']) {
        echo 'width: ' . $firstrow[$tablefield]['calculated_width'] . '%';
    } ?>" />
<?php
    }
}
if (!(isset($data['return_json']) && $data['return_json'])) {
    if (empty($data['hidden_sections']['actions'])) {
?>
            <col class="clementine_crud-list_table_col_actions" />
<?php
    }
?>
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
    if (empty($data['hidden_sections']['actions'])) {
?>
                <th class="clementine_crud-list_table_th_actions no_autoclick"><?php
        //xls export button
        if (empty($data['hidden_sections']['xlsbutton'])) {
            $button_label = $data["button_label_xls"];
            $href = $ns->add_param($request->FULLURL, 'export_xls');
            $href = $ns->add_param($href, 'sEcho', '1');
            foreach ($data['url_parameters'] as $key => $val) {
                $href = $ns->add_param($href, $key, $val, 1);
            }
?>
    <a class="clementine_crud-xlsbutton clementine_crud-list-xlsbutton <?php echo implode(' ', $data['more_classes_xlsbutton']); ?>" href="<?php echo $href; ?>" title="<?php echo $button_label; ?>">
        <i class="glyphicon glyphicon-download"></i><span class="text-hide"><?php echo $button_label; ?></span>
    </a>
<?php
        }
?></th>
<?php
    }
?>
            </tr>
        </thead>
        <tbody>
<?php
}
// valeurs
foreach ($data['values'] as $current_key => $ligne) {
    $out = $this->getBlockHtml(
        $data['class'] . '/index_rows',
        array(
            'current_key' => $current_key,
            'ligne' => $ligne,
            'alldata' => $data
        ),
        $request
    );
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
    if (!(isset($data['return_json']) && $data['return_json'])) {
        $button_label = $data["button_label_create"];
        //create button
        if (empty($data['hidden_sections']['createbutton'])) {
            $href = __WWW__ . '/' . $data['class'] . '/create';
            if (isset($data['button_url_create'])) {
                $href = $data['button_url_create'];
            }
            foreach ($data['url_parameters'] as $key => $val) {
                $href = $ns->add_param($href, $key, $val, 1);
            }
?>
    <a class="clementine_crud-createbutton clementine_crud-list-createbutton <?php echo implode(' ', $data['more_classes_createbutton']); ?>" href="<?php echo $href; ?>" title="<?php echo $button_label; ?>">
        <i class="glyphicon glyphicon-plus"></i><span class="text-hide"><?php echo $button_label; ?></span>
    </a>
<?php
        }
    }
?>
</div>
<?php
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
        // $ns->matrix2xls('export.xls', $donnees, $header_titles);
        echo serialize(array(
            'filename' => 'export.xls',
            'donnees' => $donnees,
            'header_titles' => $header_titles
        ));
    } else {
        echo json_encode($output);
    }
}
