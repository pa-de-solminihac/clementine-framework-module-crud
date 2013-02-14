<?php
$ns = $this->getModel('fonctions');
foreach ($data['alldata']['fields'] as $tablefield => $metas) {
    if (array_key_exists($tablefield, $data['ligne'])) { // array_key_exists !== isset
        $val = $data['ligne'][$tablefield];
        $fieldmeta = $data['alldata']['fields'][$tablefield];
        $field_class = $tablefield;
        $field_name = $tablefield;
        if ($fieldmeta['type'] != 'custom_field' && strpos($tablefield, '.')) {
            list ($table, $field) = explode('.', $tablefield, 2);
            $field_class = $table . '-' . $field;
            $field_name = $field;
        }
        $htmlval = $ns->htmlentities($val);
        $hidden = 0;
        if (isset($data['alldata']['metas']['hidden_fields'][$tablefield]) && $data['alldata']['metas']['hidden_fields'][$tablefield]) {
            $hidden = 1;
        }
        $class = '';
        if (isset($metas['class'])) {
            $class = $metas['class'];
        }
        $mapping = '';
        if (isset($data['alldata']['mapping'][$fieldmeta['type']])) {
            $mapping = $data['alldata']['mapping'][$fieldmeta['type']];
        }
        if (!$hidden) {
            if ($this->canGetBlock($data['alldata']['class'] . '/update_fields/custom_' . $tablefield)) {
                $this->getBlock($data['alldata']['class'] . '/update_fields/custom_' . $tablefield, array('tablefield' => $tablefield, 'ligne' => $data['ligne'], 'data' => $data['alldata']));
            } else {
                if ($mapping == 'hidden') {
?>
    <input type="hidden" id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>" value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval); ?>" />
<?php
                } else {
?>
        <tr class="clementine_crud-<?php echo $data['alldata']['formtype']; ?>-row-<?php echo $field_class; ?>">
<?php
                    if (!(isset($data['alldata']['hidden_sections']['names']) && ($data['alldata']['hidden_sections']['names']))) {
?>
            <td class="clementine_crud-<?php echo $data['alldata']['formtype']; ?>-title_column">
<?php
                        // because plupload
                        if ($mapping != 'file') {
?>
                <label for="<?php echo $field_class; ?>">
<?php
                        }
                        if (isset($data['alldata']['metas']['title_mapping'][$tablefield])) {
                            echo $data['alldata']['metas']['title_mapping'][$tablefield];
                        } else {
                            echo ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
                        }
                        if ($mapping != 'file') {
?>
                </label>
<?php
                        }
?>
            </td>
<?php
                    }
                    if (!(isset($data['alldata']['hidden_sections']['values']) && ($data['alldata']['hidden_sections']['values']))) {
?>
            <td class="clementine_crud-<?php echo $data['alldata']['formtype']; ?>-value_column">
<?php
                        switch ($mapping) {
                            case 'html':
                                echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $data['ligne'][$tablefield]);
                                break;
                            case 'checkbox':
?>
    <input type="hidden" id="<?php echo $field_class; ?>-hidden" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>-hidden" value="0" />
    <input type="checkbox" id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>" value="1"
<?php
                            if ($htmlval) {
?>
    checked="checked"
<?php
                            }
?> />
<?php
                                break;
                            case 'file':
?>
    <input type="hidden" id="<?php echo $field_class; ?>-hidden" name="<?php echo $field_class; ?>-hidden" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>-hidden" value="<?php echo $htmlval; ?>" />
    <span id="<?php echo $field_class; ?>-uplcontainer" class="clementine_crud-plupload_container">
        <input type="file" id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>" />
    </span>
<?php

                                if (isset($fieldmeta['parameters'])) {
?>
    <span id="<?php echo $field_class; ?>-infoscontainer">
<?php
                                    if (isset($fieldmeta['parameters']['extensions'])) {
?>
    <span id="<?php echo $field_class; ?>-infosextensions">
        <?php echo implode (', ', $fieldmeta['parameters']['extensions']); ?>
    </span>
<?php
                                    }
                                    if (isset($fieldmeta['parameters']['max_filesize'])) {
?>
    <span id="<?php echo $field_class; ?>-infosmax_filesize">
        (max <?php
                                        $fullsize = $this->getModel('fonctions')->convert_bytesize($fieldmeta['parameters']['max_filesize']);
                                        $size = round((float) $fullsize, 2);
                                        $unite = substr($fullsize, -1);
                                        echo $size . '&nbsp;' . strtoupper($unite) . 'o';
?>)
    </span>
<?php
                                    }
?>
    </span>
<?php
                                }



                                if ($htmlval) {
                                    $visiblename = basename(preg_replace('/^[^-]*-/', '', $htmlval));
                                    $read_url = __WWW__ . '/' . $data['alldata']['class'] . '/read?' . $data['current_key'];
                                    $read_file_url = $ns->mod_param($read_url, 'file', $tablefield);
?>
    <a href="<?php echo $read_file_url; ?>" id="<?php echo $field_class; ?>-getfile" class="plupload_getfile">voir <?php echo $visiblename; ?></a>
    <a href="" id="<?php echo $field_class; ?>-after" class="plupload_finished" style="display: none; ">supprimer</a>
    <span id="<?php echo $field_class; ?>-removecontainer">
    <input type="checkbox" id="<?php echo $field_class; ?>-remove" name="<?php echo $field_class; ?>-remove" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>-remove" value="1" /> supprimer
    </span>
<?php
                                }
                                break;
                            case 'textarea':
?>
                <textarea id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?> <?php echo $class; ?>"><?php echo $htmlval; ?></textarea>
<?php
                                break;
                            default:
                                if (isset($fieldmeta['fieldvalues'])) {
?>
                <select id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>">
<?php
                                    foreach ($fieldmeta['fieldvalues'] as $fieldkey => $fieldval) {
?>
    <option value="<?php echo $fieldkey; ?>" <?php
    if ($fieldkey == $htmlval) {
?>
    selected="selected"
<?php
    }
?>><?php echo $fieldval; ?></option>
<?php
                                    }
?>
                </select>
<?php
                                } else {
?>
                <input type="<?php
                                    if ($mapping == 'password') {
                                        echo 'password';
                                    } else {
                                        echo 'text';
                                    }
                ?>" id="<?php echo $field_class; ?>" name="<?php echo $field_class; ?>" class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $fieldmeta['type']; ?>" value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval); ?>" <?php
                if (!empty($fieldmeta['size'])) {
                    echo 'maxlength="' . $fieldmeta['size'] . '" ';
                }
                ?> />
<?php
                                }
                                break;
                        }

                        // affichage du commentaire si disponible
                        if (isset($fieldmeta['comment'])) {
?>
    <span id="<?php echo $field_class; ?>-comment" class="clementine_crud-<?php echo $data['alldata']['formtype']; ?>-comment">
<?php
                            echo $ns->htmlentities($fieldmeta['comment']);
?>
    </span>
<?php
                        }
?>
            </td>
<?php
                    }
?>
        </tr>
<?php
                }
            }
        }
    }
}
?>
