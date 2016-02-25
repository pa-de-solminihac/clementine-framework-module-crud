<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
echo $label_open;
echo $label_close;
?>
    <div class="<?php echo $valueDivClasses; ?>">
        <span
            class="<?php
                if (!$hasOnsenUI) {
                    if ($mapping == 'togglebutton') {
                        echo 'togglebutton togglebutton-primary ';
                    } else {
                        echo 'checkbox checkbox-primary ';
                    }
                }
                echo implode(' ', $data['data']['more_classes_field_checkbox']);
?>">
            <input
                type="hidden"
                id="<?php echo $fieldClass; ?>-hidden"
                name="<?php echo $fieldClass; ?>"
                class="clementine_crud-<?php echo $formType . '_type-' . $mapping; ?>-hidden"
                value="0" <?php
                if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
                    echo ' required ';
                }
?> />
            <label
                for="<?php echo $fieldClass; ?>">
                <input
                    type="checkbox"
                    id="<?php echo $fieldClass; ?>"
                    name="<?php echo $fieldClass; ?>"
<?php
// $valueClasses_base instead of $valueClasses here, because the 'form-control' CSS class conflicts with the material design theme (bootstrap3material)
?>
                    class="<?php echo $valueClasses_base; ?>"
                    value="1" <?php
                if ($fieldEscapedValue) {
                    echo ' checked="checked" ';
                }
                if (!empty($fieldMeta['readonly'])) {
                    echo " readonly ";
                }
                if (!empty($fieldMeta['disabled'])) {
                    echo " disabled ";
                }
                if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
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
