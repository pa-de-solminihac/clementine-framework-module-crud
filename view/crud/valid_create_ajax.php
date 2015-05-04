<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('body').on('submit', 'form.clementine_crud-<?php
    if (isset($data['formtype'])) {
        echo $data['formtype'];
    } else {
        echo 'create';
    }
?>_form', function (e) {
            var currentform = jQuery(this);
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
                        //html5 form validation
                        var errstr = msg.substring(1);
                        var errdata = '';
                        try {
                            errdata = JSON.parse(errstr);
                        } catch (e) {
                        }
                        var html5set = 0;
                        if (typeof errdata === 'object') {
                            for (var errfield_id in errdata) {
                                if (errdata.hasOwnProperty(errfield_id)) {
                                    try {
                                        var errfield_msg = errdata[errfield_id];
                                        var errfield = document.getElementById(errfield_id);
                                        if (errfield) {
                                            errfield.setCustomValidity(errfield_msg);
                                            jQuery('#' + errfield_id).off('propertychange.clementine_crud input.clementine_crud');
                                            jQuery('#' + errfield_id).on('propertychange.clementine_crud input.clementine_crud', function () {
                                                this.setCustomValidity('');
                                            });
                                            html5set = 1;
                                        }
                                    } catch (e) {
                                        html5set = 0;
                                        break;
                                    }
                                }
                            }
                        }
                        //fallback
                        if (html5set && typeof document.styleSheets[0].insertRule == 'function') {
                            //mise en évidence des erreurs à la FF, identique sous FF et Chrome. on l'ajoute à la volée car sinon FF met en évidence les champs invalides même avant validation du formulaire
                            var css_invalid = 'select:invalid, textarea:invalid, input:invalid { -moz-box-shadow: 0 0 4px #FF0000; -webkit-box-shadow: 0 0 4px #FF0000; box-shadow: 0 0 4px #FF0000; }';
                            //le navigateur réécrit souvent les règles de CSS, alors on va se baser sur le début de la chaîne seulement
                            if (document.styleSheets[0].cssRules[0].cssText.substr(0, 47) != css_invalid.substr(0, 47)) {
                                document.styleSheets[0].insertRule(css_invalid, 0);
                            }
                            //if the form is invalid, try to submit it again. The form won't actually submit; this will just cause the browser to display the native HTML5 error messages!
                            try {
                                if (!currentform[0].checkValidity()) {
                                    // setTimeout necessaire pour Chrome
                                    setTimeout(function() {
                                        currentform.find(':submit').click()
                                    }, 100);
                                }
                            } catch (e) {
                            }
                        } else {
                            if (errdata) {
                                var errmsg = "Merci de vérifier les points suivants : ";
                                for (var errfield_id in errdata) {
                                    if (errdata.hasOwnProperty(errfield_id)) {
                                        var errfield_msg = errdata[errfield_id];
                                        errmsg += "\r\n- " + errfield_msg;
                                    }
                                }
                            } else {
                                var errmsg = errstr;
                            }
                            alert(errmsg);
                        }
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
