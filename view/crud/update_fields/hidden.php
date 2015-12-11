<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
?>
    <input
        type="hidden"
        id="<?php echo $fieldClass; ?>"
        name="<?php echo $fieldClass; ?>"
        class="clementine_crud-<?php echo $formType . '_type-' . $mapping; ?>"
        value="<?php echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $fieldEscapedValue); ?>" />
