<?php
$ns = Clementine::getModel('fonctions');
foreach ($data['alldata']['fields'] as $tableField => $fieldMeta) {
    if (!array_key_exists($tableField, $data['ligne'])) { // array_key_exists !== isset
        continue;
    }
    $formType = $data['alldata']['formtype'];
    $fieldValue = $data['ligne'][$tableField];
    $fieldClass = $tableField;
    $fieldName = $tableField;
    if ($fieldMeta['type'] != 'custom_field' && strpos($tableField, '.')) {
        list($table, $field) = explode('.', $tableField, 2);
        $fieldClass = $table . '-' . $field;
        $fieldName = $field;
    }
    $fieldEscapedValue = $ns->htmlentities($fieldValue);
    $hidden = 0;
    if (isset($data['alldata']['metas']['hidden_fields'][$tableField]) && $data['alldata']['metas']['hidden_fields'][$tableField]) {
        $hidden = 1;
    }
    $class = '';
    if (isset($fieldMeta['class'])) {
        $class = $fieldMeta['class'];
    }
    $mapping = '';
    if (isset($data['alldata']['mapping'][$fieldMeta['type']])) {
        $mapping = $data['alldata']['mapping'][$fieldMeta['type']];
    }
    if ($fieldMeta['type'] == 'custom_field' && isset($fieldMeta['custom_type'])) {
        if (isset($data['alldata']['mapping'][$fieldMeta['custom_type']])) {
            $mapping = $data['alldata']['mapping'][$fieldMeta['custom_type']];
        }
    }
    if (!$mapping && !isset($fieldMeta['fieldvalues'])) {
        $mapping = 'novalue';
    }
    // opening wrapper tag
    if (!empty($data['alldata']['wrappers']['open'][$fieldClass])) {
        $reverse_wrappers = array_reverse($data['alldata']['wrappers']['open'][$fieldClass]);
        foreach ($reverse_wrappers as $wrapper) {
            if ($wrapper['opening_block']) {
                //echo $wrapper['opening_block'] . PHP_EOL;
                Clementine::getBlock($wrapper['opening_block'], $data, $request);
            }
        }
    }
    // display field if not hidden
    if (!$hidden) {
        $label_open = '';
        $label_close = '';
        $commentaire = '';
        $valueClasses = '';
        // opening and closing <label> tags
        $doDisplayNames = empty($data['alldata']['hidden_sections']['names']);
        if ($doDisplayNames) {
            $label_open.= '<label for="' . $fieldClass . '" class="clementine_crud-title_column clementine_crud-' . $formType . '-title_column ' . $fieldClass . '-title_column ';
            if (!empty($data['alldata']['metas']['mandatory_fields'][$tableField])) {
                $label_open.= 'clementine_crud-' . $formType . '-required_field';
            }
            $label_open.= ' ' . implode(' ', $data['alldata']['more_classes_field_key']) . ' ';
            $label_open.= '">';
            if (isset($data['alldata']['metas']['title_mapping'][$tableField])) {
                $label_open.= $data['alldata']['metas']['title_mapping'][$tableField];
            } else {
                $label_open.= ucfirst(preg_replace('/[_-]+/', ' ', $fieldName));
            }
            $label_open.= PHP_EOL;
            $label_close = '</label>';
        }
        // commentaire if available
        if (isset($fieldMeta['comment'])) {
            $commentaire.= '<span
                id="' . $fieldClass . '-comment"
                class="clementine_crud-comment clementine_crud-' . $formType . '-comment ' . $fieldClass . '-comment ' . implode(' ', $data['alldata']['more_classes_field_comment']) . '">';
            $commentaire.= $ns->htmlentities($fieldMeta['comment']);
            $commentaire.= ' </span>';
        }

        $doDisplayValues = empty($data['alldata']['hidden_sections']['values']);
        if ($doDisplayValues) {
            $valueDivClasses = 'clementine_crud-value_column clementine_crud-' . $formType . '-value_column ' . $fieldClass . '-value_column ';
            $valueDivClasses.= implode(' ', $data['alldata']['more_classes_field_val_div']) . ' ';
            $valueClasses_base = 'clementine_crud-type-' . $mapping . ' clementine_crud-' . $formType . '_type-' . $mapping . ' ' . $fieldClass . '-value_field ';
            $valueClasses = $valueClasses_base . implode(' ', $data['alldata']['more_classes_field_val']) . ' ';
        }

        // data to be passed to subblock
        $alldata = array(
            'class' => $class,
            'commentaire' => $commentaire,
            'current_key' => $data['current_key'],
            'data' => $data['alldata'],
            'fieldClass' => $fieldClass,
            'fieldEscapedValue' => $fieldEscapedValue,
            'fieldMeta' => $fieldMeta,
            'formType' => $formType,
            'hasOnsenUI' => !empty($data['data']['onsenui']),
            'label_close' => $label_close,
            'label_open' => $label_open,
            'ligne' => $data['ligne'],
            'mapping' => $mapping,
            'tablefield' => $tableField, // case is important for backward compatibility
            'valueClasses' => $valueClasses,
            'valueClasses_base' => $valueClasses_base,
            'valueDivClasses' => $valueDivClasses,
        );

        // load custom block instead if available
        $customBlockPath = $data['alldata']['class'] . '/update_fields/custom_' . $tableField;
        $hasCustomBlock = Clementine::canGetBlock($customBlockPath);
        if ($hasCustomBlock) {
            Clementine::getBlock($customBlockPath, $alldata, $request);
        } elseif ($doDisplayValues) {
            if ($mapping != 'hidden') {
                $rowDivClasses = "clementine_crud-row ";
                $rowDivClasses .= "clementine_crud-$formType-row ";
                $rowDivClasses .= "clementine_crud-$formType-row-$fieldClass ";
                $rowDivClasses .= implode(' ', $data['alldata']['more_classes_field_wrap']);
?>
<div class="<?php echo $rowDivClasses; ?>">
<?php
            }
            switch ($mapping) {
            case 'hidden':
            case 'html':
            case 'file':
            case 'textarea':
            case 'radio':
                $real_mapping = $mapping;
                Clementine::getBlock($data['alldata']['class'] . '/update_fields/' . $real_mapping, $alldata, $request);
                break;

            case 'checkbox':
            case 'togglebutton':
                $real_mapping = 'checkbox';
                Clementine::getBlock($data['alldata']['class'] . '/update_fields/' . $real_mapping, $alldata, $request);
                break;

            case 'date':
            case 'datetime':
            case 'time':
            case 'month':
            case 'week':
                $real_mapping = 'datetime';
                Clementine::getBlock($data['alldata']['class'] . '/update_fields/' . $real_mapping, $alldata, $request);
                break;

            case 'span':
            case 'color':
            case 'email':
            case 'novalue':
            case 'number':
            case 'password':
            case 'range':
            case 'search':
            case 'tel':
            case 'url':
                $real_mapping = 'html5';
                Clementine::getBlock($data['alldata']['class'] . '/update_fields/' . $real_mapping, $alldata, $request);
                break;

            default:
                if (!isset($fieldMeta['fieldvalues'])/* || $mapping == 'span'*/) {
                    break;
                }
                $real_mapping = 'default';
                Clementine::getBlock($data['alldata']['class'] . '/update_fields/' . $real_mapping, $alldata, $request);
                break;
            }
            if ($mapping != 'hidden') {
?>
</div>
<?php
            }
        } else {
            Clementine::log('Champ non géré ? ' . $tableField, 'purple');
        }
    }
    // closing wrapper tag
    if (!empty($data['alldata']['wrappers']['close'][$fieldClass])) {
        $wrappers = $data['alldata']['wrappers']['close'][$fieldClass];
        foreach ($wrappers as $wrapper) {
            if ($wrapper['closing_block']) {
                //echo $wrapper['closing_block'] . PHP_EOL;
                Clementine::getBlock($wrapper['closing_block'], $data, $request);
            }
        }
    }
}
