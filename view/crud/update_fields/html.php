<?php
$tableField = $data['tablefield']; // case is important for backward compatibility
extract($data, EXTR_SKIP);
echo $label_open;
echo $label_close;
?>
    <div class="<?php echo $valueDivClasses; ?>">
<?php
echo str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $ligne[$tableField]);
echo $data['commentaire'];
?>
    </div>
