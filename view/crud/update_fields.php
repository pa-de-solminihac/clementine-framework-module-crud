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
        if ($fieldmeta['type'] == 'custom_field' && isset($fieldmeta['custom_type'])) {
            if (isset($data['alldata']['mapping'][$fieldmeta['custom_type']])) {
                $mapping = $data['alldata']['mapping'][$fieldmeta['custom_type']];
            }
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
            if ($this->canGetBlock($data['alldata']['class'] . '/update_fields/custom_' . $tablefield)) {
                $this->getBlock($data['alldata']['class'] . '/update_fields/custom_' . $tablefield, array(
                    'tablefield' => $tablefield,
                    'ligne' => $data['ligne'],
                    'data' => $data['alldata']
                ) , $request);
            } else {
                if ($mapping == 'hidden') {
?>
    <input
        type="hidden"
        id="<?php echo $field_class; ?>"
        name="<?php echo $field_class; ?>"
        class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $mapping; ?>"
        value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval); ?>" />
<?php
                } else {
?>
    <div class="clementine_crud-row clementine_crud-<?php
                    echo $data['alldata']['formtype']; ?>-row clementine_crud-<?php
                    echo $data['alldata']['formtype']; ?>-row-<?php
                    echo $field_class; ?> <?php
                    echo implode(' ', $data['alldata']['more_classes_field_wrap']); ?>">
<?php
                    $label_open = '';
                    $label_close = '';
                    if (!(isset($data['alldata']['hidden_sections']['names']) && ($data['alldata']['hidden_sections']['names']))) {
                        $label_open.= '<label for="' . $field_class . '" class="clementine_crud-title_column clementine_crud-' . $data['alldata']['formtype'] . '-title_column ' . $field_class . '-title_column ';
                        if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                            $label_open.= 'clementine_crud-' . $data['alldata']['formtype'] . '-required_field';
                        }
                        $label_open.= ' ' . implode(' ', $data['alldata']['more_classes_field_key']) . ' ';
                        $label_open.= '">';
                        if (isset($data['alldata']['metas']['title_mapping'][$tablefield])) {
                            $label_open.= $data['alldata']['metas']['title_mapping'][$tablefield];
                        } else {
                            $label_open.= ucfirst(preg_replace('/[_-]+/', ' ', $field_name));
                        }
                        $label_open.= PHP_EOL;
                        $label_close = '</label>';
                    }
                    // affichage du commentaire si disponible
                    $commentaire = '';
                    if (isset($fieldmeta['comment'])) {
                        $commentaire.= '<span
                            id="' . $field_class . '-comment"
                            class="clementine_crud-comment clementine_crud-' . $data['alldata']['formtype'] . '-comment ' . $field_class . '-comment ' . implode(' ', $data['alldata']['more_classes_field_comment']) . '">';
                        $commentaire.= $ns->htmlentities($fieldmeta['comment']);
                        $commentaire.= ' </span>';
                    }
                    if (!(isset($data['alldata']['hidden_sections']['values']) && ($data['alldata']['hidden_sections']['values']))) {
                        $divclasses = 'clementine_crud-value_column clementine_crud-' . $data['alldata']['formtype'] . '-value_column ' . $field_class . '-value_column ';
                        $divclasses_more = implode(' ', $data['alldata']['more_classes_field_val_div']) . ' ';
                        $valueclasses = 'clementine_crud-type-' . $mapping . ' clementine_crud-' . $data['alldata']['formtype'] . '_type-' . $mapping . ' ' . $field_class . '-value_field ';
                        $valueclasses_more = implode(' ', $data['alldata']['more_classes_field_val']) . ' ';
                        switch ($mapping) {
                        case 'html':
                            echo $label_open;
                            echo $label_close;
?>
                <div class="<?php echo $divclasses; ?> <?php echo $divclasses_more; ?>">
<?php
                            echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $data['ligne'][$tablefield]);
                            echo $commentaire;
?>
                </div>
<?php
                            break;

                        case 'checkbox':
                        case 'togglebutton':
                            echo $label_open;
                            echo $label_close;
?>
                <div class="<?php
                            echo $divclasses; ?> <?php
                            echo $divclasses_more; ?>">
                    <span
                        class="<?php
                            if ($mapping == 'togglebutton') {
                                echo 'togglebutton togglebutton-primary ';
                            } else {
                                echo 'checkbox checkbox-primary ';
                            }
                            echo implode(' ', $data['alldata']['more_classes_field_checkbox']);
?>">
                        <input
                            type="hidden"
                            id="<?php echo $field_class; ?>-hidden"
                            name="<?php echo $field_class; ?>"
                            class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $mapping; ?>-hidden"
                            value="0" <?php
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            }
?> />
                        <label
                            for="<?php echo $field_class; ?>">
                            <input
                                type="checkbox"
                                id="<?php echo $field_class; ?>"
                                name="<?php echo $field_class; ?>"
                                class="<?php echo $valueclasses; ?>"
                                value="1" <?php
                            if ($htmlval) {
                                echo ' checked="checked" ';
                            }
                            if (!empty($fieldmeta['readonly'])) {
                                echo " readonly ";
                            }
                            if (!empty($fieldmeta['disabled'])) {
                                echo " disabled ";
                            }
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            }
?> />
                        </label>
                    </span>

