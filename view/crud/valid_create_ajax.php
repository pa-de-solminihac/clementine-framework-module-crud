<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('body').delegate('form.clementine_crud-<?php
    if (isset($data['formtype'])) {
        echo $data['formtype'];
    } else {
        echo 'create';
    }
?>_form', 'submit', function (e) {
            var formaction = jQuery(this).attr('action');
            if (!formaction) {
                formaction = document.location.href;
            }
            var formdata = jQuery(this).serialize();
            var formmethod = jQuery(this).attr('method');
            var retour = 1;
            jQuery.ajax({
                async: false,
                type: formmethod,
                url: formaction,
                data: formdata,
                success: function(msg) {
                    var retval = msg.substring(0, 1);
                    if (retval == '1') {
                        alert(msg.substring(1));
                        retour = 0;
                    } else if (retval == '2') {
                        document.location = msg.substring(1);
                        retour = 0;
                    }
                }
            });
            if (retour) {
                alert('Problème technique, merci de réessayer plus tard');
            }
            e.preventDefault();
        });
    });
</script>
