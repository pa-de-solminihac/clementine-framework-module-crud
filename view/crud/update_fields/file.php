<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
$ns = $this->getModel('fonctions');
echo $label_open;
echo $label_close;
?>
    <div class="crud-file <?php echo $valueDivClasses; ?>">
        <input
            type="hidden"
            id="<?php echo $fieldClass; ?>-hidden"
            name="<?php echo $fieldClass; ?>-hidden"
            class="clementine_crud-<?php echo $formType . '_type-' . $mapping; ?>-hidden"
            value="<?php echo $fieldEscapedValue; ?>"
            <?php
                if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
                    echo ' required ';
                } ?> />
        <span
            id="<?php echo $fieldClass; ?>-uplcontainer"
            class="clementine_crud-plupload_container <?php echo $valueClasses; ?>">
            <input
                type="file"
                id="<?php echo $fieldClass; ?>"
                name="<?php echo $fieldClass; ?>"
                class="<?php echo $valueClasses_base; ?>"
                <?php
                if (!empty($fieldMeta['readonly'])) {
                    echo " readonly ";
                }
                if (!empty($fieldMeta['disabled'])) {
                    echo " disabled ";
                }
                if (!empty($fieldMeta['custom_attr'])) {
                    foreach ($fieldMeta['custom_attr'] as $key => $value) {
                        echo ' ' . $key . '="' . $ns->htmlentities($value) . '" ';
                    }
                }
                if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
                    echo ' required ';
                } ?> />

<?php
                if ($fieldEscapedValue) {
                    $visiblename = basename(preg_replace('/^[^-]*-/', '', $fieldEscapedValue));
                    $read_url = __WWW__ . '/' . $data['data']['class'] . '/read?' . $data['current_key'];
                    $read_file_url = $ns->mod_param($read_url, 'file', $tableField);
                    foreach ($data['data']['url_parameters'] as $key => $val) {
                        $read_file_url = $ns->add_param($read_file_url, $key, $val, 1);
                    }
                    if (empty($fieldMeta['readonly'])) {
?>
            <a
                href=""
                id="<?php echo $fieldClass; ?>-after"
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
                id="<?php echo $fieldClass; ?>-getfile"
                target="_blank"
                class="plupload_getfile">
                <i class="glyphicon glyphicon-eye-open"></i>
                voir <em><?php echo $visiblename; ?></em>
            </a>
            <span
                id="<?php echo $fieldClass; ?>-removecontainer">
<?php
if ($hasOnsenUI) {
?>
<label class="checkbox">
<?php
}
?>
                <input
                    type="checkbox"
                    id="<?php echo $fieldClass; ?>-remove"
                    name="<?php echo $fieldClass; ?>-remove"
                    class="<?php echo $valueClasses; ?>"
                    value="1"
                    <?php
                    if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
                        echo ' required ';
                    } ?> /> supprimer
<?php
if ($hasOnsenUI) {
?>
<div class="checkbox__checkmark"></div>
<span class="ons-checkbox-inner"></span>
</label>
<?php
}
?>
            </span>
<?php
                }
?>

        </span>
<?php
                if (isset($fieldMeta['parameters'])) {
?>
        <span
            id="<?php echo $fieldClass; ?>-infoscontainer"
            class="crud-infoscontainer <?php echo implode(' ', $data['data']['more_classes_field_comment']); ?>">
<?php
                    if (isset($fieldMeta['parameters']['extensions'])) {
?>
        <span
            id="<?php echo $fieldClass; ?>-infosextensions" class="crud-infosextensions">
            <?php echo implode(', ', $fieldMeta['parameters']['extensions']); ?>
        </span>
<?php
                    }
                    if (isset($fieldMeta['parameters']['max_filesize'])) {
?>
        <span
            id="<?php echo $fieldClass; ?>-infosmax_filesize" class="crud-infosmax_filesize">
            (max <?php
                        $fullsize = $ns->convert_bytesize($fieldMeta['parameters']['max_filesize']);
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
