<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);

// shadowed element
$datetime_value = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $fieldEscapedValue);
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
        id="<?php echo $fieldClass; ?>-hidden"
        name="<?php echo $fieldClass; ?>"
        value="<?php echo $datetime_formated; ?>" />
<?php
echo $label_open;
echo $label_close;
// visible element

?>
    <div class="<?php echo $valueDivClasses; ?>">
        <input
            type="<?php echo $mapping; ?>"
            id="<?php echo $fieldClass; ?>"
            class="<?php echo $valueClasses; ?>"
            value="<?php echo $datetime_formated; ?>"
<?php
if (!empty($fieldMeta['readonly'])) {
    echo " readonly ";
}
if (!empty($fieldMeta['disabled'])) {
    echo " disabled ";
}
if (!empty($fieldMeta['autofocus'])) {
    echo " autofocus ";
}
if (!empty($fieldMeta['data-hint'])) {
    echo ' data-hint="' . $fieldMeta['data-hint'] . '" ';
}
if (!empty($fieldMeta['custom_attr'])) {
    foreach ($fieldMeta['custom_attr'] as $key => $value) {
        echo ' ' . $key . '="' . $value . '" ';
    }
}
if (!empty($fieldMeta['placeholder'])) {
    echo ' placeholder="' . $fieldMeta['placeholder'] . '" ';
}
if (!empty($data['data']['metas']['mandatory_fields'][$tableField])) {
    echo ' required ';
} ?> />

<?php
    echo $commentaire;
?>
    </div>
<?php
