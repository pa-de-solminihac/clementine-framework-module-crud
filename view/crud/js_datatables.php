<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        // effet hover sur les colonnes
        jQuery(document).ready(function() {
            jQuery('.clementine-dataTables').dataTable({
                <?php
if (Clementine::$config['module_jstools']['persistent_datatables']) {
?>
                "bStateSave": true,
<?php
}
if (Clementine::$config['module_jstools']['nb_res_datatables']) {
?>
                "aLengthMenu": <?php echo Clementine::$config['module_jstools']['nb_res_datatables']; ?>,
<?php
}
                ?>
                "aaSorting": [], /* disable initial sort */
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "fnServerData": function (sSource, aoData, fnCallback, oSettings) {
                    oSettings.jqXHR = jQuery.ajax({
                        "dataType": 'json',
                        "type": "GET",
                        "url": sSource,
                        "data": aoData,
                        "success": fnCallback,
                        "error": function (xhr, textStatus, error) {
                            if (textStatus === 'timeout') {
                                alert('The server took too long to send the data.');
                            } else {
                                alert('An error occurred on the server. Please check that your are still connected to your account.');
                            }
                            myDataTable.fnProcessingIndicator(false);
                        }
                    }); 
                },
                "sAjaxSource": "<?php echo $request->EQUIV[$request->LANG]; ?>",
                "sServerMethod": "GET",
                "iDisplayLength": 10,
                "oLanguage": {
                    "sUrl": "<?php echo __WWW_ROOT_JSTOOLS__; ?>/skin/js/jquery.dataTables/locale/<?php echo $request->LANG; ?>.txt"
                },
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    if (!jQuery(this).data('noms_colonnes')) {
                        var noms_colonnes = new Array();
                        jQuery(this).find('colgroup > col').each(function() {
                            var colName = jQuery(this).attr('class');
                            var tdClass = colName.replace(/table_col_/, 'table_td_');
                            noms_colonnes.push(tdClass);
                        });
                        jQuery(this).data('noms_colonnes', noms_colonnes);
                    } else {
                        var noms_colonnes = jQuery(this).data('noms_colonnes');
                    }
                    jQuery('td', nRow).each(function(cellIndex) {
                        jQuery(this).addClass(noms_colonnes[cellIndex]);
                    });
                    return nRow;
                },
                "fnDrawCallback": function() {
                    jQuery(this).find('tr').removeClass("alt-row");
                    jQuery(this).find('tr:odd').addClass("alt-row");
                    //  disable sorting for actions column
                    jQuery('.clementine_crud-list_table_th_actions').unbind('click');
                }
            });
        });
    }
</script>
