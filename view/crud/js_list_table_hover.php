<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        // effet hover sur les colonnes
        jQuery(document).ready(function() {
            jQuery('.clementine_crud-list_table td').hover(function() {
                var col = parseInt(jQuery(this.parentNode).find('td').index(this));
                jQuery(jQuery(this).parent().parent().parent().find('colgroup:first col')[col]).addClass("hover");
                jQuery(jQuery(this).parent().parent().parent().find('thead:first th')[col]).addClass("hover");
                jQuery(jQuery(this).parent().parent().parent().find('tr').each(function() {
                    var cellules = jQuery(this).find('td');
                    if (cellules.length) {
                        jQuery(cellules[col]).addClass("hover");
                    }
                }));
            }, function () {
                var col = parseInt(jQuery(this.parentNode).find('td').index(this));
                jQuery(jQuery(this).parent().parent().parent().find('colgroup:first col')[col]).removeClass("hover");
                jQuery(jQuery(this).parent().parent().parent().find('thead:first th')[col]).removeClass("hover");
                jQuery(jQuery(this).parent().parent().parent().find('tr').each(function() {
                    var cellules = jQuery(this).find('td');
                    if (cellules.length) {
                        jQuery(cellules[col]).removeClass("hover");
                    }
                }));
            });
<?php
if (isset($data['autoclick']) && $data['autoclick']) {
?>
            // clic sur <td> => clid sur le premier lien qu'il contient (sauf si dans colonne qui a la classe "no_autoclick"
            jQuery('body').delegate('.clementine_crud-list_table td', 'click', function (e) {
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
<?php
}
?>
            // classe CSS alt-row (1 ligne sur 2)
            jQuery('.clementine_crud-list_table tbody').each (function() {
                jQuery(this).find('tr').removeClass("alt-row");
                jQuery(this).find('tr:odd').addClass("alt-row");
            });
        });
    }
</script>
