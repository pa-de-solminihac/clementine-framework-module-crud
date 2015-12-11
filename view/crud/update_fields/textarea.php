<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
echo $label_open;
echo $label_close;
?>
    <div class="<?php echo $valueDivClasses; ?>">
        <textarea
            id="<?php echo $fieldClass; ?>"
            name="<?php echo $fieldClass; ?>"
            class="<?php echo $valueClasses; ?> <?php echo $class; ?>"
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
                if (!empty($data['alldata']['metas']['mandatory_fields'][$tableField])) {
                    echo ' required ';
                } ?> ><?php
                echo $fieldEscapedValue; ?></textarea>
<?php
                echo $commentaire;
?>
    </div>
