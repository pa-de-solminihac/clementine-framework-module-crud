<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        var base_options = {
<?php
$this->getBlock($data['class'] . '/js_anytime_options', $data);
?>
        };
        // datetime
        jQuery('.clementine_crud-create_type-datetime').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%d/%m/%Y %H:%i:%s'
        }));
        jQuery('.clementine_crud-update_type-datetime').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%d/%m/%Y %H:%i:%s'
        }));
        // date
        jQuery('.clementine_crud-create_type-date').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%d/%m/%Y'
        }));
        jQuery('.clementine_crud-update_type-date').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%d/%m/%Y'
        }));
        // timepicker
        jQuery('.clementine_crud-create_type-time').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%H:%i:%s'
        }));
        jQuery('.clementine_crud-update_type-time').AnyTime_picker(crud_anytime_merge_options(base_options,{
            format: '%H:%i:%s'
        }));
        // ajout de boutons "vider" aux champs datetime et time
        jQuery('.clementine_crud-create_type-datetime').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        jQuery('.clementine_crud-create_type-date').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        jQuery('.clementine_crud-create_type-time').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        jQuery('.clementine_crud-update_type-datetime').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        jQuery('.clementine_crud-update_type-date').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        jQuery('.clementine_crud-update_type-time').after(' <a href="#" class="AnyTime_emptier">vider</a>');
        // action des boutons "vider"
        jQuery('.AnyTime_emptier').click(function() {
            var rang = parseInt(jQuery(this.parentNode.childNodes).index(this));
            var elt = jQuery(this.parentNode.childNodes[parseInt(rang - 2)]);
            if (elt) {
                elt.val('');
            }
            return false;
        });
    }
</script>