<?php
                            echo $commentaire;
?>
                </div>
<?php
                            break;

                        case 'file':
                            echo $label_open;
                            echo $label_close;
?>
                <div class="<?php
                            echo $divclasses; ?> <?php
                            echo $divclasses_more; ?>">
                    <input
                        type="hidden"
                        id="<?php echo $field_class; ?>-hidden"
                        name="<?php echo $field_class; ?>-hidden"
                        class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $mapping; ?>-hidden"
                        value="<?php echo $htmlval; ?>"
                        <?php
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            } ?> />
                    <span
                        id="<?php echo $field_class; ?>-uplcontainer"
                        class="clementine_crud-plupload_container <?php echo $valueclasses_more; ?>">
                        <input
                            type="file"
                            id="<?php echo $field_class; ?>"
                            name="<?php echo $field_class; ?>"
                            class="<?php echo $valueclasses; ?>"
                            <?php
                            if (!empty($fieldmeta['readonly'])) {
                                echo " readonly ";
                            }
                            if (!empty($fieldmeta['disabled'])) {
                                echo " disabled ";
                            }
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            } ?> />

<?php
                            if ($htmlval) {
                                $visiblename = basename(preg_replace('/^[^-]*-/', '', $htmlval));
                                $read_url = __WWW__ . '/' . $data['alldata']['class'] . '/read?' . $data['current_key'];
                                $read_file_url = $ns->mod_param($read_url, 'file', $tablefield);
                                foreach ($data['alldata']['url_parameters'] as $key => $val) {
                                    $read_file_url = $ns->add_param($read_file_url, $key, $val, 1);
                                }
                                if (empty($fieldmeta['readonly'])) {
?>
                        <a
                            href=""
                            id="<?php echo $field_class; ?>-after"
                            class="plupload_finished delbutton"
                            style="display: none; ">
                            <i class="glyphicon glyphicon-trash"></i>
                            supprimer
                        </a>
<?php
                                }
?>
                        <a
                            href="<?php echo $read_file_url; ?>"
                            id="<?php echo $field_class; ?>-getfile"
                            target="_blank"
                            class="plupload_getfile">
                            <i class="glyphicon glyphicon-eye-open"></i>
                            voir <em><?php echo $visiblename; ?></em>
                        </a>
                        <span
                            id="<?php echo $field_class; ?>-removecontainer">
                            <input
                                type="checkbox"
                                id="<?php echo $field_class; ?>-remove"
                                name="<?php echo $field_class; ?>-remove"
                                class="<?php echo $valueclasses; ?>-remove <?php echo $valueclasses_more; ?>"
                                value="1"
                                <?php
                                if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                    echo ' required ';
                                } ?> /> supprimer
                        </span>
