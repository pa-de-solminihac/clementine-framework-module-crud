<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        // confirmation JS lors du clic sur un bouton de suppression
        jQuery('body').off('click.crud-delbutton', '.delbutton').on('click.crud-delbutton', '.delbutton', function (e) {
            if (confirm('Voulez-vous vraiment supprimer cet élément ?')) {
                return true;
            }
            e.stopImmediatePropagation();
            return false;
        });
    }
</script>
