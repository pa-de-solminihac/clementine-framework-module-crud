<?php
$ns = $this->getModel('fonctions');
foreach ($data['alldata']['fields'] as $tablefield => $metas) {
    if (array_key_exists($tablefield, $data['ligne'])) { // array_key_exists !== isset
        $val = $data['ligne'][$tablefield];
        $fieldmeta = $data['alldata']['fields'][$tablefield];
        $field_name = $tablefield;
        $field_class = $tablefield;
        if ($fieldmeta['type'] != 'custom_field' && strpos($tablefield, '.')) {
            list ($table, $field) = explode('.', $tablefield, 2);
            $field_class = $table . '-' . $field;
            $field_name = $field;
        }
        $hidden = 0;
        if (isset($data['alldata']['metas']['hidden_fields'][$tablefield]) && $data['alldata']['metas']['hidden_fields'][$tablefield]) {
            $hidden = 1;
        }
        if (!$hidden) {
?>
            <tr class="clementine_crud-read-row-<?php echo $field_class; ?>">
<?php
            if (!(isset($data['alldata']['hidden_sections']['names']) && ($data['alldata']['hidden_sections']['names']))) {
?>
                <td class="clementine_crud-read-title_column"><?php
                if (isset($data['alldata']['metas']['title_mapping'][$tablefield])) {
                    echo $data['alldata']['metas']['title_mapping'][$tablefield];
                } else {
                    echo ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
                }
?></td>
<?php
            }
            if (!(isset($data['alldata']['hidden_sections']['values']) && ($data['alldata']['hidden_sections']['values']))) {
?>
                <td class="clementine_crud-read-value_column"><?php
                $mapping = '';
                if (isset($data['alldata']['mapping'][$fieldmeta['type']])) {
                    $mapping = $data['alldata']['mapping'][$fieldmeta['type']];
                }
                if ($this->canGetBlock($data['alldata']['class'] . '/read_fields/custom_' . $tablefield)) {
                    $this->getBlock($data['alldata']['class'] . '/read_fields/custom_' . $tablefield, array('tablefield' => $tablefield, 'ligne' => $data['ligne'], 'data' => $data['alldata']));
                } else {
                    if ($mapping == 'html') {
                        echo $data['ligne'][$tablefield];
                    } else {
                        switch ($mapping) {
                            case 'checkbox':
                                if ($data['ligne'][$tablefield]) {
                                    echo '✓';
                                } else {
                                    echo '✕';
                                }
                                break;
                            case 'file':
                                $thisdata = array(
                                    'ligne' => $data['ligne'],
                                    'tablefield' => $tablefield
                                );
                                $this->getBlock($data['alldata']['class'] . '/read_file', array('data' => $thisdata, 'alldata' => $data['alldata']));
                                break;
                            default:
                                if (!empty($fieldmeta['fieldvalues']) && isset($fieldmeta['fieldvalues'][$data['ligne'][$tablefield]])) {
                                    echo $fieldmeta['fieldvalues'][$data['ligne'][$tablefield]];
                                } else {
                                    echo $ns->htmlentities($data['ligne'][$tablefield]);
                                }
                                break;
                        }
                    }
                }
?></td>
<?php
            }
?>
            </tr>
<?php
        }
    }
}
