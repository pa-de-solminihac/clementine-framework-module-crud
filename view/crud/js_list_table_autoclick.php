<?php
if (isset($data['autoclick']) && $data['autoclick']) {
?>
<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        // effet hover sur les colonnes
        jQuery(document).ready(function() {
            // clic sur <td> => clid sur le premier lien qu'il contient (sauf si dans colonne qui a la classe "no_autoclick"
            jQuery('body').on('click', '.clementine_crud-list_table td', function (e) {
                var col = jQuery(this).parent().children().index(jQuery(this));
                var cols = jQuery(this).closest('table').find('thead th');
                if (!(jQuery(cols[col]).hasClass('no_autoclick'))) {
                    var url = jQuery(this).find('a:first').attr('href');
                    if (url && url.length) {
                        document.location = url;
                        return false;
                    }
                }
            });
        });
    }
</script>
<?php
}
?>
