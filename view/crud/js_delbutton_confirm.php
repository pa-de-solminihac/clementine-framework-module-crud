<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        // confirmation JS lors du clic sur un bouton de suppression
        jQuery('body').on('click', '.delbutton', function () {
            if (confirm('Voulez-vous vraiment supprimer cet élément ?')) {
                return true;
            }
            return false;
        });
    }
</script>
