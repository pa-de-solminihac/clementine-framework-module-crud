<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
echo $label_open;
echo $label_close;
?>
    <div class="<?php echo $valueDivClasses; ?>">
<?php
$i = 0;
foreach ($fieldMeta['fieldvalues'] as $fieldkey => $fieldval) {
    ++$i;
?>
        <span
            class="radio radio-primary"
<?php
    if (!strlen($fieldval)) {
        echo ' style="display: none; " ';
    } ?>>
        <label for="<?php
        echo $fieldClass . '-' . $i; ?>">
                <input type="radio"
                    name="<?php echo $fieldClass; ?>"
                    value="<?php echo $fieldkey; ?>"
                    id="<?php echo $fieldClass . '-' . $i; ?>"
                    class="<?php echo $valueClasses; ?>"
<?php
        if ($fieldkey == $fieldEscapedValue) {
            echo ' checked="checked" ';
        }
    if (!empty($fieldMeta['readonly'])) {
        echo " readonly ";
    }
    if (!empty($fieldMeta['disabled'])) {
        echo " disabled ";
    }
    if (!empty($data['alldata']['metas']['mandatory_fields'][$tableField])) {
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