<?php
                            }
?>

                    </span>
<?php
                            if (isset($fieldmeta['parameters'])) {
?>
                    <span
                        id="<?php echo $field_class; ?>-infoscontainer"
                        class="<?php echo implode(' ', $data['alldata']['more_classes_field_comment']); ?>">
<?php
                                if (isset($fieldmeta['parameters']['extensions'])) {
?>
                    <span
                        id="<?php echo $field_class; ?>-infosextensions">
                        <?php echo implode(', ', $fieldmeta['parameters']['extensions']); ?>
                    </span>
<?php
                                }
                                if (isset($fieldmeta['parameters']['max_filesize'])) {
?>
                    <span
                        id="<?php echo $field_class; ?>-infosmax_filesize">
                        (max <?php
                                    $fullsize = $ns->convert_bytesize($fieldmeta['parameters']['max_filesize']);
                                    $size = round((float)$fullsize, 2);
                                    $unite = substr($fullsize, -1);
                                    echo $size . '&nbsp;' . strtoupper($unite) . 'o'; ?>)
                    </span>
<?php
                                }
?>
                    </span>
<?php
                            }
                            echo $commentaire;
?>
                </div>
<?php
                            break;

                        case 'textarea':
                            echo $label_open;
                            echo $label_close;
?>
                <div class="<?php echo $divclasses; ?> <?php echo $divclasses_more; ?>">
                    <textarea
                        id="<?php echo $field_class; ?>"
                        name="<?php echo $field_class; ?>"
                        class="<?php echo $valueclasses; ?> <?php echo $class; ?> <?php echo $valueclasses_more; ?>"
                        <?php
                            if (!empty($fieldmeta['readonly'])) {
                                echo " readonly ";
                            }
                            if (!empty($fieldmeta['disabled'])) {
                                echo " disabled ";
                            }
                            if (!empty($fieldmeta['autofocus'])) {
                                echo " autofocus ";
                            }
                            if (!empty($fieldmeta['data-hint'])) {
                                echo ' data-hint="' . $fieldmeta['data-hint'] . '" ';
                            }
                            if (!empty($fieldmeta['custom_attr'])) {
                                foreach ($fieldmeta['custom_attr'] as $key => $value) {
                                    echo ' ' . $key . '="' . $value . '" ';
                                }
                            }
                            if (!empty($fieldmeta['placeholder'])) {
                                echo ' placeholder="' . $fieldmeta['placeholder'] . '" ';
                            }
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            } ?> ><?php
                            echo $htmlval; ?></textarea>
<?php
                            echo $commentaire;
?>
                </div>
<?php
                            break;

                        case 'date':
                        case 'datetime':
                        case 'time':
                        case 'month':
                        case 'week':
                            // shadowed element
                            $datetime_value = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval);
                            $datetime_formated = '';
                            // force date/time format
                            if (strlen($datetime_value)) {
                                $datetime_timestamp = strtotime($datetime_value);
                                switch ($mapping) {
                                case 'date':
                                    $datetime_format = 'Y-m-d';
                                    break;
                                case 'time':
                                    $datetime_format = 'H:i';
                                    break;
                                case 'datetime':
                                    $datetime_format = 'Y-m-d H:i';
                                    break;
                                case 'month':
                                    $datetime_format = 'Y-m';
                                    break;
                                case 'week':
                                    $datetime_format = 'o-\WW';
                                    break;
                                }
                                $datetime_formated = date($datetime_format, $datetime_timestamp);
                            }
?>
                <input
                    type="hidden"
                    id="<?php echo $field_class; ?>-hidden"
                    name="<?php echo $field_class; ?>"
                    value="<?php echo $datetime_formated; ?>" />
<?php
                            echo $label_open;
                            echo $label_close;
                            // visible element

