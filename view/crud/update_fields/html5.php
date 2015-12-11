<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
echo $label_open;
echo $label_close;
?>
<div class="<?php echo $valueDivClasses; ?>">
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
            id="<?php echo $fieldClass; ?>"
            name="<?php echo $fieldClass; ?>"
            class="<?php echo $valueClasses; ?>"
            value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $fieldEscapedValue); ?>" <?php
                if (!empty($fieldMeta['size'])) {
                    echo ' maxlength="' . $fieldMeta['size'] . '" ';
                }
                if (!empty($fieldMeta['readonly'])) {
                    echo ' readonly ';
                }
                if (!empty($fieldMeta['disabled'])) {
                    echo " disabled ";
                }
                if (!empty($fieldMeta['autofocus'])) {
                    echo ' autofocus ';
                }
                if (!empty($fieldMeta['data-hint'])) {
                    echo ' data-hint="' . $fieldMeta['data-hint'] . '" ';
                }
                if (!empty($fieldMeta['placeholder'])) {
                    echo ' placeholder="' . $fieldMeta['placeholder'] . '" ';
                }
                if (!empty($fieldMeta['custom_attr'])) {
                    foreach ($fieldMeta['custom_attr'] as $key => $value) {
                        echo ' ' . $key . '="' . $value . '" ';
                    }
                }
                if (!empty($data['alldata']['metas']['mandatory_fields'][$tableField])) {
                    echo ' required ';
                } ?> />
<?php
                if ($mapping == 'span') {
?>
    <span
        id="<?php echo $fieldClass; ?>-span"
        name="<?php echo $fieldClass; ?>-span"
        class="<?php echo $valueClasses; ?>">
<?php
                    $displayed_val = $fieldEscapedValue;
                    if (isset($fieldMeta['fieldvalues'])) {
                        $displayed_val = $fieldMeta['fieldvalues'][$fieldEscapedValue];
                    }
                    echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $displayed_val); ?>
    </span>
<?php
                }
                echo $commentaire;
?>
</div>
