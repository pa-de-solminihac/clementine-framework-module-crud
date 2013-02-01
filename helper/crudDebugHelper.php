<?php
class crudDebugHelper extends crudDebugHelper_Parent
{
    public function crud_constructor ()
    {
        if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
            $this->trigger_error("Crud constructor : \$this->tables ne doit pas être vide. Surchargez la fonction _init du modèle... ", E_USER_ERROR, 1);
        }
        die();
    }

    public function crud_missing_primary_key ($table, $field)
    {
        if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
            $this->trigger_error("Crud missing primary key : " . $table . '.' . $field, E_USER_ERROR, 3);
        }
        die();
    }

    public function crud_incomplete_key ()
    {
        if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
            $this->trigger_error("Crud incomplete key", E_USER_WARNING, 3);
        }
    }

    public function unknown_element ()
    {
        if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
            $this->trigger_error("Crud read : cet élément n'existe pas ou n'est pas accessible ", E_USER_WARNING, 1);
        }
    }
}
?>