?>
                <div class="<?php echo $divclasses; ?> <?php echo $divclasses_more; ?>">
                    <input
                        type="<?php echo $mapping; ?>"
                        id="<?php echo $field_class; ?>"
                        class="<?php echo $valueclasses; ?> <?php echo $valueclasses_more; ?>"
                        value="<?php echo $datetime_formated; ?>"
                        <?php
                            if (!empty($fieldmeta['readonly'])) {
                                echo " readonly ";
                            }
                            if (!empty($fieldmeta['disabled'])) {
                                echo " disabled ";
                            }
                            if (!empty($fieldmeta['autofocus'])) {
                                echo " autofocus ";
                            }
                            if (!empty($fieldmeta['data-hint'])) {
                                echo ' data-hint="' . $fieldmeta['data-hint'] . '" ';
                            }
                            if (!empty($fieldmeta['custom_attr'])) {
                                foreach ($fieldmeta['custom_attr'] as $key => $value) {
                                    echo ' ' . $key . '="' . $value . '" ';
                                }
                            }
                            if (!empty($fieldmeta['placeholder'])) {
                                echo ' placeholder="' . $fieldmeta['placeholder'] . '" ';
                            }
                            if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                echo ' required ';
                            } ?> />

<?php
                            echo $commentaire;
?>
                </div>
<?php
                            break;

                        default:
                            if (isset($fieldmeta['fieldvalues']) && $mapping != 'span') {
                                if ($mapping == 'radio') {
                                    echo $label_open;
                                    echo $label_close;
?>
                <div class="<?php echo $divclasses; ?> <?php echo $divclasses_more; ?>">
<?php
                                    $i = 0;
                                    foreach ($fieldmeta['fieldvalues'] as $fieldkey => $fieldval) {
                                        ++$i;
?>
                    <span
                        class="radio radio-primary"
                        <?php
                                        if (!strlen($fieldval)) {
                                            echo ' style="display: none; " ';
                                        } ?>>
                        <label for="<?php
                                        echo $field_class . '-' . $i; ?>">
                            <input type="radio"
                                name="<?php echo $field_class; ?>"
                                value="<?php echo $fieldkey; ?>"
                                id="<?php echo $field_class . '-' . $i; ?>"
                                class="<?php echo $valueclasses; ?>"
                                <?php
                                        if ($fieldkey == $htmlval) {
                                            echo ' checked="checked" ';
                                        }
                                        if (!empty($fieldmeta['readonly'])) {
                                            echo " readonly ";
                                        }
                                        if (!empty($fieldmeta['disabled'])) {
                                            echo " disabled ";
                                        }
                                        if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                            echo ' required ';
                                        } ?> />
<?php
                                        echo $fieldval; ?></label>
                    </span>
<?php
                                    }
                                    echo $commentaire;
?>
                </div>
<?php
                                } else {
                                    echo $label_open;
                                    echo $label_close;
?>
                <div class="<?php
                                    echo $divclasses; ?> <?php
                                    echo $divclasses_more; ?>">
<?php
                                    if (!empty($fieldmeta['readonly'])) {
?>
                    <input
                        type="hidden"
                        id="<?php echo $field_class; ?>"
                        name="<?php echo $field_class; ?>"
                        class="clementine_crud-<?php echo $data['alldata']['formtype'] . '_type-' . $mapping; ?>"
                        value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval); ?>" />
<?php
                                    }
?>
                    <select id="<?php echo $field_class; ?>"
                            name="<?php echo $field_class; ?>"
                            class="<?php echo $valueclasses; ?> <?php echo $valueclasses_more; ?>"
                        <?php
                                    if (!empty($fieldmeta['readonly'])) {
                                        echo " readonly ";
                                        echo " disabled ";
                                    }
                                    if (!empty($fieldmeta['disabled'])) {
                                        echo " disabled ";
                                    }
                                    if (!empty($fieldmeta['autofocus'])) {
                                        echo " autofocus ";
                                    }
                                    if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                        echo ' required ';
                                    } ?> >
