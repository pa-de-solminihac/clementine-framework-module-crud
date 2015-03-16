<?php
class crudDebugHelper extends crudDebugHelper_Parent
{
    //public function missing_wrapper_id($opening_html_tag, $from_fieldkey, $to_fieldkey)
    //{
        //$this->trigger_error("CRUD wrapping missing wrapper id: attribut \"id\" manquant dans le code HTML du wrapper : " . $opening_html_tag, E_USER_ERROR, 1);
    //}

    public function unknown_field($fieldkey)
    {
        $this->trigger_error("CRUD wrapping unknown field: cet élément n'existe pas dans \$this->data['fields'] : " . $fieldkey, E_USER_ERROR, 1);
    }
}
