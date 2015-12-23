<script type="text/javascript">
    // si jQuery est chargé
    (function() {
        if (typeof(jQuery) == "undefined") {
            return undefined;
        }
        jQuery(document).ready(function() {

            var plupload_crudform = '';
            // plupload par fichier
<?php
foreach ($data['fields'] as $tablefield => $fieldMeta) {
    if (!(isset($fieldMeta['type']) && $fieldMeta['type'] == 'file')) {
        continue;
    }
    $browseButton = str_replace('.', '-', $tablefield);
    $data['plupload_block'] = array(
        $browseButton => $fieldMeta,
    );
    Clementine::getBlock($data['class'] . '/js_plupload_block', $data, $request);
}
Clementine::getBlock($data['class'] . '/js_plupload_general', $data, $request);
?>

        });
    })();
</script>