<?php
                                    foreach ($fieldmeta['fieldvalues'] as $fieldkey => $fieldval) {
                                        // hidden if no label is set
?>
                        <option
                            value="<?php echo $fieldkey; ?>"
                            <?php
                                        if ($ns->htmlentities($fieldkey) == $htmlval) {
?>
                            selected="selected"
<?php
                                        }
                                        if (is_array($fieldval)) {
                                            if (!strlen($fieldval['text'])) {
?>
                            style="display: none; "
<?php
                                            }
                                        } else {
                                            if (!strlen($fieldval)) {
?>
                            style="display: none; "
<?php
                                            }
                                        }
                                        if (is_array($fieldval)) {
                                            foreach ($fieldval as $fkey => $fv) {
                                                if ($fkey != 'text') {
                                                    echo $fkey;
                                                    if (isset($fv)) {
                                                        echo '="' . $fv . '"';
                                                    }
                                                }
                                            }
?>><?php
                                            echo $fieldval['text']; ?></option>
<?php
                                        } else {
?>><?php
                                            echo $fieldval; ?></option>
<?php
                                        }
                                    }
?>
                    </select>
                    <?php echo $commentaire; ?>
                </div>
<?php
                                }
                            } else {
                                echo $label_open;
                                echo $label_close;
?>
                <div
                    class="<?php echo $divclasses; ?> <?php echo $divclasses_more; ?>">
                    <input type="<?php
                                if (in_array($mapping, array(
                                    'password',
                                    'tel',
                                    'url',
                                    'email',
                                    'search',
                                    'number',
                                    'range',
                                    'color'
                                ))) {
                                    echo $mapping;
                                } elseif ($mapping == 'span') {
                                    echo "hidden";
                                } else {
                                    echo 'text';
                                } ?>"
                            id="<?php echo $field_class; ?>"
                            name="<?php echo $field_class; ?>"
                            class="<?php echo $valueclasses; ?> <?php echo $valueclasses_more; ?>"
                            value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $htmlval); ?>" <?php
                                if (!empty($fieldmeta['size'])) {
                                    echo ' maxlength="' . $fieldmeta['size'] . '" ';
                                }
                                if (!empty($fieldmeta['readonly'])) {
                                    echo ' readonly ';
                                }
                                if (!empty($fieldmeta['disabled'])) {
                                    echo " disabled ";
                                }
                                if (!empty($fieldmeta['autofocus'])) {
                                    echo ' autofocus ';
                                }
                                if (!empty($fieldmeta['data-hint'])) {
                                    echo ' data-hint="' . $fieldmeta['data-hint'] . '" ';
                                }
                                if (!empty($fieldmeta['placeholder'])) {
                                    echo ' placeholder="' . $fieldmeta['placeholder'] . '" ';
                                }
                                if (!empty($fieldmeta['custom_attr'])) {
                                    foreach ($fieldmeta['custom_attr'] as $key => $value) {
                                        echo ' ' . $key . '="' . $value . '" ';
                                    }
                                }
                                if (!empty($data['alldata']['metas']['mandatory_fields'][$tablefield])) {
                                    echo ' required ';
                                } ?> />
<?php
                                if ($mapping == 'span') {
?>
                    <span
                        id="<?php echo $field_class; ?>-span"
                        name="<?php echo $field_class; ?>-span"
                        class="<?php echo $valueclasses; ?> <?php echo $valueclasses_more; ?>">
<?php
                                    $displayed_val = $htmlval;
                                    if (isset($fieldmeta['fieldvalues'])) {
                                        $displayed_val = $fieldmeta['fieldvalues'][$htmlval];
                                    }
                                    echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $displayed_val); ?>
                    </span>

<?php
                                }
                                echo $commentaire;
?>
                </div>
<?php
                            }
                            break;
                        }
                    }
?>
        </div>
<?php
                }
            }
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
