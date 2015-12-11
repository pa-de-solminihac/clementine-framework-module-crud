<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
$ns = $this->getModel('fonctions');
echo $label_open;
echo $label_close;
?>
    <div class="<?php echo $valueDivClasses; ?>">
<?php
if (!empty($fieldMeta['readonly'])) {
?>
        <input
            type="hidden"
            id="<?php echo $fieldClass; ?>"
            name="<?php echo $fieldClass; ?>"
            class="clementine_crud-<?php echo $formType . '_type-' . $mapping; ?>"
            value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $fieldEscapedValue); ?>" />
<?php
}
?>
        <select id="<?php echo $fieldClass; ?>"
                name="<?php echo $fieldClass; ?>"
                class="crud-select <?php echo $valueClasses; ?>"
<?php
if (!empty($fieldMeta['readonly'])) {
    echo " readonly ";
    echo " disabled ";
}
if (!empty($fieldMeta['disabled'])) {
    echo " disabled ";
}
if (!empty($fieldMeta['autofocus'])) {
    echo " autofocus ";
}
if (!empty($data['alldata']['metas']['mandatory_fields'][$tableField])) {
    echo ' required ';
} ?> >
<?php
    foreach ($fieldMeta['fieldvalues'] as $fieldkey => $fieldval) {
        // hidden if no label is set
?>
            <option
                value="<?php echo $fieldkey; ?>"
<?php
        if ($ns->htmlentities($fieldkey) == $fieldEscapedValue) {
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
