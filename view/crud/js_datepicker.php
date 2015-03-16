<script>
function crud_js_datepicker_update_shadow(id, value) {
    // TODO: ici, convertir le format de date, peut être en utilisant moment.js ? auquel cas il faudra en faire un module indépendant...
    // TODO: vérifier que la validation HTML5 en AJAX passe toujours bien
    var shadow_el = jQuery('#' + id + '-hidden');
    if (value == null) {
        value = '';
    }
    shadow_el.val(value);
    return true;
}
jQuery(document).ready(function() {
    jQuery('.clementine_crud-create_type-date, .clementine_crud-update_type-date, .clementine_crud-create_type-time, .clementine_crud-update_type-time, .clementine_crud-create_type-datetime, .clementine_crud-update_type-datetime').on(
        'change.<?php echo $data['class']; ?>_datepicker, focus.<?php echo $data['class']; ?>_datepicker, blur.<?php echo $data['class']; ?>_datepicker',
        function(e) {
            var el = jQuery(this);
            var el_id = el.attr('id');
            var selected_value = el.val();
            crud_js_datepicker_update_shadow(el_id, selected_value);
        }
    );
});
</script>
