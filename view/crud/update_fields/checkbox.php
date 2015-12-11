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
<?php
if (!$hasOnsenUI) {
?>
<label class="checkbox">
<?php
}
?>
                <input
                    type="checkbox"
                    id="<?php echo $fieldClass; ?>"
                    name="<?php echo $fieldClass; ?>"
                    class="<?php echo $valueClasses; ?>"
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
<?php
if (!$hasOnsenUI) {
?>
<div class="checkbox__checkmark"></div>
<span class="ons-checkbox-inner"></span>
</label>
<?php
}
?>
            </label>
        </span>
<?php
                echo $commentaire;
?>
    </div>
<?php
