<?php
$ns = $this->getModel('fonctions');
foreach ($data['alldata']['fields'] as $tablefield => $metas) {
    if (array_key_exists($tablefield, $data['ligne'])) { // array_key_exists !== isset
        $val = $data['ligne'][$tablefield];
        $fieldmeta = $data['alldata']['fields'][$tablefield];
        $field_class = $tablefield;
        $field_name = $tablefield;
        if ($fieldmeta['type'] != 'custom_field' && strpos($tablefield, '.')) {
            list($table, $field) = explode('.', $tablefield, 2);
            $field_class = $table . '-' . $field;
            $field_name = $field;
        }
        $hidden = 0;
        if (isset($data['alldata']['metas']['hidden_fields'][$tablefield]) && $data['alldata']['metas']['hidden_fields'][$tablefield]) {
            $hidden = 1;
        }
        // opening wrapper tag
        if (!empty($data['alldata']['wrappers']['open'][$field_class])) {
            $reverse_wrappers = array_reverse($data['alldata']['wrappers']['open'][$field_class]);
            foreach ($reverse_wrappers as $wrapper) {
                if ($wrapper['opening_block']) {
                    //echo $wrapper['opening_block'] . PHP_EOL;
                    $this->getBlock($wrapper['opening_block'], $data, $request);
                }
            }
        }
        if (!$hidden) {
?>
            <div class="clementine_crud-read-row-<?php echo $field_class; ?> <?php echo implode(' ', $data['alldata']['more_classes_field_wrap']); ?>">
<?php
            $label_open = '';
            $label_close = '';
            if (!(isset($data['alldata']['hidden_sections']['names']) && ($data['alldata']['hidden_sections']['names']))) {
                // because plupload
                //if ($mapping != 'file') {
                $label_open.= '<label for="' . $field_class . '" class="clementine_crud-' . $data['alldata']['formtype'] . '-title_column ';
                if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                    //$label_open.= 'clementine_crud-' . $data['alldata']['formtype'] . '-required_field';
                }
                $label_open.= ' ' . implode(' ', $data['alldata']['more_classes_field_key']) . '">';
                //}
                if (isset($data['alldata']['metas']['title_mapping'][$tablefield])) {
                    $label_open.= $data['alldata']['metas']['title_mapping'][$tablefield];
                } else {
                    $label_open.= ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
                }
                $label_open.= PHP_EOL;
                //if ($mapping != 'file') {
                $label_close = '</label>';
                //}
            }
            if (!(isset($data['alldata']['hidden_sections']['values']) && ($data['alldata']['hidden_sections']['values']))) {
                $valueclasses = 'clementine_crud-' . $data['alldata']['formtype'] . '-value_column ';
                $valueclasses.= 'clementine_crud-' . $data['alldata']['formtype'] . '_type-' . $fieldmeta['type'];
                $valueclasses.= ' ' . implode(' ', $data['alldata']['more_classes_field_val']) . ' ';
                $div_open = '<div id="' .  $field_class . '" class="' . $valueclasses . ' ">'; 
                $div_close = '</div>';
                $mapping = '';
                if (isset($data['alldata']['mapping'][$fieldmeta['type']])) {
                    $mapping = $data['alldata']['mapping'][$fieldmeta['type']];
                }
                if ($this->canGetBlock($data['alldata']['class'] . '/read_fields/custom_' . $tablefield)) {
                    $this->getBlock($data['alldata']['class'] . '/read_fields/custom_' . $tablefield, array('tablefield' => $tablefield, 'ligne' => $data['ligne'], 'data' => $data['alldata']), $request);
                } else {
                    if ($mapping == 'html') {
                        echo $label_open;
                        echo $label_close;
                        echo $div_open;
                        echo $data['ligne'][$tablefield];
                        echo $div_close;
                    } else {
                        switch ($mapping) {
                            case 'checkbox':
                                echo $label_open;
                                echo $label_close;
                                echo $div_open;
                                if ($data['ligne'][$tablefield]) {
                                    echo '✓';
                                } else {
                                    echo '✕';
                                }
                                echo $div_close;
                                break;
                            case 'file':
                                echo $label_open;
                                echo $label_close;
                                echo $div_open;
                                $thisdata = array(
                                    'ligne' => $data['ligne'],
                                    'tablefield' => $tablefield
                                );
                                $this->getBlock(
                                    $data['alldata']['class'] . '/read_file',
                                    array(
                                        'data' => $thisdata,
                                        'alldata' => $data['alldata']
                                    ),
                                    $request
                                );
                                echo $div_close;
                                break;
                            default:
                                echo $label_open;
                                echo $label_close;
                                echo $div_open;
                                if (!empty($fieldmeta['fieldvalues']) && isset($fieldmeta['fieldvalues'][$data['ligne'][$tablefield]])) {
                                    echo $fieldmeta['fieldvalues'][$data['ligne'][$tablefield]];
                                } else {
                                    echo $ns->htmlentities($data['ligne'][$tablefield]);
                                }
                                echo $div_close;
                                break;
                        }
                    }
                }
            }
?>
            </div>
<?php
        }
        // closing wrapper tag
        if (!empty($data['alldata']['wrappers']['close'][$field_class])) {
            $wrappers = $data['alldata']['wrappers']['close'][$field_class];
            foreach ($wrappers as $wrapper) {
                if ($wrapper['closing_block']) {
                    //echo $wrapper['closing_block'] . PHP_EOL;
                    $this->getBlock($wrapper['closing_block'], $data, $request);
                }
            }
        }
    }
}
